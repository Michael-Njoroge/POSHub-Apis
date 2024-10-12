<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
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

        if(!$request->warehouse) {
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
            'user' => $user,
            'token' => $token,
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
            'phone' => $request->phone,
            'gender' => $request->gender,
            'group_id' => $group_id,
            'active' => $request->status ?? true, 
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ]);
    }

}
