<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CompanyProfile;
use App\Models\DriverProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ResetPasswordOtpMail;

class AuthController extends Controller
{
    /**
     * Register a new user (Individual Customer, Company Customer, or Driver).
     */
    public function register(Request $request)
    {
        try {
            // 1. Validation Rules (Safe from php_fileinfo extension crashes)
            $rules = [
                'fname'           => 'required|string|max:255',
                'lname'           => 'nullable|string|max:255',
                'email'           => 'required|email|max:255|unique:users,email',
                'phone'           => 'nullable|string|max:255',
                'secondary_phone' => 'nullable|string|max:50',
                'password'        => 'required|string|min:6',
                'role'            => 'required|in:individual_customer,company_customer,driver,admin',
                'status'          => 'nullable|in:active,inactive,pending,banned',
                'country_code'    => 'nullable|string|max:10',
                'country_id'      => 'nullable|exists:countries,id',
                'city_id'         => 'nullable|exists:cities,id',
                'dateofbirth'     => 'nullable|date',
                'address'         => 'nullable|string',
                'locale'          => 'nullable|string|in:ar,en',
                'firebase_token'  => 'nullable|string',
            ];

            // Conditional Rules based on role
            if ($request->role === 'company_customer') {
                $rules['company_name']            = 'required|string|max:255';
                $rules['commercial_register']     = 'required|string|max:100';
                $rules['civil_id']                = 'required|string|max:50';
                $rules['tax_number']              = 'nullable|string|max:100';
                $rules['representative_name']     = 'nullable|string|max:255';
                $rules['representative_position'] = 'nullable|string|max:255';
                $rules['representative_phone']    = 'nullable|string|max:100';
                $rules['verification_status']     = 'nullable|in:pending,verified,rejected';
                $rules['rejection_reason']        = 'nullable|string';
            } elseif ($request->role === 'driver') {
                $rules['truck_type_id']             = 'nullable|exists:truck_types,id';
                $rules['license_number']           = 'nullable|string|max:255';
                $rules['wallet_balance']           = 'nullable|numeric|min:0';
                $rules['rating']                   = 'nullable|numeric|min:1|max:5';
                $rules['availability_status']      = 'nullable|in:available,busy,offline';
                $rules['driver_verification_status'] = 'nullable|in:pending,verified,rejected';
                $rules['driver_rejection_reason']  = 'nullable|string';
            }

            // Custom Validation Messages
            $messages = [
                'fname.required'               => 'First name is required.',
                'email.required'               => 'Email address is required.',
                'email.email'                  => 'Email address must be a valid email format.',
                'email.unique'                 => 'This email address is already registered.',
                'password.required'            => 'Password is required.',
                'password.min'                 => 'Password must be at least 6 characters.',
                'role.required'                => 'Role is required.',
                'role.in'                      => 'Invalid user role selected.',
                'company_name.required'        => 'Company name is required for company accounts.',
                'commercial_register.required' => 'Commercial register (CR) number is required.',
                'civil_id.required'            => 'Civil ID / National ID is required for company accounts.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation error',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            // 1.1 Safe File Validation (Protects against missing php_fileinfo on cPanel)
            $fileErrors = [];

            if ($request->hasFile('photo')) {
                $photoFile = $request->file('photo');
                if (!$photoFile->isValid()) {
                    $fileErrors['photo'][] = 'Invalid photo file upload.';
                } elseif ($photoFile->getSize() > 5 * 1024 * 1024) {
                    $fileErrors['photo'][] = 'The photo must not exceed 5MB.';
                } else {
                    $photoExt = strtolower($photoFile->getClientOriginalExtension());
                    $allowedImageExts = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'];
                    if (!in_array($photoExt, $allowedImageExts)) {
                        $fileErrors['photo'][] = 'Invalid photo format. Allowed formats: ' . implode(', ', $allowedImageExts);
                    }
                }
            }

            if ($request->hasFile('commercial_register_doc')) {
                $docFile = $request->file('commercial_register_doc');
                if (!$docFile->isValid()) {
                    $fileErrors['commercial_register_doc'][] = 'Invalid commercial register document upload.';
                } elseif ($docFile->getSize() > 10 * 1024 * 1024) {
                    $fileErrors['commercial_register_doc'][] = 'The commercial register document must not exceed 10MB.';
                } else {
                    $docExt = strtolower($docFile->getClientOriginalExtension());
                    $allowedDocExts = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array($docExt, $allowedDocExts)) {
                        $fileErrors['commercial_register_doc'][] = 'Invalid document format. Allowed formats: ' . implode(', ', $allowedDocExts);
                    }
                }
            }

            if ($request->hasFile('license_photo')) {
                $f = $request->file('license_photo');
                if (!$f->isValid()) {
                    $fileErrors['license_photo'][] = 'Invalid license photo upload.';
                } elseif ($f->getSize() > 10 * 1024 * 1024) {
                    $fileErrors['license_photo'][] = 'The license document must not exceed 10MB.';
                } else {
                    $ext = strtolower($f->getClientOriginalExtension());
                    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array($ext, $allowed)) {
                        $fileErrors['license_photo'][] = 'Invalid document format. Allowed formats: ' . implode(', ', $allowed);
                    }
                }
            }

            if ($request->hasFile('truck_registration_photo')) {
                $f = $request->file('truck_registration_photo');
                if (!$f->isValid()) {
                    $fileErrors['truck_registration_photo'][] = 'Invalid truck registration upload.';
                } elseif ($f->getSize() > 10 * 1024 * 1024) {
                    $fileErrors['truck_registration_photo'][] = 'The truck registration document must not exceed 10MB.';
                } else {
                    $ext = strtolower($f->getClientOriginalExtension());
                    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array($ext, $allowed)) {
                        $fileErrors['truck_registration_photo'][] = 'Invalid document format. Allowed formats: ' . implode(', ', $allowed);
                    }
                }
            }

