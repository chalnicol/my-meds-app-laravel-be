<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSchedule extends Model
{
    //
    protected $fillable = [
        'medication_id',
        'schedule_time',
        'quantity',
    ];

    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    
}
