<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Resources\BillersResource;
use App\Http\Resources\CustomersResource;
use App\Http\Resources\GroupsResource;
use App\Http\Resources\SuppliersResource;
use App\Http\Resources\UsersLoginResource;
use App\Http\Resources\UsersResource;
use App\Models\Company;
use App\Models\Group;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\Warehouse;
use App\Notifications\ForgotPassword;
use App\Notifications\OtpNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class UsersController extends Controller
{

    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:pos_users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $data['ip_address'] = $request->ip();
        $data['group_id'] = Group::where('name', 'cashier')->first()->id;
        $data['username'] = strtolower($request->firstname . $request->lastname);

        $user = User::create($data);

        $otp = rand(1000, 9999);
        $user->otp = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        $user = User::where('id', $user->id)->first();

        $user->notify(new OtpNotification($otp));

        return response()->json([
            'success' => true,
            'data' => [
                'id' => encrypt($user->id),
                'email' => $this->mask_info($user->email),
            ],
            'message' => 'Check your email to verify your account.'
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'warehouse' => 'nullable|uuid|exists:pos_warehouses,id',
            'remember_me' => 'boolean'
        ]);
        $first_warehouse = Warehouse::orderBy('created_at', 'asc')->first();

        $data['warehouse'] = $data['warehouse'] ?? $first_warehouse->id;

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Invalid credentials'
            ], 401);
        }

        // if (!$user->inGroup('owner')) {
        //     return response()->json(['message' => 'Site is offline. Please try later.'], 403);
        // }

        if ($user->inGroup('Customer') || $user->inGroup('Supplier')) {
            return response()->json(['message' => 'Access denied for this user group'], 403);
        }

        if (!$user->inGroup('Admin') && !$user->inGroup('Owner')) {
            $user->update(['warehouse_id' => $request->warehouse]);
        }

        $otp = rand(1000, 9999);
        $user->otp = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        $user->notify(new OtpNotification($otp));

        return response()->json([
            'success' => true,
            'data' => [
                'email' => $this->mask_info($user->email),
                'phone' => $this->mask_info($user->phone),
                'id' => encrypt($user->id),
            ],
            'message' => 'OTP sent. Please verify to complete login.'
        ]);
    }
    public function verify_otp(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
            'otp' => 'required|integer',
            'remember_me' => 'boolean',
            'is_login' => 'boolean',
        ]);

        if ($request->is_login) {
            $user = User::where('id', decrypt($request->id))->first();
        } else {
            $user = User::where('email', $request->id)->first();
        }

        if ($user->otp === $request->otp && Carbon::now()->lessThanOrEqualTo($user->otp_expires_at)) {
            $user->otp_verified_at = Carbon::now();
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            $ipAddress = request()->ip();
            
            // Set the expiration time
            $expiration = $request->remember_me ? 10080 : 120; 
            config(['sanctum.expiration' => $expiration]);

            $token = $user->createToken('authToken', ['*'])->plainTextToken;

            $this->user_login($user, $ipAddress);

            $user->update([
                'last_ip_address' => $ipAddress,
                'last_login' => now(),
                'warehouse_id' => $request->warehouse ?? Warehouse::orderBy('created_at', 'asc')->first()->id,
            ]);

            $user->load('user_logins');

            return $this->sendResponse(UsersResource::make($user, $token)->response()->getData(true), 'Success');
        }

        return response()->json(['message' => 'Invalid or expired OTP'], 400);
    }

    public function resend_otp(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
        ]);

        $user = User::where('id', decrypt($request->id))->first();

        $otp = rand(1000, 9999);

        $user->otp = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        $user->notify(new OtpNotification($otp));

        return response()->json([
            'success' => true,
            'message' => 'Success, OTP sent.'
        ]);
    }


    public function mask_info($input)
    {
        if (strpos($input, '.') !== false) {
            list($name, $domain) = explode('.', $input);
            $maskedName = substr($name, 0, 3) . str_repeat('*', 9);
            return $maskedName . '.' . $domain;
        } else {
            return substr($input, 0, 4) . str_repeat('*', 9) . substr($input, -3);
        }
    }


    protected function user_login(User $user, $ipAddress)
    {
        UserLogin::create([
            'user_id' => $user->id,
            'ip_address' => $ipAddress,
            'login' => $user->email,
            'login_time' => now(),
        ]);
    }

    public function get_user_login(User $user)
    {
        $login = UserLogin::where('user_id', $user->id)->latest()->first();

        return $this->sendResponse(UsersLoginResource::make($login)->response()->getData(true), 'User login retrieved successfully');
    }

    public function log_out(User $user)
    {
        $user->tokens()->delete();

        return $this->sendResponse([], 'User logged out successfully');
    }

    public function create_user(CreateUserRequest $request)
    {
        if (!auth()->user()->inGroup('Owner')) {
            return response()->json(['message' => 'Access denied for this user group'], 403);
        }

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:pos_users',
            'password' => 'required|string|min:8|confirmed',
            'group_id' => 'required|exists:pos_groups,id',
            'warehouse_id' => 'required|exists:pos_warehouses,id',
            'username' => 'required|string|max:255',
            'active' => 'boolean',
        ]);

        $cashier_group = Group::where('name', 'cashier')->first();

        $data['group_id'] = $request->group_id ? $request->group_id : $cashier_group->id;
        $user = User::create($data);

        $created_user = User::with('group', 'warehouse')->find($user->id);

        return $this->sendResponse(UsersResource::make($created_user)->response()->getData(true), 'User created successfully');
    }

    public function change_password(Request $request)
    {
        $data = $request->validate([
            'password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = auth()->user();
        if (!Hash::check($data['password'], $user->password)) {
            return $this->sendError('Current password is incorrect');
        }

        if ($data['password'] === $data['new_password']) {
            return $this->sendError('Add a different password');
        }

        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);

        return $this->sendResponse([], 'Password changed successfully');
    }

    public function forgot_password(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:pos_users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        $token = Str::random(60);
        $user->remember_code = $token;
        $user->forgotten_password_code = $token;
        $user->forgotten_password_time = now();
        $user->save();
        $domain = "http://localhost:5173/reset-password";
        $url = $domain . "?token=" . $token;

        $user->notify(new ForgotPassword($url));

        return $this->sendResponse([], 'Password reset link sent to your email');
    }

    public function reset_password(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::where('remember_code', $request->token)->first();

        if (!$user) {
            return $this->sendError('Invalid token');
        }

        $user->update([
            'password' => Hash::make($request->password),
            'remember_code' => null,
            'forgotten_password_code' => null,
            'forgotten_password_time' => null,
        ]);

        return $this->sendResponse([], 'Password changed successfully');
    }

    public function get_users(Request $request)
    {
        if (!auth()->user()->inGroup('Owner')) {
            return response()->json(['message' => 'Access denied for this user group'], 403);
        }

        $users = User::with('group', 'warehouse')->get();

        return $this->sendResponse(UsersResource::collection($users)->response()->getData(true), 'Users retrieved successfully');
    }

    public function get_user(User $user)
    {
        if (!auth()->user()->inGroup('Owner')) {
            return response()->json(['message' => 'Access denied for this user group'], 403);
        }

        $user = User::with('group', 'warehouse')->find($user->id);

        return $this->sendResponse(UsersResource::make($user)->response()->getData(true), 'User retrieved successfully');
    }

    public function update_user(User $user, Request $request)
    {

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'group_id' => 'required|exists:pos_groups,id',
            'warehouse_id' => 'required|exists:pos_warehouses,id',
        ]);

        $user->update($data);

        return $this->sendResponse(UsersResource::make($user)->response()->getData(true), 'User updated successfully');
    }

    public function update_profile_image(Request $request, User $user)
    {
        $data = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
        ]);

        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');
            $uuid = Str::uuid();
            $image_name = $uuid . '-' . time() . '.' . $image->getClientOriginalExtension();
            $image_path = $image->storeAs('user_images', $image_name, 'public');
            $data['avatar'] = asset('storage/' . $image_path);

            if ($user->avatar) {
                $path = str_replace(asset('storage') . '/', '', $user->avatar);
                Storage::disk('public')->delete($path);
            }

            $user->avatar = $data['avatar'];
            $user->save();
        }

        return $this->sendResponse(UsersResource::make($user)->response()->getData(true), 'User profile updated successfully');
    }

    public function create_company(Request $request)
    {
        $commonValidation = [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'zip' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
        ];

        $groupName = $request->validate([
            'group_name' => 'required|string|max:255|in:customer,biller,supplier'
        ]);

        $group = Group::where('name', $groupName['group_name'])->first();

        if (!$group) {
            $group = Group::create(['name' => $groupName['group_name']]);
        }

        if ($group->name === 'customer') {
            $request->validate($commonValidation);
            $input = $request->only(['name', 'address', 'city', 'state', 'email', 'phone', 'zip', 'country']);
        } elseif ($group->name === 'biller') {
            // Add specific validation for billers
            $billerValidation = array_merge($commonValidation, [
                'logo' => 'required|string|max:255',
                'company' => 'required|string|max:255',
                'invoice_footer' => 'nullable|string|max:255',
            ]);
            $request->validate($billerValidation);
            $input = $request->only(['name', 'address', 'city', 'state', 'email', 'phone', 'zip', 'country', 'logo', 'company', 'invoice_footer']);
        } elseif ($group->name === 'supplier') {
            $request->validate($commonValidation);
            $input = $request->only(['name', 'address', 'city', 'state', 'email', 'phone', 'zip', 'country']);
        } else {
            return response()->json(['message' => 'Invalid group'], 400);
        }

        $input['group_name'] = $group->name;
        $input['group_id'] = $group->id;

        $company = Company::create($input);
        $created_company = Company::with('group')->find($company->id);

        return response()->json([
            'message' => $group->name == 'customer'
                ? 'Customer created successfully'
                : ($group->name == 'biller'
                    ? 'Biller created successfully'
                    : 'Supplier created successfully'),
            'data' => $created_company
        ]);
    }

    public function get_customers(Request $request)
    {
        if (!auth()->user()->inGroup('Owner')) {
            return response()->json(['message' => 'Access denied for this user group'], 403);
        }

        $customers = Company::where('group_name', 'customer')->get();

        return $this->sendResponse(CustomersResource::collection($customers)->response()->getData(true), 'Customers retrieved successfully');
    }

    public function get_suppliers(Request $request)
    {
        if (!auth()->user()->inGroup('Owner')) {
            return response()->json(['message' => 'Access denied for this user group'], 403);
        }

        $suppliers = Company::where('group_name', 'supplier')->get();

        return $this->sendResponse(SuppliersResource::collection($suppliers)->response()->getData(true), 'Suppliers retrieved successfully');
    }

    public function get_billers(Request $request)
    {
        if (!auth()->user()->inGroup('Owner')) {
            return response()->json(['message' => 'Access denied for this user group'], 403);
        }

        $billers = Company::where('group_name', 'biller')->get();

        return $this->sendResponse(BillersResource::collection($billers)->response()->getData(true), 'Billers retrieved successfully');
    }

    public function change_user_status(Request $request, User $user)
    {

        $request->validate([
            'status' => 'required|boolean|in:1,0'
        ]);
        if (!auth()->user()->inGroup('Owner')) {
            return response()->json(['message' => 'Access denied for this user group'], 403);
        }

        $user->update(['active' => $request->status]);

        return $this->sendResponse([], $user->active ? 'User activated successfully' : 'User deactivated successfully');
    }

    public function delete_user(User $user)
    {
        if (!auth()->user()->inGroup('Owner')) {
            return response()->json(['message' => 'Access denied for this user group'], 403);
        }

        if ($user->avatar) {
            Storage::delete($user->avatar);
        }

        $user->delete();

        return $this->sendResponse([], 'User deleted successfully');
    }

    public function get_groups(Request $request)
    {
        $groups = Group::all();

        return $this->sendResponse(GroupsResource::collection($groups)->response()->getData(true), 'Groups retrieved successfully');
    }
}
