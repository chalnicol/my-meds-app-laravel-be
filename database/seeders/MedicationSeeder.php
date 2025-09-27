<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // Import your User model
use Illuminate\Support\Facades\Hash; // For hashing the password


class MedicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $users = [
           
            [
                "brandName" => "Biogesic",
                "genericName" => "Paracetamol",
                "dosage" => "500mg",
                "dosageUnit" => "mg",
                "status" => "Active",
                "frequencyType" => "Everyday",
                "frequency" => json_encode([]),
                "dailySchedule" => json_encode(["08:00AM", "12:00PM", "08:00PM"]),
            ],
            [
                "brandName" => "Amoxicillin",
                "genericName" => "Amoxicillin",
                "dosage" => "500mg",
                "dosageUnit" => "mg",
                "status" => "Active",
                "frequencyType" => "SpecificDays",
                "frequency" => json_encode(["Monday", "Wednesday", "Friday"]),
                "dailySchedule" => json_encode(["02:00PM", "06:00PM", "10:00PM"]),
            ],
            [
                "brandName" => "Ibuprofen",
                "genericName" => "Ibuprofen",
                "dosage" => "200mg",
                "dosageUnit" => "mg",
                "status" => "Active",
                "frequencyType" => "SpecificDays",
                "frequency" => json_encode(["Tuesday", "Thursday", "Saturday"]),
                "dailySchedule" => json_encode(["02:00PM", "06:00PM", "10:00PM"]),
            ]
            
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
