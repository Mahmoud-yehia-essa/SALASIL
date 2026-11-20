<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CompanyProfile;
use App\Models\Country;
use App\Models\DriverProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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
            // Normalize incoming driver / customer fields
            if (!$request->filled('fname')) {
                if ($request->filled('full_name')) {
                    $parts = explode(' ', trim($request->full_name), 2);
                    $request->merge([
                        'fname' => $parts[0],
                        'lname' => $parts[1] ?? ($request->lname ?? ''),
                    ]);
                } elseif ($request->filled('name')) {
                    $parts = explode(' ', trim($request->name), 2);
                    $request->merge([
                        'fname' => $parts[0],
                        'lname' => $parts[1] ?? ($request->lname ?? ''),
                    ]);
                }
            }

            $appType = strtolower(trim((string) $request->input('app_type', '')));
            $locale  = strtolower(trim((string) $request->input('locale', 'ar')));
            $rawEmailInput = $request->filled('email') ? strtolower(trim((string) $request->email)) : null;

            // Block mismatched app_type vs registration role immediately
            if ($appType === 'driver' && $request->filled('role') && $request->role !== 'driver') {
                return $this->buildWrongAppResponse('WRONG_APP_CUSTOMER_ON_DRIVER', $request->role, 'customer', $locale, true);
            }
            if ($appType === 'customer' && $request->role === 'driver') {
                return $this->buildWrongAppResponse('WRONG_APP_DRIVER_ON_CUSTOMER', 'driver', 'driver', $locale, true);
            }

            // Check if this email or phone is already registered under the opposite app role
            $effectiveAppType = $appType !== '' ? $appType : ($request->role === 'driver' ? 'driver' : 'customer');
            $existingUser = $this->findExistingUserByEmailOrPhone(
                $rawEmailInput,
                $request->input('phone'),
                $request->input('country_code')
            );
            if ($existingUser) {
                $wrongAppResp = $this->checkCrossAppRole($existingUser, $effectiveAppType, $locale, true);
                if ($wrongAppResp !== null) {
                    return $wrongAppResp;
                }
            }

            // For driver: email is optional in Figma. If missing, generate fallback driver email.
            if ($request->role === 'driver' && !$request->filled('email')) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone ?? uniqid());
                $request->merge([
                    'email' => 'driver_' . $cleanPhone . '@salasil.local',
                ]);
            }

            // Map truck_type name to truck_type_id if only name is provided
            if ($request->filled('truck_type') && !$request->filled('truck_type_id')) {
                $matchedType = \App\Models\TruckType::where('name_en', 'LIKE', '%' . $request->truck_type . '%')
                    ->orWhere('name_ar', 'LIKE', '%' . $request->truck_type . '%')
                    ->first();
                if ($matchedType) {
                    $request->merge(['truck_type_id' => $matchedType->id]);
                }
            }

            // 1. Validation Rules (Safe from php_fileinfo extension crashes)
            $rules = [
                'fname'           => 'required|string|max:255',
                'lname'           => 'nullable|string|max:255',
                'email'           => 'required|email|max:255|unique:users,email',
                'phone'           => 'nullable|string|max:255',
                'secondary_phone' => 'nullable|string|max:50',
                'password'        => 'required|string|min:6',
                'role'            => 'required|in:individual_customer,company_customer,driver,admin',
                'app_type'        => 'nullable|in:customer,driver',
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
                $rules['phone']                    = 'required|string|max:255';
                $rules['truck_type_id']             = 'nullable|exists:truck_types,id';
                $rules['truck_sub_type_id']         = 'nullable|integer';
                $rules['plate_number']             = 'nullable|string|max:50';
                $rules['plate_country_id']         = 'nullable|integer';
                $rules['plate_country_code']       = 'nullable|string|max:10';
                $rules['model_year']               = 'nullable|string|max:50';
                $rules['administrative_number']    = 'nullable|string|max:100';
                $rules['is_main_truck']            = 'nullable';
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

            // Prevent duplicate phone registrations on same app role (e.g. driver fallback emails)
            if ($request->filled('phone')) {
                $phoneMatchUser = $this->findExistingUserByEmailOrPhone(null, $request->input('phone'), $request->input('country_code'));
                if ($phoneMatchUser) {
                    $phoneMsg = str_starts_with($locale, 'ar')
                        ? 'رقم الهاتف هذا مسجل بالفعل.'
                        : 'This phone number is already registered.';
                    return response()->json([
                        'status'  => false,
                        'message' => $phoneMsg,
                        'errors'  => ['phone' => [$phoneMsg]],
                    ], 422);
                }
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

            // Safe file validation for Driver documents & aliases
            $licenseFile = $request->file('license_photo') ?? ($request->file('driving_licence') ?? $request->file('license'));
            if ($licenseFile) {
                if (!$licenseFile->isValid()) {
                    $fileErrors['license_photo'][] = 'Invalid license document upload.';
                } elseif ($licenseFile->getSize() > 10 * 1024 * 1024) {
                    $fileErrors['license_photo'][] = 'The license document must not exceed 10MB.';
                } else {
                    $ext = strtolower($licenseFile->getClientOriginalExtension());
                    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array($ext, $allowed)) {
                        $fileErrors['license_photo'][] = 'Invalid document format. Allowed formats: ' . implode(', ', $allowed);
                    }
                }
            }

            $truckRegFile = $request->file('truck_registration_photo') ?? ($request->file('vehicle_registration') ?? $request->file('truck_reg'));
            if ($truckRegFile) {
                if (!$truckRegFile->isValid()) {
                    $fileErrors['truck_registration_photo'][] = 'Invalid truck registration upload.';
                } elseif ($truckRegFile->getSize() > 10 * 1024 * 1024) {
                    $fileErrors['truck_registration_photo'][] = 'The truck registration document must not exceed 10MB.';
                } else {
                    $ext = strtolower($truckRegFile->getClientOriginalExtension());
                    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array($ext, $allowed)) {
                        $fileErrors['truck_registration_photo'][] = 'Invalid document format. Allowed formats: ' . implode(', ', $allowed);
                    }
                }
            }

            $civilIdFile = $request->file('civil_id_photo') ?? ($request->file('national_id') ?? $request->file('civil_id'));
            if ($civilIdFile) {
                if (!$civilIdFile->isValid()) {
                    $fileErrors['civil_id_photo'][] = 'Invalid civil ID upload.';
                } elseif ($civilIdFile->getSize() > 10 * 1024 * 1024) {
                    $fileErrors['civil_id_photo'][] = 'The civil ID document must not exceed 10MB.';
                } else {
                    $ext = strtolower($civilIdFile->getClientOriginalExtension());
                    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array($ext, $allowed)) {
                        $fileErrors['civil_id_photo'][] = 'Invalid document format. Allowed formats: ' . implode(', ', $allowed);
                    }
                }
            }

            $passportFile = $request->file('passport_photo') ?? ($request->file('passport') ?? $request->file('passport_doc'));
            if ($passportFile) {
                if (!$passportFile->isValid()) {
                    $fileErrors['passport_photo'][] = 'Invalid passport copy upload.';
                } elseif ($passportFile->getSize() > 10 * 1024 * 1024) {
                    $fileErrors['passport_photo'][] = 'The passport document must not exceed 10MB.';
                } else {
                    $ext = strtolower($passportFile->getClientOriginalExtension());
                    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array($ext, $allowed)) {
                        $fileErrors['passport_photo'][] = 'Invalid document format. Allowed formats: ' . implode(', ', $allowed);
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
                $plateCountryId = null;
                $plateCountryCode = null;
                $resolvedPlateNumber = $request->filled('plate_number') ? trim((string) $request->plate_number) : null;

                if ($request->filled('plate_country_id')) {
                    $plateCountry = Country::find((int) $request->input('plate_country_id'));
                    if ($plateCountry) {
                        $plateCountryId = (int) $plateCountry->id;
                        $plateCountryCode = strtoupper(trim((string) ($plateCountry->code ?? '')));
                    }
                }
                if (!$plateCountryCode && $request->filled('plate_country_code')) {
                    $plateCountryCode = strtoupper(trim((string) $request->input('plate_country_code')));
                    if (!$plateCountryId && $plateCountryCode !== '') {
                        $matchedCountry = Country::whereRaw('UPPER(code) = ?', [$plateCountryCode])->first();
                        if ($matchedCountry) {
                            $plateCountryId = (int) $matchedCountry->id;
                        }
                    }
                }
                if ($resolvedPlateNumber !== null && $resolvedPlateNumber !== '' && $plateCountryCode) {
                    $cleanLocal = preg_replace('/^' . preg_quote($plateCountryCode, '/') . '\s*[-·:|]\s*/i', '', $resolvedPlateNumber);
                    $cleanLocal = trim((string) $cleanLocal);
                    if ($cleanLocal !== '') {
                        $resolvedPlateNumber = $plateCountryCode . ' - ' . $cleanLocal;
                    }
                }

                $driver = new DriverProfile();
                $driver->user_id               = $user->id;
                $driver->truck_type_id         = $request->filled('truck_type_id') ? $request->truck_type_id : null;
                $driver->license_number        = $request->filled('license_number') ? trim($request->license_number) : null;
                $driver->plate_number          = $resolvedPlateNumber;
                $driver->model_year            = $request->filled('model_year') ? trim($request->model_year) : ($request->filled('manufacturing_year') ? trim($request->manufacturing_year) : null);
                $driver->administrative_number = $request->filled('administrative_number') ? trim($request->administrative_number) : ($request->filled('max_load') ? trim($request->max_load) : null);
                $driver->is_main_truck         = $request->has('is_main_truck') ? (bool)$request->is_main_truck : 1;
                $driver->wallet_balance        = $request->filled('wallet_balance') ? $request->wallet_balance : 0.00;
                $driver->rating                = $request->filled('rating') ? $request->rating : 5.00;
                $driver->availability_status   = $request->availability_status ?? 'offline';
                $driver->verification_status   = $request->driver_verification_status ?? 'pending';
                $driver->rejection_reason      = $request->filled('driver_rejection_reason') ? trim($request->driver_rejection_reason) : null;

                if ($licenseFile) {
                    $driver->license_photo = $this->saveUploadedFile($licenseFile, 'upload/driver_docs', 'license');
                }

                if ($truckRegFile) {
                    $driver->truck_registration_photo = $this->saveUploadedFile($truckRegFile, 'upload/driver_docs', 'truckreg');
                }

                if ($civilIdFile) {
                    $driver->civil_id_photo = $this->saveUploadedFile($civilIdFile, 'upload/driver_docs', 'civilid');
                }

                if ($passportFile) {
                    $driver->passport_photo = $this->saveUploadedFile($passportFile, 'upload/driver_docs', 'passport');
                }

                $driver->save();

                // Populate initial default truck in driver_trucks if plate or truck type provided
                if ($driver->truck_type_id || $driver->plate_number) {
                    try {
                        $initialTruckData = [
                            'driver_id'          => $user->id,
                            'truck_type_id'      => $driver->truck_type_id ?? (\App\Models\TruckType::first()->id ?? 1),
                            'truck_sub_type_id'  => $request->filled('truck_sub_type_id') ? (int) $request->input('truck_sub_type_id') : null,
                            'plate_number'       => $driver->plate_number,
                            'manufacturing_year' => $driver->model_year ? (int)$driver->model_year : null,
                            'is_default'         => $driver->is_main_truck ? 1 : 0,
                            'is_verified'        => 0,
                        ];
                        if (Schema::hasColumn('driver_trucks', 'plate_country_id')) {
                            $initialTruckData['plate_country_id'] = $plateCountryId;
                        }
                        if (Schema::hasColumn('driver_trucks', 'plate_country_code')) {
                            $initialTruckData['plate_country_code'] = $plateCountryCode;
                        }
                        \App\Models\DriverTruck::create($initialTruckData);
                    } catch (\Throwable $ignoreTruck) {
                        Log::warning('Driver truck initial creation warning: ' . $ignoreTruck->getMessage());
                    }
                }
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
            'app_type'       => 'nullable|in:customer,driver',
            'locale'         => 'nullable|string|max:10',
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

        $userQuery = User::where('email', strtolower($credential))
            ->orWhere('phone', $credential)
            ->orWhere('client_code', $credential);

        if ($request->filled('country_code') && $request->filled('phone')) {
            $code = trim($request->country_code);
            $rawPhone = trim($request->phone);
            $cleanPhone = preg_replace('/\D+/', '', $rawPhone);
            $fullPhone = $code . $rawPhone;
            $userQuery = $userQuery->orWhere('phone', $cleanPhone)
                ->orWhere('phone', $fullPhone)
                ->orWhere(function ($sq) use ($code, $rawPhone) {
                    $sq->where('country_code', $code)->where('phone', $rawPhone);
                });
        }

        $user = $userQuery->first();

        // 3. Verify Password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        // 3.1 Enforce strict app-role separation (Customer app vs Driver app)
        $wrongAppResp = $this->checkCrossAppRole(
            $user,
            $request->input('app_type'),
            $request->input('locale', $user->locale ?? 'ar'),
            false
        );
        if ($wrongAppResp !== null) {
            return $wrongAppResp;
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

        if ($user && $request->filled('app_type')) {
            $wrongAppResp = $this->checkCrossAppRole(
                $user,
                $request->input('app_type'),
                $request->input('locale', $user->locale ?? 'ar'),
                false
            );
            if ($wrongAppResp !== null) {
                return $wrongAppResp;
            }
        }

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
            if ($user->driverProfile->passport_photo) {
                $userData['driver_profile']['passport_photo_url'] = asset('upload/driver_docs/' . $user->driverProfile->passport_photo);
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

    /**
     * Find an existing user by email or phone variants (used to enforce strict app-role isolation).
     */
    private function findExistingUserByEmailOrPhone(?string $email, ?string $phone, ?string $countryCode = null): ?User
    {
        $cleanEmail = $email ? strtolower(trim($email)) : '';
        $rawPhone = $phone ? trim($phone) : '';
        $cleanPhone = $rawPhone !== '' ? preg_replace('/\D+/', '', $rawPhone) : '';
        $code = $countryCode ? trim($countryCode) : '';
        $cleanCode = $code !== '' ? preg_replace('/\D+/', '', $code) : '';

        $phoneVariants = [];
        if (strlen($cleanPhone) >= 6) {
            $trimmedLeadingZero = ltrim($cleanPhone, '0');
            $phoneVariants = array_values(array_unique(array_filter([
                $rawPhone,
                $cleanPhone,
                $trimmedLeadingZero,
                $code !== '' ? ($code . $cleanPhone) : null,
                $code !== '' && $trimmedLeadingZero !== '' ? ($code . $trimmedLeadingZero) : null,
                $cleanCode !== '' ? ($cleanCode . $cleanPhone) : null,
            ])));
        }

        if ($cleanEmail === '' && empty($phoneVariants)) {
            return null;
        }

        return User::where(function ($q) use ($cleanEmail, $phoneVariants, $code, $rawPhone, $cleanPhone) {
            if ($cleanEmail !== '') {
                $q->whereRaw('LOWER(email) = ?', [$cleanEmail]);
            }
            if (!empty($phoneVariants)) {
                $method = $cleanEmail !== '' ? 'orWhereIn' : 'whereIn';
                $q->$method('phone', $phoneVariants);
                if ($code !== '') {
                    $q->orWhere(function ($sq) use ($code, $rawPhone, $cleanPhone) {
                        $sq->where('country_code', $code)
                           ->whereIn('phone', array_values(array_unique(array_filter([$rawPhone, $cleanPhone]))));
                    });
                }
            }
        })->first();
    }

    /**
     * Ensure customer accounts only use the Customer app and driver accounts only use the Driver app.
     */
    private function checkCrossAppRole(
        User $user,
        ?string $appType,
        ?string $locale = 'ar',
        bool $isRegister = false
    ) {
        $normalizedApp = strtolower(trim((string) $appType));
        if (!in_array($normalizedApp, ['customer', 'driver'], true)) {
            return null;
        }

        $role = strtolower(trim((string) $user->role));
        $lang = strtolower(trim((string) ($locale ?: 'ar')));

        if ($normalizedApp === 'driver' && $role !== 'driver') {
            if ($role === 'admin') {
                return $this->buildWrongAppResponse('WRONG_APP_ADMIN_ON_DRIVER', $role, 'admin', $lang, $isRegister);
            }
            return $this->buildWrongAppResponse('WRONG_APP_CUSTOMER_ON_DRIVER', $role, 'customer', $lang, $isRegister);
        }

        if ($normalizedApp === 'customer' && $role === 'driver') {
            return $this->buildWrongAppResponse('WRONG_APP_DRIVER_ON_CUSTOMER', $role, 'driver', $lang, $isRegister);
        }

        return null;
    }

    private function buildWrongAppResponse(
        string $errorCode,
        string $userRole,
        string $expectedApp,
        string $locale,
        bool $isRegister
    ) {
        $isAr = str_starts_with($locale, 'ar');

        if ($errorCode === 'WRONG_APP_CUSTOMER_ON_DRIVER') {
            $message = $isAr
                ? ($isRegister
                    ? 'هذا المستخدم مسجل كعميل (Customer)، لا يستطيع التسجيل أو الدخول على تطبيق السائق (Driver). الرجاء الدخول من خلال تطبيق العميل.'
                    : 'هذا المستخدم مسجل كعميل (Customer)، لا يستطيع الدخول على تطبيق السائق (Driver). الرجاء الدخول من خلال تطبيق العميل.')
                : ($isRegister
                    ? 'This user is registered as a Customer and cannot register or log in on the Driver app. Please sign in through the Customer app.'
                    : 'This user is registered as a Customer and cannot log in to the Driver app. Please sign in through the Customer app.');
        } elseif ($errorCode === 'WRONG_APP_DRIVER_ON_CUSTOMER') {
            $message = $isAr
                ? ($isRegister
                    ? 'هذا المستخدم مسجل كسائق (Driver)، لا يستطيع التسجيل أو الدخول على تطبيق العميل (Customer). الرجاء الدخول من خلال تطبيق السائق.'
                    : 'هذا المستخدم مسجل كسائق (Driver)، لا يستطيع الدخول على تطبيق العميل (Customer). الرجاء الدخول من خلال تطبيق السائق.')
                : ($isRegister
                    ? 'This user is registered as a Driver and cannot register or log in on the Customer app. Please sign in through the Driver app.'
                    : 'This user is registered as a Driver and cannot log in to the Customer app. Please sign in through the Driver app.');
        } else {
            $message = $isAr
                ? 'حساب الإدارة لا يمكنه الدخول إلى تطبيق السائق. الرجاء استخدام لوحة التحكم.'
                : 'Admin accounts cannot log in to the Driver app. Please use the admin dashboard.';
        }

        return response()->json([
            'status'       => false,
            'error_code'   => $errorCode,
            'user_role'    => $userRole,
            'expected_app' => $expectedApp,
            'message'      => $message,
        ], 403);
    }

}
