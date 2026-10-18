<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CompanyProfile;
use App\Models\DriverProfile;
use App\Models\Country;
use App\Models\City;
use App\Models\UserCompanyDocument;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Show form to add a new client/user.
     */
    public function AddUser()
    {
        $countries = Country::where('is_active', 1)->orderBy('name_en', 'asc')->get();
        return view('admin.backend.user.add_user', compact('countries'));
    }

    /**
     * Store new client/user in database.
     */
    public function StoreUser(Request $request)
    {
        $rules = [
            'fname' => 'required|string|max:255',
            'lname' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:255',
            'secondary_phone' => 'nullable|string|max:255',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,individual_customer,company_customer,driver',
            'status' => 'required|in:active,inactive,pending,banned',
            'is_verified' => 'nullable',
            'credit' => 'nullable|numeric|min:0',
            'country_code' => 'nullable|string|max:10',
            'country_id' => 'nullable|exists:countries,id',
            'city_id' => 'nullable|exists:cities,id',
            'dateofbirth' => 'nullable|date',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ];

        if ($request->role === 'company_customer') {
            $rules['company_name'] = 'required|string|max:255';
            $rules['commercial_register'] = 'required|string|max:100';
            $rules['commercial_register_doc'] = 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120';
            $rules['civil_id'] = 'required|string|max:50';
            $rules['tax_number'] = 'nullable|string|max:100';
            $rules['representative_name'] = 'nullable|string|max:255';
            $rules['representative_position'] = 'nullable|string|max:255';
            $rules['representative_phone'] = 'nullable|string|max:100';
            $rules['verification_status'] = 'nullable|in:pending,verified,rejected';
            $rules['rejection_reason'] = 'nullable|string';
        } elseif ($request->role === 'driver') {
            $rules['license_number'] = 'nullable|string|max:255';
            $rules['license_photo'] = 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120';
            $rules['truck_registration_photo'] = 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120';
            $rules['civil_id_photo'] = 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120';
            $rules['wallet_balance'] = 'nullable|numeric|min:0';
            $rules['rating'] = 'nullable|numeric|min:1|max:5';
            $rules['availability_status'] = 'nullable|in:available,busy,offline';
            $rules['driver_verification_status'] = 'nullable|in:pending,verified,rejected';
            $rules['driver_rejection_reason'] = 'nullable|string';
        }

        $request->validate($rules, [
            'fname.required' => 'First name is required.',
            'email.required' => 'Email address is required.',
            'email.unique' => 'This email address is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'company_name.required' => 'Company legal name is required.',
            'commercial_register.required' => 'Commercial register (CR) number is required.',
            'civil_id.required' => 'Civil ID / National ID is required for corporate account.',
        ]);

        $user = new User();
        $user->fname = $request->fname;
        $user->lname = $request->lname;
        $user->email = strtolower(trim($request->email));
        $user->phone = $request->phone;
        $user->secondary_phone = $request->secondary_phone;
        $user->password = Hash::make($request->password);
        $user->role = $request->role;
        $user->status = $request->status;
        $user->is_verified = $request->has('is_verified') ? (bool) $request->is_verified : false;
        $user->credit = $request->filled('credit') ? (float) $request->credit : 0.00;
        $user->country_code = $request->country_code ?? '+966';
        $user->country_id = $request->country_id ? $request->country_id : null;
        $user->city_id = $request->city_id ? $request->city_id : null;
        $user->dateofbirth = $request->dateofbirth;
        $user->address = $request->address;
        $user->locale = $request->locale ?? 'en';

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/user_images'), $filename);
            $user->photo = $filename;
        }

        $user->save();

        if ($request->role === 'company_customer') {
            $company = new CompanyProfile();
            $company->user_id = $user->id;
            $company->company_name = trim($request->company_name);
            $company->commercial_register = trim($request->commercial_register);
            $company->civil_id = trim($request->civil_id);
            $company->tax_number = $request->tax_number ? trim($request->tax_number) : null;
            $company->representative_name = $request->representative_name ? trim($request->representative_name) : null;
            $company->representative_position = $request->representative_position ? trim($request->representative_position) : null;
            $company->representative_phone = $request->representative_phone ? trim($request->representative_phone) : null;
            $company->verification_status = $request->verification_status ?? 'pending';
            $company->rejection_reason = $request->rejection_reason ? trim($request->rejection_reason) : null;

            if ($request->hasFile('commercial_register_doc')) {
                $doc = $request->file('commercial_register_doc');
                $docName = date('YmdHi') . '_cr_' . uniqid() . '.' . $doc->getClientOriginalExtension();
                $doc->move(public_path('upload/company_docs'), $docName);
                $company->commercial_register_doc = $docName;
            }

            $company->save();
        } elseif ($request->role === 'driver') {
            $driver = new DriverProfile();
            $driver->user_id = $user->id;
            $driver->license_number = $request->license_number ? trim($request->license_number) : null;
            $driver->wallet_balance = $request->wallet_balance ?? 0.00;
            $driver->rating = $request->rating ?? 5.00;
            $driver->availability_status = $request->availability_status ?? 'offline';
            $driver->verification_status = $request->driver_verification_status ?? 'pending';
            $driver->rejection_reason = $request->driver_rejection_reason ? trim($request->driver_rejection_reason) : null;

            if ($request->hasFile('license_photo')) {
                $f = $request->file('license_photo');
                $fn = date('YmdHi') . '_license_' . uniqid() . '.' . $f->getClientOriginalExtension();
                $f->move(public_path('upload/driver_docs'), $fn);
                $driver->license_photo = $fn;
            }

            if ($request->hasFile('truck_registration_photo')) {
                $f = $request->file('truck_registration_photo');
                $fn = date('YmdHi') . '_truckreg_' . uniqid() . '.' . $f->getClientOriginalExtension();
                $f->move(public_path('upload/driver_docs'), $fn);
                $driver->truck_registration_photo = $fn;
            }

            if ($request->hasFile('civil_id_photo')) {
                $f = $request->file('civil_id_photo');
                $fn = date('YmdHi') . '_civilid_' . uniqid() . '.' . $f->getClientOriginalExtension();
                $f->move(public_path('upload/driver_docs'), $fn);
                $driver->civil_id_photo = $fn;
            }

            $driver->save();
        }

        try {
            \App\Services\AdminNotificationService::newMemberRegistered($user);
        } catch (\Throwable $ignoreAdminNotif) {}

        $notification = [
            'message' => 'New Client Created Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.owners')->with($notification);
    }

    /**
     * List all registered clients/users with filter capability.
     */
    public function AllOwners(Request $request)
    {
        $query = User::with(['country', 'city']);

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('client_code', 'like', "%{$search}%")
                  ->orWhere('fname', 'like', "%{$search}%")
                  ->orWhere('lname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('secondary_phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->get();

        return view('admin.backend.user.all_owners', compact('users'));
    }

    /**
     * AJAX endpoint to filter clients/users list.
     */
    public function FilterUsersAjax(Request $request)
    {
        $query = User::with(['country', 'city']);

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('client_code', 'like', "%{$search}%")
                  ->orWhere('fname', 'like', "%{$search}%")
                  ->orWhere('lname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('secondary_phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->get();

        $html = view('admin.backend.user.user_table_rows', compact('users'))->render();

        return response()->json([
            'status' => 'success',
            'html' => $html,
            'count' => count($users)
        ]);
    }

    /**
     * Public Digital Verification Profile Page (accessible via QR Code or link).
     */
    public function PublicUserProfile($code)
    {
        $user = User::with(['companyProfile', 'driverProfile', 'trucks.truckType', 'trucks.truckSubType', 'country', 'city'])
                    ->where('client_code', $code)
                    ->orWhere('id', $code)
                    ->firstOrFail();

        return view('admin.backend.user.public_user_profile', compact('user'));
    }

    /**
     * Show form to edit client/user details.
     */
    public function EditUser($id)
    {
        $user = User::with(['companyProfile', 'driverProfile', 'country', 'city', 'companyDocuments.requiredDocument'])->findOrFail($id);
        $countries = Country::where('is_active', 1)->orderBy('name_en', 'asc')->get();
        $cities = $user->country_id ? City::where('country_id', $user->country_id)->where('is_active', 1)->orderBy('name_en', 'asc')->get() : collect();
        $availableCompanyDocuments = \App\Models\CompanyRequiredDocument::where('is_active', 1)
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')->orWhere('user_id', $user->id);
            })
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        return view('admin.backend.user.edit_user', compact('user', 'countries', 'cities', 'availableCompanyDocuments'));
    }

    /**
     * Update client/user details in database.
     */
    public function UpdateUser(Request $request)
    {
        $id = $request->id;
        $user = User::findOrFail($id);

        $rules = [
            'fname' => 'required|string|max:255',
            'lname' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:255',
            'secondary_phone' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,individual_customer,company_customer,driver',
            'status' => 'required|in:active,inactive,pending,banned',
            'is_verified' => 'nullable',
            'credit' => 'nullable|numeric|min:0',
            'country_code' => 'nullable|string|max:10',
            'country_id' => 'nullable|exists:countries,id',
            'city_id' => 'nullable|exists:cities,id',
            'dateofbirth' => 'nullable|date',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ];

        if ($request->role === 'company_customer') {
            $rules['company_name'] = 'required|string|max:255';
            $rules['commercial_register'] = 'required|string|max:100';
            $rules['commercial_register_doc'] = 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120';
            $rules['civil_id'] = 'required|string|max:50';
            $rules['tax_number'] = 'nullable|string|max:100';
            $rules['representative_name'] = 'nullable|string|max:255';
            $rules['representative_position'] = 'nullable|string|max:255';
            $rules['representative_phone'] = 'nullable|string|max:100';
            $rules['verification_status'] = 'nullable|in:pending,verified,rejected';
            $rules['rejection_reason'] = 'nullable|string';
        } elseif ($request->role === 'driver') {
            $rules['license_number'] = 'nullable|string|max:255';
            $rules['license_photo'] = 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120';
            $rules['truck_registration_photo'] = 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120';
            $rules['civil_id_photo'] = 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120';
            $rules['wallet_balance'] = 'nullable|numeric|min:0';
            $rules['rating'] = 'nullable|numeric|min:1|max:5';
            $rules['availability_status'] = 'nullable|in:available,busy,offline';
            $rules['driver_verification_status'] = 'nullable|in:pending,verified,rejected';
            $rules['driver_rejection_reason'] = 'nullable|string';
        }

        $request->validate($rules, [
            'fname.required' => 'First name is required.',
            'email.required' => 'Email address is required.',
            'email.unique' => 'This email address is registered by another account.',
            'company_name.required' => 'Company legal name is required.',
            'commercial_register.required' => 'Commercial register (CR) number is required.',
            'civil_id.required' => 'Civil ID / National ID is required for corporate account.',
        ]);

        $user->fname = $request->fname;
        $user->lname = $request->lname;
        $user->email = strtolower(trim($request->email));
        $user->phone = $request->phone;
        $user->secondary_phone = $request->secondary_phone;
        $user->role = $request->role;
        $user->status = $request->status;
        $user->is_verified = $request->has('is_verified') ? (bool) $request->is_verified : false;
        $user->credit = $request->filled('credit') ? (float) $request->credit : 0.00;
        $user->country_id = $request->country_id ? $request->country_id : null;
        $user->city_id = $request->city_id ? $request->city_id : null;
        $user->dateofbirth = $request->dateofbirth;
        $user->address = $request->address;
        $user->locale = $request->locale ?? 'en';

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            @unlink(public_path('upload/user_images/' . $user->photo));
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/user_images'), $filename);
            $user->photo = $filename;
        }

                $wasVerified = (bool) $user->getOriginal('is_verified');
        $prevCredit  = (float) $user->getOriginal('credit');
