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

class ProfileController extends Controller
{
    /**
     * Get Current Authenticated User Profile.
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();
        $user->load(['country', 'city', 'companyProfile', 'driverProfile.truckType', 'trucks.truckType', 'trucks.brand', 'trucks.model']);

        return response()->json([
            'status'  => true,
            'message' => 'Profile retrieved successfully',
            'data'    => [
                'user' => $this->formatUserData($user)
            ]
        ], 200);
    }

    /**
     * Update Current Authenticated User Profile.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        try {
            // 1. Base Validation Rules (Safe from php_fileinfo extension crashes)
            $rules = [
                'fname'           => 'required|string|max:255',
                'lname'           => 'nullable|string|max:255',
                'email'           => 'required|email|max:255|unique:users,email,' . $user->id,
                'phone'           => 'nullable|string|max:255',
                'secondary_phone' => 'nullable|string|max:50',
                'country_code'    => 'nullable|string|max:10',
                'country_id'      => 'nullable|exists:countries,id',
                'city_id'         => 'nullable|exists:cities,id',
                'dateofbirth'     => 'nullable|date',
                'address'         => 'nullable|string',
                'locale'          => 'nullable|string|in:ar,en',
                'firebase_token'  => 'nullable|string',
            ];

            // Conditional Rules based on User Role
            if ($user->role === 'company_customer') {
                $rules['company_name']            = 'required|string|max:255';
                $rules['commercial_register']     = 'required|string|max:100';
                $rules['civil_id']                = 'required|string|max:50';
                $rules['tax_number']              = 'nullable|string|max:100';
                $rules['representative_name']     = 'nullable|string|max:255';
                $rules['representative_position'] = 'nullable|string|max:255';
                $rules['representative_phone']    = 'nullable|string|max:100';
            } elseif ($user->role === 'driver') {
                $rules['truck_type_id']             = 'nullable|exists:truck_types,id';
                $rules['license_number']           = 'nullable|string|max:255';
                $rules['availability_status']      = 'nullable|in:available,busy,offline';
            }

            $messages = [
                'fname.required'               => 'First name is required.',
                'email.required'               => 'Email address is required.',
                'email.unique'                 => 'This email address is registered by another account.',
                'company_name.required'        => 'Company name is required.',
                'commercial_register.required' => 'Commercial register (CR) number is required.',
                'civil_id.required'            => 'Civil ID / National ID is required.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation error',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            // Safe File Validation
            $fileErrors = [];

            if ($request->hasFile('photo')) {
                $f = $request->file('photo');
                if (!$f->isValid()) {
                    $fileErrors['photo'][] = 'Invalid photo file upload.';
                } elseif ($f->getSize() > 5 * 1024 * 1024) {
                    $fileErrors['photo'][] = 'The photo must not exceed 5MB.';
                } else {
                    $ext = strtolower($f->getClientOriginalExtension());
                    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'];
                    if (!in_array($ext, $allowed)) {
                        $fileErrors['photo'][] = 'Invalid photo format. Allowed formats: ' . implode(', ', $allowed);
                    }
                }
            }

            if ($request->hasFile('commercial_register_doc')) {
                $f = $request->file('commercial_register_doc');
                if (!$f->isValid()) {
                    $fileErrors['commercial_register_doc'][] = 'Invalid commercial register document upload.';
                } elseif ($f->getSize() > 10 * 1024 * 1024) {
                    $fileErrors['commercial_register_doc'][] = 'The document must not exceed 10MB.';
                } else {
                    $ext = strtolower($f->getClientOriginalExtension());
                    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array($ext, $allowed)) {
                        $fileErrors['commercial_register_doc'][] = 'Invalid document format. Allowed formats: ' . implode(', ', $allowed);
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

            // 2. Update User Main Data
            $user->fname           = trim($request->fname);
            $user->lname           = $request->filled('lname') ? trim($request->lname) : null;
            $user->email           = strtolower(trim($request->email));
            $user->phone           = $request->filled('phone') ? trim($request->phone) : null;
            $user->secondary_phone = $request->filled('secondary_phone') ? trim($request->secondary_phone) : null;
            $user->country_code    = $request->country_code ?? $user->country_code ?? '+966';
            $user->country_id      = $request->filled('country_id') ? $request->country_id : null;
            $user->city_id         = $request->filled('city_id') ? $request->city_id : null;
            $user->dateofbirth     = $request->filled('dateofbirth') ? $request->dateofbirth : null;
            $user->address         = $request->filled('address') ? trim($request->address) : null;
            $user->locale          = $request->locale ?? $user->locale ?? 'ar';

            if ($request->filled('firebase_token')) {
                $user->firebase_token = $request->firebase_token;
            }

            // Update Profile Photo safely
            if ($request->hasFile('photo')) {
                $uploadedPhoto = $this->saveUploadedFile($request->file('photo'), 'upload/user_images');
                if ($uploadedPhoto) {
                    $this->deleteOldFile('upload/user_images/' . $user->photo);
                    $user->photo = $uploadedPhoto;
                }
            }

            $user->save();

            // 3. Update Company Profile (if applicable)
            if ($user->role === 'company_customer') {
                $company = CompanyProfile::firstOrNew(['user_id' => $user->id]);
                $company->company_name            = trim($request->company_name);
                $company->commercial_register     = trim($request->commercial_register);
                $company->civil_id                = trim($request->civil_id);
                $company->tax_number              = $request->filled('tax_number') ? trim($request->tax_number) : null;
                $company->representative_name     = $request->filled('representative_name') ? trim($request->representative_name) : null;
                $company->representative_position = $request->filled('representative_position') ? trim($request->representative_position) : null;
                $company->representative_phone    = $request->filled('representative_phone') ? trim($request->representative_phone) : null;

                if ($request->hasFile('commercial_register_doc')) {
                    $uploadedDoc = $this->saveUploadedFile($request->file('commercial_register_doc'), 'upload/company_docs', 'cr');
                    if ($uploadedDoc) {
                        $this->deleteOldFile('upload/company_docs/' . $company->commercial_register_doc);
                        $company->commercial_register_doc = $uploadedDoc;
                    }
                }

                $company->save();
            }
            // 4. Update Driver Profile (if applicable)
            elseif ($user->role === 'driver') {
                $driver = DriverProfile::firstOrNew(['user_id' => $user->id]);
                $driver->truck_type_id       = $request->filled('truck_type_id') ? $request->truck_type_id : null;
                $driver->license_number      = $request->filled('license_number') ? trim($request->license_number) : null;
                if ($request->filled('availability_status')) {
                    $driver->availability_status = $request->availability_status;
                }

                if ($request->hasFile('license_photo')) {
                    $uploaded = $this->saveUploadedFile($request->file('license_photo'), 'upload/driver_docs', 'license');
                    if ($uploaded) {
                        $this->deleteOldFile('upload/driver_docs/' . $driver->license_photo);
                        $driver->license_photo = $uploaded;
                    }
                }

                if ($request->hasFile('truck_registration_photo')) {
                    $uploaded = $this->saveUploadedFile($request->file('truck_registration_photo'), 'upload/driver_docs', 'truckreg');
                    if ($uploaded) {
                        $this->deleteOldFile('upload/driver_docs/' . $driver->truck_registration_photo);
                        $driver->truck_registration_photo = $uploaded;
                    }
                }

                if ($request->hasFile('civil_id_photo')) {
                    $uploaded = $this->saveUploadedFile($request->file('civil_id_photo'), 'upload/driver_docs', 'civilid');
                    if ($uploaded) {
                        $this->deleteOldFile('upload/driver_docs/' . $driver->civil_id_photo);
                        $driver->civil_id_photo = $uploaded;
                    }
                }

                $driver->save();
            }

            DB::commit();

            // 5. Reload relations & format response
            $user->load(['country', 'city', 'companyProfile', 'driverProfile.truckType', 'trucks.truckType', 'trucks.brand', 'trucks.model']);

            return response()->json([
                'status'  => true,
                'message' => 'Profile updated successfully',
                'data'    => [
                    'user' => $this->formatUserData($user)
                ]
            ], 200);

        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            \Illuminate\Support\Facades\Log::error('Profile API Update Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update profile: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change Password     * Change Password for Authenticated User.
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password'      => 'required|string',
            'new_password'          => 'required|string|min:6|different:current_password|confirmed',
        ], [
            'current_password.required'  => 'Current password is required.',
            'new_password.required'      => 'New password is required.',
            'new_password.min'           => 'New password must be at least 6 characters.',
            'new_password.different'     => 'New password cannot be the same as the current password.',
            'new_password.confirmed'     => 'New password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Current password is incorrect.',
                'errors'  => [
                    'current_password' => ['The provided current password does not match our records.']
                ]
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Password changed successfully'
        ], 200);
    }

    /**
     * Update FCM / Firebase Token.
     */
    public function updateFirebaseToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firebase_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Firebase token is required.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $user->firebase_token = $request->firebase_token;
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Firebase token updated successfully'
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

            $targetDir = public_path($subfolder);
            if (!file_exists($targetDir)) {
                @mkdir($targetDir, 0777, true);
            }

            $file->move($targetDir, $uniqueName);

            $cpanelUpload = base_path('../public_html/' . $subfolder);
            if (is_dir(base_path('../public_html')) && realpath($targetDir) !== realpath($cpanelUpload)) {
                if (!file_exists($cpanelUpload)) {
                    @mkdir($cpanelUpload, 0777, true);
                }
                @copy($targetDir . '/' . $uniqueName, $cpanelUpload . '/' . $uniqueName);
            }

            return $uniqueName;
        } catch (\Throwable $ex) {
            \Illuminate\Support\Facades\Log::error('Error saving file in ' . $subfolder . ': ' . $ex->getMessage());
            return null;
        }
    }

    /**
     * Safely delete an old file from both public_path and public_html.
     */
    private function deleteOldFile(?string $relativePath): void
    {
        if (empty($relativePath)) return;

        $p1 = public_path($relativePath);
        if (file_exists($p1)) {
            @unlink($p1);
        }

        $p2 = base_path('../public_html/' . $relativePath);
        if (file_exists($p2)) {
            @unlink($p2);
        }
    }

}
