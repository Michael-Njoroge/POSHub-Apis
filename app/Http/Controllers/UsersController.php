<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\CustomerRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\BillersResource;
use App\Http\Resources\CustomersResource;
use App\Http\Resources\SuppliersResource;
use App\Http\Resources\UsersResource;
use App\Models\Company;
use App\Models\Group;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'warehouse' => 'nullable|uuid|exists:pos_warehouses,id',
        ]);
        $first_warehouse = Warehouse::orderBy('created_at', 'asc')->first();

        if (!$request->warehouse) {
            $request->warehouse = $first_warehouse->id;
        }

        $ipAddress = $request->ip();

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
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

        $token = $user->createToken('authToken')->plainTextToken;

        $this->saveUserLogin($user, $ipAddress);

        $user->update([
            'last_ip_address' => $ipAddress,
            'last_login' => now(),
        ]);

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'last_ip_address' => $user->last_ip_address,
                'ip_address' => $ipAddress,
                'token' => $token,
            ]
        ]);
    }

    protected function saveUserLogin(User $user, $ipAddress)
    {
        UserLogin::create([
            'user_id' => $user->id,
            'ip_address' => $ipAddress,
            'login' => $user->email,
            'login_time' => now(),
        ]);
    }

    public function create_user(CreateUserRequest $request)
    {
        if (!auth()->user()->inGroup('Owner')) {
            return response()->json(['message' => 'Access denied for this user group'], 403);
        }

        $cashier_group = Group::where('name', 'cashier')->first();

        $group_id = $request->group_id ?? $cashier_group->id;

        $user = User::create([
            'username' => strtolower($request->username),
            'email' => strtolower($request->email),
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'warehouse_id' => $request->warehouse_id,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'group_id' => $group_id,
            'active' => $request->status ?? true,
        ]);

        $created_user = User::with('group', 'warehouse')->find($user->id);

        return $this->sendResponse(UsersResource::make($created_user)->response()->getData(true), 'User created successfully');
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

    public function update_user(User $user, UpdateUserRequest $request)
    {
        $user->update([
            'email' => strtolower($request->email),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'gender' => $request->gender,
        ]);

        return $this->sendResponse(UsersResource::make($user)->response()->getData(true), 'User updated successfully');
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
    
}
