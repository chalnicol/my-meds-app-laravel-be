<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // Import your User model
use Illuminate\Support\Facades\Hash; // For hashing the password


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $users = [
           
            [
                "fullname" => "Admin User",
                "email" => "admin@example.com",
                "password" => Hash::make('asdfasdf'), // Use a secure password
                "email_verified_at" => now(), // Set email as verified
            ],
            [
                "fullname" => "Charlou Nicolas",
                "email" => "charlou@example.com",
                "password" => Hash::make('asdfasdf'), // Use a secure password
                "email_verified_at" => now(), // Set email as verified
            ],
            [
                "fullname" => "Nong Nicolas",
                "email" => "nong@example.com",
                "password" => Hash::make('asdfasdf'), // Use a secure password
                "email_verified_at" => now(), // Set email as verified
            ],
            [
                "fullname" => "Roger Nicolas",
                "email" => "nongers@example.com",
                "password" => Hash::make('asdfasdf'), // Use a secure password
                "email_verified_at" => now(), // Set email as verified
            ],
            [
                "fullname" => "Charlie Nicolas",
                "email" => "charles@example.com",
                "password" => Hash::make('asdfasdf'), // Use a secure password
                "email_verified_at" => now(), // Set email as verified
            ],
            
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                [
                    'email' => $user['email'],
                ],
                [
                    'fullname' => $user['fullname'],
                    'password' => $user['password'],
                    'email_verified_at' => $user['email_verified_at'], // Set email verification timestamp
                ]
            );
        }

    }
}
