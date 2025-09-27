<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class MedicationIntake extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'user_id',
        'medication_id',
        'scheduled_time', 
        'taken_at',
    ];

    protected $casts = [
        'taken_at' => 'datetime',
    ];
   

}
