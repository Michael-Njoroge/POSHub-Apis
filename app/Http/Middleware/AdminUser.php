<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $group = $user->group;
        if($group->name !== 'Owner' && $group->name !== 'Admin') {
            return response()->json([
                'status' => false,
                'code' => 401,
                'message' => 'You are not authorized to perform this action'
            ]);
        }
        return $next($request);
    }
}
