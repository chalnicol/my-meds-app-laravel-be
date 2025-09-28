<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Medication;
use App\Models\Stock;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
  
    public function getCounts () 
    {
        return response()->json([
            'message' => 'Counts fetched successfully.',
            'counts' => [
                'users' => User::count(),
                'roles' => Role::count(),
                'medications' => Medication::count(),
                'stocks' => Stock::count(),
            ]
        ]);
    }

}