$user->save();
        $newCreditVal = (float) $user->credit;
        $diffCredit = $newCreditVal - $prevCredit;
        if (abs($diffCredit) > 0.001) {
            \App\Models\WalletTransaction::create([
                'user_id'       => $user->id,
                'amount'        => abs($diffCredit),
                'type'          => $diffCredit > 0 ? 'deposit' : 'withdrawal',
                'balance_after' => $newCreditVal,
                'description'   => $diffCredit > 0 
                    ? 'Balance recharge from Administration (شحن رصيد من الإدارة)' 
                    : 'Balance adjustment by Administration (تعديل رصيد من الإدارة)',
            ]);
        }

        try {
            if (!$wasVerified && $user->is_verified) {
                $user->notify(new \App\Notifications\CustomerActivityNotification([
                    'title'        => 'Account Verified',
                    'body'         => 'Congratulations! Your profile has been officially verified by Salasil Logistics.',
                    'type'         => 'system',
                    'reference_id' => 'VERIFIED-' . $user->id,
                    'action_route' => '/personal_information_screen',
                ]));
            }
            if ((float) $user->credit > $prevCredit) {
                $diff = (float) $user->credit - $prevCredit;
                $user->notify(new \App\Notifications\CustomerActivityNotification([
                    'title'        => 'Wallet Credited',
                    'body'         => 'An amount of ' . number_format($diff, 3) . ' KWD has been added to your balance by Salasil administration.',
                    'type'         => 'payment',
                    'reference_id' => 'CREDIT-' . $user->id,
                    'action_route' => '/profile_screen',
                ]));
            }
        } catch (\Throwable $ignoreUpdateNotif) {}

        if ($request->role === 'company_customer') {
            $company = CompanyProfile::firstOrNew(['user_id' => $user->id]);
            $company->company_name = trim($request->company_name);
            $company->commercial_register = trim($request->commercial_register);
            $company->civil_id = trim($request->civil_id);
            $company->tax_number = $request->tax_number ? trim($request->tax_number) : null;
            $company->representative_name = $request->representative_name ? trim($request->representative_name) : null;
            $company->representative_position = $request->representative_position ? trim($request->representative_position) : null;
            $company->representative_phone = $request->representative_phone ? trim($request->representative_phone) : null;
            $company->verification_status = $request->verification_status ?? 'pending';
            $company->rejection_reason = $request->rejection_reason ? trim($request->rejection_reason) : null;

            if ($request->hasFile('commercial_register_doc')) {
                $doc = $request->file('commercial_register_doc');
                if (!empty($company->commercial_register_doc) && file_exists(public_path('upload/company_docs/' . $company->commercial_register_doc))) {
                    @unlink(public_path('upload/company_docs/' . $company->commercial_register_doc));
                }
                $docName = date('YmdHi') . '_cr_' . uniqid() . '.' . $doc->getClientOriginalExtension();
                $doc->move(public_path('upload/company_docs'), $docName);
                $company->commercial_register_doc = $docName;
            }

            // Save required documents selection for this company
            if ($request->has('required_documents')) {
                $company->required_documents = array_map('intval', (array)$request->required_documents);
            }

            $company->save();

            // Optional: Dispatch notification if admin checked send notification checkbox
            if ($request->boolean('send_doc_request_notification')) {
                try {
                    $selectedIds = (array)($company->required_documents ?? []);
                    $docNames = \App\Models\CompanyRequiredDocument::whereIn('id', $selectedIds)->pluck('name_en')->toArray();
                    $docNamesList = !empty($docNames) ? implode(', ', $docNames) : 'Official company documents';
                    $customNote = $request->filled('doc_request_custom_note') ? trim($request->doc_request_custom_note) : null;
                    $body = $customNote ?: ("Administration requests you to upload required documents: {$docNamesList}. Please upload them in your Company Documents section to complete account verification.");

                    $user->notify(new \App\Notifications\CustomerActivityNotification([
                        'title'        => 'Company Documents Upload Requested',
                        'body'         => $body,
                        'type'         => 'system',
                        'reference_id' => 'DOC-REQ-' . $user->id . '-' . time(),
                        'action_route' => '/company_documents_screen',
                        'metadata'     => [
                            'user_id'       => $user->id,
                            'documents'     => $docNames,
                            'requested_ids' => $selectedIds,
                        ],
                    ]));
                } catch (\Throwable $ignoreNotif) {}
            }
        } elseif ($request->role === 'driver') {
            $driver = DriverProfile::firstOrNew(['user_id' => $user->id]);
            $driver->license_number = $request->license_number ? trim($request->license_number) : null;
            if ($request->filled('wallet_balance')) {
                $driver->wallet_balance = $request->wallet_balance;
            }
            if ($request->filled('rating')) {
                $driver->rating = $request->rating;
            }
            $driver->availability_status = $request->availability_status ?? 'offline';
            $driver->verification_status = $request->driver_verification_status ?? 'pending';
            $driver->rejection_reason = $request->driver_rejection_reason ? trim($request->driver_rejection_reason) : null;

            if ($request->hasFile('license_photo')) {
                $f = $request->file('license_photo');
                if (!empty($driver->license_photo) && file_exists(public_path('upload/driver_docs/' . $driver->license_photo))) {
                    @unlink(public_path('upload/driver_docs/' . $driver->license_photo));
                }
                $fn = date('YmdHi') . '_license_' . uniqid() . '.' . $f->getClientOriginalExtension();
                $f->move(public_path('upload/driver_docs'), $fn);
                $driver->license_photo = $fn;
            }

            if ($request->hasFile('truck_registration_photo')) {
                $f = $request->file('truck_registration_photo');
                if (!empty($driver->truck_registration_photo) && file_exists(public_path('upload/driver_docs/' . $driver->truck_registration_photo))) {
                    @unlink(public_path('upload/driver_docs/' . $driver->truck_registration_photo));
                }
                $fn = date('YmdHi') . '_truckreg_' . uniqid() . '.' . $f->getClientOriginalExtension();
                $f->move(public_path('upload/driver_docs'), $fn);
                $driver->truck_registration_photo = $fn;
            }

            if ($request->hasFile('civil_id_photo')) {
                $f = $request->file('civil_id_photo');
                if (!empty($driver->civil_id_photo) && file_exists(public_path('upload/driver_docs/' . $driver->civil_id_photo))) {
                    @unlink(public_path('upload/driver_docs/' . $driver->civil_id_photo));
                }
                $fn = date('YmdHi') . '_civilid_' . uniqid() . '.' . $f->getClientOriginalExtension();
                $f->move(public_path('upload/driver_docs'), $fn);
                $driver->civil_id_photo = $fn;
            }

            $driver->save();
        }

        $notification = [
            'message' => 'Client Profile Updated Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.owners')->with($notification);
    }

    /**
     * Change client status asynchronously via AJAX.
     */
    public function ChangeStatusAjax(Request $request)
    {
        $userId = $request->input('user_id') ?? $request->input('id');
        $status = $request->input('status');

        if (!$userId || !$status) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid parameters provided.'
            ], 422);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client account not found.'
            ], 404);
        }

        $user->status = $status;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Client status updated to ' . ucfirst($status) . ' successfully!'
        ]);
    }

    /**
     * Toggle client status between active and inactive.
     */
    public function ToggleStatusUser($id)
    {
        $user = User::findOrFail($id);
        $user->status = ($user->status === 'active') ? 'inactive' : 'active';
        $user->save();

        $statusMsg = ($user->status === 'active') ? 'Client Activated Successfully!' : 'Client Deactivated Successfully!';

        $notification = [
            'message' => $statusMsg,
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * Toggle user verification status via AJAX.
     */
    public function ToggleVerificationAjax(Request $request)
    {
        $oldCreditVal = (float) $user->credit;
        $userId = $request->input('user_id') ?? $request->input('id');
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        $user->is_verified = !$user->is_verified;
        if ($request->filled('credit')) {
            $user->credit = (float) $request->credit;
        }
        $user->save();
        if ($request->filled('credit')) {
            $diffCredit = (float) $user->credit - $oldCreditVal;
            if (abs($diffCredit) > 0.001) {
                \App\Models\WalletTransaction::create([
                    'user_id'       => $user->id,
                    'amount'        => abs($diffCredit),
                    'type'          => $diffCredit > 0 ? 'deposit' : 'withdrawal',
                    'balance_after' => (float) $user->credit,
                    'description'   => $diffCredit > 0 
                        ? 'Balance recharge from Administration (شحن رصيد من الإدارة)' 
                        : 'Balance adjustment by Administration (تعديل رصيد من الإدارة)',
                ]);
            }
        }

        try {
            if ($user->is_verified) {
                $user->notify(new \App\Notifications\CustomerActivityNotification([
                    'title'        => 'Account Verified',
                    'body'         => 'Congratulations! Your profile has been officially verified by Salasil Logistics.',
                    'type'         => 'system',
                    'reference_id' => 'VERIFIED-' . $user->id,
                    'action_route' => '/personal_information_screen',
                ]));
            }
            if ($request->filled('credit') && (float) $request->credit > 0) {
                $user->notify(new \App\Notifications\CustomerActivityNotification([
                    'title'        => 'Wallet Credited',
                    'body'         => 'An amount of ' . number_format((float)$request->credit, 3) . ' KWD has been added to your credit balance by Salasil administration.',
                    'type'         => 'payment',
                    'reference_id' => 'CREDIT-' . $user->id,
                    'action_route' => '/profile_screen',
                ]));
            }
        } catch (\Throwable $ignoreToggleNotif) {}

        return response()->json([
            'status' => 'success',
            'is_verified' => (bool) $user->is_verified,
            'credit' => (float) $user->credit,
            'message' => $user->is_verified ? 'User marked as Verified successfully!' : 'User verification removed.'
        ]);
    }

    /**
     * Delete client/user from database.
     */
    public function DeleteUser($id)
    {
        $user = User::findOrFail($id);

        if (!empty($user->photo) && file_exists(public_path('upload/user_images/' . $user->photo))) {
            @unlink(public_path('upload/user_images/' . $user->photo));
        }

        $user->delete();

        $notification = [
            'message' => 'Client Account Deleted Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * Change user uploaded document status (approved, rejected, pending) via AJAX.
     */
    public function ChangeDocumentStatusAjax(Request $request)
    {
        $doc = UserCompanyDocument::findOrFail($request->id);
        $status = strtolower(trim((string)$request->status));

        if (!in_array($status, ['approved', 'verified', 'rejected', 'pending'])) {
            return response()->json(['status' => false, 'message' => 'Invalid status provided.'], 400);
        }

        $doc->status = ($status === 'verified') ? 'approved' : $status;
        if ($doc->status === 'rejected') {
            $doc->rejection_reason = $request->rejection_reason ?: 'Document does not meet verification requirements';
        } else {
            $doc->rejection_reason = null;
        }
        $doc->save();

        return response()->json([
            'status' => true,
            'doc_status' => $doc->status,
            'message' => 'Document marked as ' . ucfirst($doc->status) . ' successfully!'
        ]);
    }

    /**
     * Delete user uploaded document.
     */
    public function DeleteUserDocument($id)
    {
        $doc = UserCompanyDocument::findOrFail($id);
        if ($doc->file_path && File::exists(public_path($doc->file_path))) {
            File::delete(public_path($doc->file_path));
        }
        $doc->delete();

        $notification = [
            'message' => 'Document deleted successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * AJAX endpoint to save required documents for a company client and immediately dispatch in-app notification.
     */
    public function RequestCompanyDocuments(Request $request, $id)
    {
        $user = User::with('companyProfile')->findOrFail($id);

        if ($request->filled('single_document_id')) {
            $singleId = (int)$request->single_document_id;
            $company = CompanyProfile::firstOrNew(['user_id' => $user->id]);
            $existing = is_array($company->required_documents) ? $company->required_documents : [];
            if (!in_array($singleId, $existing, true)) {
                $existing[] = $singleId;
                $company->required_documents = array_values(array_unique(array_map('intval', $existing)));
                $company->save();
            }
            $targetDoc = \App\Models\CompanyRequiredDocument::find($singleId);
            $docName = $targetDoc ? $targetDoc->name_en : 'required document';
            $selectedDocIds = [$singleId];
            $docNames = [$docName];
            $body = "Administration specifically requests you to upload your '{$docName}'. Please upload it in your Company Documents section to complete account verification.";
        } else {
            $selectedDocIds = $request->has('required_documents') 
                ? array_map('intval', (array)$request->required_documents) 
                : [];

            $company = CompanyProfile::firstOrNew(['user_id' => $user->id]);
            $company->required_documents = $selectedDocIds;
            $company->save();

            // Get document names in English and Arabic
            $docs = \App\Models\CompanyRequiredDocument::whereIn('id', $selectedDocIds)->get();
            $docNames = $docs->pluck('name_en')->toArray();
            $docNamesList = !empty($docNames) ? implode(', ', $docNames) : 'Official company documents';
            
            $customNote = $request->filled('note') ? trim($request->note) : null;
            $body = $customNote ?: ("Administration requests you to upload required documents: {$docNamesList}. Please upload them in your Company Documents section to complete account verification.");
        }

        try {
            $user->notify(new \App\Notifications\CustomerActivityNotification([
                'title'        => 'Company Documents Upload Requested',
                'body'         => $body,
                'type'         => 'system',
                'reference_id' => 'DOC-REQ-' . $user->id . '-' . time(),
                'action_route' => '/company_documents_screen',
                'metadata'     => [
                    'user_id'       => $user->id,
                    'documents'     => $docNames,
                    'requested_ids' => $selectedDocIds,
                ],
            ]));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error sending document request notification: ' . $e->getMessage());
        }

        return response()->json([
            'status'        => true,
            'message'       => 'Document requirements updated and notification sent to client successfully!',
            'count'         => count($selectedDocIds),
            'document_names'=> $docNames,
        ]);
    }

    /**
     * AJAX endpoint to create an additional custom document requirement specifically for this company user.
     */
    public function StoreUserCustomDocument(Request $request, $id)
    {
        $user = User::with('companyProfile')->findOrFail($id);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name_en'        => 'required|string|max:150',
            'name_ar'        => 'nullable|string|max:150',
            'description_en' => 'nullable|string|max:500',
            'description_ar' => 'nullable|string|max:500',
            'is_required'    => 'nullable',
            'send_notif'     => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $isRequired = $request->has('is_required') ? (bool)$request->is_required : true;
        $nameEn = trim($request->name_en);
        $nameAr = $request->filled('name_ar') ? trim($request->name_ar) : $nameEn;
        $descEn = $request->filled('description_en') ? trim($request->description_en) : 'Additional official document requested by administration';
        $descAr = $request->filled('description_ar') ? trim($request->description_ar) : 'مستند إضافي مطلوب من قبل الإدارة لاستكمال التوثيق';

        $doc = \App\Models\CompanyRequiredDocument::create([
            'user_id'        => $user->id,
            'name_en'        => $nameEn,
            'name_ar'        => $nameAr,
            'description_en' => $descEn,
            'description_ar' => $descAr,
            'is_required'    => $isRequired,
            'is_active'      => 1,
            'sort_order'     => 99,
        ]);

        // If required, ensure it is added to the user's required_documents array
        $company = \App\Models\CompanyProfile::firstOrNew(['user_id' => $user->id]);
        $reqList = is_array($company->required_documents) ? $company->required_documents : [];
        if ($isRequired && !in_array((int)$doc->id, $reqList, true)) {
            $reqList[] = (int)$doc->id;
            $company->required_documents = array_values(array_unique(array_map('intval', $reqList)));
            $company->save();
        }

        // Send in-app notification if requested
        $sendNotif = $request->has('send_notif') ? (bool)$request->send_notif : true;
        if ($sendNotif) {
            $displayName = $nameEn . ($nameAr && $nameAr !== $nameEn ? " ({$nameAr})" : '');
            try {
                $user->notify(new \App\Notifications\CustomerActivityNotification([
                    'title'        => 'Additional Document Requested',
                    'body'         => "Administration requests you to upload an additional document: '{$displayName}'. Please upload it in your Company Documents section to complete account verification.",
                    'type'         => 'system',
                    'reference_id' => 'DOC-REQ-ADD-' . $doc->id . '-' . time(),
                    'action_route' => '/company_documents_screen',
                    'metadata'     => [
                        'user_id'              => $user->id,
                        'required_document_id' => $doc->id,
                        'document_name_en'     => $nameEn,
                        'document_name_ar'     => $nameAr,
                    ],
                ]));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Error sending custom doc request notification: ' . $e->getMessage());
            }
        }

        return response()->json([
            'status'   => true,
            'message'  => "Additional document '{$nameEn}' added successfully!",
            'document' => [
                'id'             => $doc->id,
                'name_en'        => $doc->name_en,
                'name_ar'        => $doc->name_ar,
                'description_en' => $doc->description_en,
                'description_ar' => $doc->description_ar,
                'is_required'    => (bool)$doc->is_required,
                'user_id'        => $doc->user_id,
            ],
        ]);
    }

    /**
     * AJAX endpoint to delete a custom document requirement created specifically for this user.
     */
    public function DeleteUserCustomDocument(Request $request, $userId, $docId)
    {
        $user = User::with('companyProfile')->findOrFail($userId);
        $doc = \App\Models\CompanyRequiredDocument::where('id', $docId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Remove from required_documents array if present
        if ($user->companyProfile && is_array($user->companyProfile->required_documents)) {
            $reqList = array_values(array_diff($user->companyProfile->required_documents, [(int)$docId]));
            $user->companyProfile->required_documents = $reqList;
            $user->companyProfile->save();
        }

        // Clean up any uploaded user document for this requirement
        $uploadedDocs = \App\Models\UserCompanyDocument::where('required_document_id', $docId)
            ->where('user_id', $user->id)
            ->get();

        foreach ($uploadedDocs as $uDoc) {
            if (!empty($uDoc->file_path) && file_exists(public_path($uDoc->file_path))) {
                @unlink(public_path($uDoc->file_path));
            }
            $uDoc->delete();
        }

        $docName = $doc->name_en;
        $doc->delete();

        return response()->json([
            'status'  => true,
            'message' => "Custom document requirement '{$docName}' removed successfully.",
        ]);
    }
}
