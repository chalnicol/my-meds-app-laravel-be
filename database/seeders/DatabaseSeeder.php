<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        $this->call(UserSeeder::class);
        //roles and permissions..
        $this->call(RolesAndPermissionsSeeder::class);
        //medication
        $this->call(MedicationSeeder::class);

        
    }
}
