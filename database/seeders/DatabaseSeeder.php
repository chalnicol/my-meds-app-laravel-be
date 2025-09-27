<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // User::factory(10)->create();
        // Users
        $this->call(UserSeeder::class);
        //roles and permissions..
        $this->call(RolesAndPermissionsSeeder::class);
        
    }
}
