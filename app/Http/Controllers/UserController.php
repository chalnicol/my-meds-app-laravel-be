<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
  
         // Get the search term from the request
        $searchTerm = $request->query('search');
        
        // Define how many items per page
        $perPage = 10; // You can make this configurable or pass it from the frontend

        $query = User::query();

        // Apply search filter if a search term is provided
        if ($searchTerm) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('fullname', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('email', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Paginate the results
        $users = $query
            ->withCount('medications')
            ->with('roles.permissions')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return UserResource::collection($users);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        
        $user->load(['roles.permissions'])
            ->loadCount(['medications']);

        return response()->json([
            'message' => 'User fetched successfully.',
            'user' => new UserResource($user)
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    public function toggleBlockUser (User $user)
    {   

        $current_block_status = $user->isBlocked();

        $user->update(['is_blocked' => !$current_block_status]);

        $message = $current_block_status ? 'User unblocked successfully!' : 'User blocked successfully!';

        return response()->json([
            'message' => $message
        ], 200); // 200 OK
    }


    public function updateUserRoles(Request $request, User $user)
    {

        $role = Role::where('name', $request->role)->first();

        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $message = "";

        // Add the 'web' guard here for both methods
        if ($user->hasRole($request->role)) {
            if ($role->name === 'admin' && $user->id === Auth::id()) {
                return response()->json([
                    'message' => 'You cannot remove your own admin role.'
                ], 403);
            }
            
            $user->removeRole($request->role); // <-- Add 'web' guard here
            $message = ucfirst($request->role) . ' role removed successfully!';
        } else {

            $user->assignRole($request->role); // <-- Add 'web' guard here
            $message = ucfirst($request->role) . ' role assigned successfully!';
        }

        $user->load('roles');

        return response()->json([
            'message' => $message,
            'user' => new UserResource($user)
        ]);
    }

   


}
