<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CompanyProfile;
use App\Models\DriverProfile;
use App\Models\Country;
use App\Models\City;
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
        $user = User::with(['companyProfile', 'driverProfile', 'country', 'city'])->findOrFail($id);
        $countries = Country::where('is_active', 1)->orderBy('name_en', 'asc')->get();
        $cities = $user->country_id ? City::where('country_id', $user->country_id)->where('is_active', 1)->orderBy('name_en', 'asc')->get() : collect();
        return view('admin.backend.user.edit_user', compact('user', 'countries', 'cities'));
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

        $user->save();

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

            $company->save();
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
}
