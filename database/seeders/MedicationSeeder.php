<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Medication; // Import your User model


class MedicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $medications = [
           
            [
                "brandName" => "Biogesic",
                "genericName" => "Paracetamol",
                "dosage" => "500mg",
                "status" => "Active",
                "drugForm" => "Tablet",
                "frequencyType" => "Everyday",
                "frequency" => null,
                "time_schedules" => [
                    [
                        "schedule_time" => "08:00:00",
                        "quantity" => 1
                    ],
                    [
                        "schedule_time" => "12:00:00",
                        "quantity" => 1,
                    ]
                ],
                "initial_stock" => [
                    'quantity' => 10,
                    'price' => 30.25,
                    'source' => 'Mercury Drug - Lagro'
                ]
            ],
            [
                "brandName" => "Advil",
                "genericName" => "Ibuprofen",
                "dosage" => "200mg",
                "status" => "Active",
                "drugForm" => "Tablet",
                "frequencyType" => "SpecificDays",
                "frequency" => [1, 3, 5],
                "time_schedules" => [
                    [
                        "schedule_time" => "08:00:00",
                        "quantity" => 2
                    ],
                    [
                        "schedule_time" => "12:00:00",
                        "quantity" => 2,
                    ]
                ],
                "initial_stock" => [
                    'quantity' => 10,
                    'price' => 30.25,
                    'source' => 'Watsons - SM Fairview'
                ]

                    
            ],
            [
                "brandName" => "Neozep",
                "genericName" => "Phenylephrine HCl + Chlorphenamine Maleate + Paracetamol",
                "dosage" => "10mg/2mg/500mg",
                "status" => "Active",
                "drugForm" => "Tablet",
                "frequencyType" => "Everyday",
                "frequency" => null,
                "time_schedules" => [
                    [
                        "schedule_time" => "08:00:00",
                        "quantity" => 1
                    ],
                    [
                        "schedule_time" => "12:00:00",
                        "quantity" => 1,
                    ],
                    [
                        "schedule_time" => "16:00:00",
                        "quantity" => 1,
                    ],
                    [
                        "schedule_time" => "20:00:00",
                        "quantity" => 1,
                    ]
                ],
                "initial_stock" => [
                    'quantity' => 10,
                    'price' => 30.25,
                    'source' => 'Mercury Drug - Lagro'
                ]
            ],
            [
                "brandName" => "Trimox",
                "genericName" => "Amoxicillin",
                "dosage" => "100mg",
                "status" => "Active",
                "drugForm" => "Capsule",
                "frequencyType" => "Everyday",
                "frequency" => null,
                "time_schedules" => [
                    [
                        "schedule_time" => "08:00:00",
                        "quantity" => 1
                    ],
                    [
                        "schedule_time" => "12:00:00",
                        "quantity" => 1,
                    ],
                    [
                        "schedule_time" => "16:00:00",
                        "quantity" => 1,
                    ],
                    [
                        "schedule_time" => "20:00:00",
                        "quantity" => 1,
                    ]
                ],
                "initial_stock" => [
                    'quantity' => 10,
                    'price' => 30.25,
                    'source' => 'Mercury Drug - Lagro'
                ]
            ]
            
        ];

        foreach ($medications as $medication) {
            $med = Medication::firstOrCreate(
                [
                    'brand_name' => $medication['brandName'],
                ],
                [
                    'user_id' => 1,
                    'generic_name' => $medication['genericName'],
                    'dosage' => $medication['dosage'],
                    'frequency_type' => $medication['frequencyType'],
                    'frequency' => $medication['frequency'],
                    'status' => $medication['status'],
                    'drug_form' => $medication['drugForm'],
                    'remaining_stock' => $medication['initial_stock']['quantity'],
                ]
            );
            if ($med) {

                $med->stocks()->create(
                    [
                        'quantity' => $medication['initial_stock']['quantity'],
                        'price' => $medication['initial_stock']['price'],
                        'source' => $medication['initial_stock']['source'],
                        'user_id' => 1,
                    ]
                );

                foreach ($medication['time_schedules'] as $time_schedule) {
                    $med->timeSchedules()->create(
                        [
                            'schedule_time' => $time_schedule['schedule_time'],
                            'quantity' => $time_schedule['quantity'],
                        ]
                    );
                }
            }
        }

    }
}