            if ($request->hasFile('civil_id_photo')) {
                $f = $request->file('civil_id_photo');
                if (!$f->isValid()) {
                    $fileErrors['civil_id_photo'][] = 'Invalid civil ID upload.';
                } elseif ($f->getSize() > 10 * 1024 * 1024) {
                    $fileErrors['civil_id_photo'][] = 'The civil ID document must not exceed 10MB.';
                } else {
                    $ext = strtolower($f->getClientOriginalExtension());
                    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array($ext, $allowed)) {
                        $fileErrors['civil_id_photo'][] = 'Invalid document format. Allowed formats: ' . implode(', ', $allowed);
                    }
                }
            }

            if (!empty($fileErrors)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation error',
                    'errors'  => $fileErrors,
                ], 422);
            }

            DB::beginTransaction();

            // 2. Create User record
            $user = new User();
            $user->fname           = trim($request->fname);
            $user->lname           = $request->filled('lname') ? trim($request->lname) : null;
            $user->email           = strtolower(trim($request->email));
            $user->phone           = $request->filled('phone') ? trim($request->phone) : null;
            $user->secondary_phone = $request->filled('secondary_phone') ? trim($request->secondary_phone) : null;
            $user->password        = Hash::make($request->password);
            $user->role            = $request->role;
            $user->status          = $request->status ?? 'pending';
            $user->country_code    = $request->country_code ?? '+966';
            $user->country_id      = $request->filled('country_id') ? $request->country_id : null;
            $user->city_id         = $request->filled('city_id') ? $request->city_id : null;
            $user->dateofbirth     = $request->filled('dateofbirth') ? $request->dateofbirth : null;
            $user->address         = $request->filled('address') ? trim($request->address) : null;
            $user->locale          = $request->locale ?? 'ar';
            $user->firebase_token  = $request->filled('firebase_token') ? $request->firebase_token : null;

            // Handle user profile photo safely
            if ($request->hasFile('photo')) {
                $user->photo = $this->saveUploadedFile($request->file('photo'), 'upload/user_images');
            }

            $user->save();

            // 3. Handle Company Profile
            if ($request->role === 'company_customer') {
                $company = new CompanyProfile();
                $company->user_id                 = $user->id;
                $company->company_name            = trim($request->company_name);
                $company->commercial_register     = trim($request->commercial_register);
                $company->civil_id                = trim($request->civil_id);
                $company->tax_number              = $request->filled('tax_number') ? trim($request->tax_number) : null;
                $company->representative_name     = $request->filled('representative_name') ? trim($request->representative_name) : null;
                $company->representative_position = $request->filled('representative_position') ? trim($request->representative_position) : null;
                $company->representative_phone    = $request->filled('representative_phone') ? trim($request->representative_phone) : null;
                $company->verification_status     = $request->verification_status ?? 'pending';
                $company->rejection_reason        = $request->filled('rejection_reason') ? trim($request->rejection_reason) : null;

                if ($request->hasFile('commercial_register_doc')) {
                    $company->commercial_register_doc = $this->saveUploadedFile($request->file('commercial_register_doc'), 'upload/company_docs', 'cr');
                }

                $company->save();
            }
            // 4. Handle Driver Profile
            elseif ($request->role === 'driver') {
                $driver = new DriverProfile();
                $driver->user_id             = $user->id;
                $driver->truck_type_id       = $request->filled('truck_type_id') ? $request->truck_type_id : null;
                $driver->license_number      = $request->filled('license_number') ? trim($request->license_number) : null;
                $driver->wallet_balance      = $request->filled('wallet_balance') ? $request->wallet_balance : 0.00;
                $driver->rating              = $request->filled('rating') ? $request->rating : 5.00;
                $driver->availability_status = $request->availability_status ?? 'offline';
                $driver->verification_status = $request->driver_verification_status ?? 'pending';
                $driver->rejection_reason    = $request->filled('driver_rejection_reason') ? trim($request->driver_rejection_reason) : null;

                if ($request->hasFile('license_photo')) {
                    $driver->license_photo = $this->saveUploadedFile($request->file('license_photo'), 'upload/driver_docs', 'license');
                }

                if ($request->hasFile('truck_registration_photo')) {
                    $driver->truck_registration_photo = $this->saveUploadedFile($request->file('truck_registration_photo'), 'upload/driver_docs', 'truckreg');
                }

                if ($request->hasFile('civil_id_photo')) {
                    $driver->civil_id_photo = $this->saveUploadedFile($request->file('civil_id_photo'), 'upload/driver_docs', 'civilid');
                }

                $driver->save();
            }

            DB::commit();

            try {
                $user->notify(new \App\Notifications\CustomerActivityNotification([
                    'title'        => 'Welcome to Salasil Logistics',
                    'body'         => 'Welcome ' . ($user->fname ?? 'Valued Customer') . '! Your account has been registered successfully. Start booking scheduled trips and tracking shipments with ease.',
                    'type'         => 'system',
                    'reference_id' => $user->client_code ?? ('CLI-' . $user->id),
                    'action_route' => '/profile_screen',
                ]));
            } catch (\Throwable $ignoreWelcome) {}

            try {
                \App\Services\AdminNotificationService::newMemberRegistered($user);
            } catch (\Throwable $ignoreAdminNotif) {}

            // 5. Generate Sanctum Bearer Token
            $token = $user->createToken('auth_token')->plainTextToken;

            // 6. Reload relations for response
            $user->load(['country', 'city', 'companyProfile', 'driverProfile.truckType']);

            $userData = $this->formatUserData($user);

            return response()->json([
                'status'  => true,
                'message' => 'User registered successfully',
                'data'    => [
                    'token'      => $token,
                    'token_type' => 'Bearer',
                    'user'       => $userData,
                ]
            ], 201);

        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('Register API Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            return response()->json([
                'status'  => false,
                'message' => 'Failed to register user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login user with Email, Phone, or Client Code and Password.
     */
    public function login(Request $request)
    {
        // 1. Validation
        $validator = Validator::make($request->all(), [
            'login'          => 'required_without_all:email,phone|string',
            'email'          => 'required_without_all:login,phone|string',
            'phone'          => 'required_without_all:login,email|string',
            'password'       => 'required|string',
            'firebase_token' => 'nullable|string',
        ], [
            'login.required_without_all' => 'Please provide an email to login.',
            'password.required'          => 'Password is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // 2. Identify login credential (Email, Phone, or Client Code)
        $credential = trim($request->email ?? $request->phone ?? $request->login);

        $user = User::where('email', strtolower($credential))
            ->orWhere('phone', $credential)
            ->orWhere('client_code', $credential)
            ->first();

        // 3. Verify Password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        // 4. Check User Account Status
        if ($user->status === 'banned') {
            return response()->json([
                'status'  => false,
                'message' => 'Your account has been banned. Please contact administration.',
            ], 403);
        }

        if ($user->status === 'inactive') {
            return response()->json([
                'status'  => false,
                'message' => 'Your account is currently inactive. Please contact support.',
            ], 403);
        }

        // 5. Update FCM / Firebase Token if provided
        if ($request->filled('firebase_token')) {
            $user->firebase_token = $request->firebase_token;
            $user->save();
        }

        // 6. Generate Sanctum Bearer Token
        $token = $user->createToken('auth_token')->plainTextToken;

        // 7. Load relations
        $user->load(['country', 'city', 'companyProfile', 'driverProfile.truckType']);

        $userData = $this->formatUserData($user);

        return response()->json([
            'status'  => true,
            'message' => 'Login successful',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'user'       => $userData,
            ]
        ], 200);
    }

    /**
     * Get Current Authenticated User profile.
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load(['country', 'city', 'companyProfile', 'driverProfile.truckType']);

        return response()->json([
            'status' => true,
            'data'   => [
                'user' => $this->formatUserData($user)
            ]
        ], 200);
    }

    /**
     * Logout authenticated user (Revoke current token).
     */
    public function logout(Request $request)
    {
        if ($request->user() && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'status'  => true,
            'message' => 'Logged out successfully'
        ], 200);
    }

    /**
     * Send Password Reset Code (Forgot Password).
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Email address is required.',
            'email.email'    => 'Please provide a valid email address.',
            'email.exists'   => 'We could not find a user with that email address.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        // Generate 4-digit OTP
        $otp = (string) rand(1000, 9999);

        // Store or update OTP in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token'      => Hash::make($otp),
                'created_at' => now()
            ]
        );

        // Send OTP verification code to user email
        try {
            Mail::to($email)->send(new ResetPasswordOtpMail($user, $otp));
        } catch (\Throwable $e) {
            Log::error('Failed to send reset password email: ' . $e->getMessage());
        }

        return response()->json([
            'status'     => true,
            'message'    => 'Password reset code sent successfully',
            'debug_code' => app()->environment('local', 'development') ? $otp : null,
        ], 200);
    }

    /**
     * Verify Password Reset Code.
     */
    public function verifyResetCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code'  => 'required|string',
        ], [
            'email.required' => 'Email address is required.',
            'code.required'  => 'Reset code is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->email));
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or expired password reset request.',
            ], 400);
        }

        // Check expiry (60 minutes)
        $createdAt = \Carbon\Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return response()->json([
                'status'  => false,
                'message' => 'Password reset code has expired. Please request a new one.',
            ], 400);
        }

        // Verify OTP code
        if (!Hash::check($request->code, $record->token) && $request->code !== $record->token) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid reset code. Please check and try again.',
            ], 400);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Reset code verified successfully',
        ], 200);
    }

    /**
     * Reset Password with Verified Code.
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'                 => 'required|email|exists:users,email',
            'code'                  => 'required|string',
            'password'              => 'required|string|min:6|confirmed',
        ], [
            'email.required'        => 'Email address is required.',
            'code.required'         => 'Reset code is required.',
            'password.required'     => 'New password is required.',
            'password.min'          => 'Password must be at least 6 characters.',
            'password.confirmed'    => 'Password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->email));
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or expired password reset request.',
            ], 400);
        }

        // Check expiry (60 minutes)
        $createdAt = \Carbon\Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return response()->json([
                'status'  => false,
                'message' => 'Password reset code has expired. Please request a new one.',
            ], 400);
        }

        // Verify OTP
        if (!Hash::check($request->code, $record->token) && $request->code !== $record->token) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid reset code. Please check and try again.',
            ], 400);
        }

        // Update password
        $user = User::where('email', $email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete reset token
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Generate new API token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Reload relations
        $user->load(['country', 'city', 'companyProfile', 'driverProfile.truckType']);

        return response()->json([
            'status'  => true,
            'message' => 'Password reset successfully',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'user'       => $this->formatUserData($user),
            ]
        ], 200);
    }

    /**
     * Format user data and append full asset URLs.
     */
    private function formatUserData(User $user): array
    {
        $userData = $user->toArray();
        $userData['is_verified'] = (bool) ($user->is_verified ?? false);
        $userData['credit'] = (float) ($user->credit ?? 0.000);
        $userData['formatted_credit'] = number_format((float) ($user->credit ?? 0), 3) . ' KWD';
        $userData['photo_url'] = $user->photo ? asset('upload/user_images/' . $user->photo) : null;

        if ($user->companyProfile && $user->companyProfile->commercial_register_doc) {
            $userData['company_profile']['commercial_register_doc_url'] = asset('upload/company_docs/' . $user->companyProfile->commercial_register_doc);
        }

        if ($user->driverProfile) {
            if ($user->driverProfile->license_photo) {
                $userData['driver_profile']['license_photo_url'] = asset('upload/driver_docs/' . $user->driverProfile->license_photo);
            }
            if ($user->driverProfile->truck_registration_photo) {
                $userData['driver_profile']['truck_registration_photo_url'] = asset('upload/driver_docs/' . $user->driverProfile->truck_registration_photo);
            }
            if ($user->driverProfile->civil_id_photo) {
                $userData['driver_profile']['civil_id_photo_url'] = asset('upload/driver_docs/' . $user->driverProfile->civil_id_photo);
            }
        }

        return $userData;
    }

    /**
     * Safely save an uploaded file across public_path and cPanel public_html directories.
     */
    private function saveUploadedFile($file, string $subfolder, string $prefix = ''): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        try {
            $ext = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
            $uniqueName = date('YmdHi') . '_' . ($prefix ? $prefix . '_' : '') . uniqid() . '.' . $ext;

            // Target directory in public_path
            $targetDir = public_path($subfolder);
            if (!file_exists($targetDir)) {
                @mkdir($targetDir, 0777, true);
            }

            // Move uploaded file
            $file->move($targetDir, $uniqueName);

            // Also synchronize with cPanel public_html if exists and differs
            $cpanelUpload = base_path('../public_html/' . $subfolder);
            if (is_dir(base_path('../public_html')) && realpath($targetDir) !== realpath($cpanelUpload)) {
                if (!file_exists($cpanelUpload)) {
                    @mkdir($cpanelUpload, 0777, true);
                }
                @copy($targetDir . '/' . $uniqueName, $cpanelUpload . '/' . $uniqueName);
            }

            return $uniqueName;
        } catch (\Throwable $ex) {
            Log::error('Error saving uploaded file in ' . $subfolder . ': ' . $ex->getMessage());
            return null;
        }
    }

}
