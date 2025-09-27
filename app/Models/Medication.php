<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Medication extends Model
{
    //
    protected $fillable = [
        'user_id', // Assuming each medication is associated with a user
        'brandName',
        'genericName',
        'dosage',
        'status',
        'frequencyType',
        'frequency',
        'dailySchedule',
        'remainingStock',
    ];

    protected $casts = [
        'frequency' => 'array',
        'dailySchedule' => 'array',
    ];

    protected $appends = [
        'total_quantity', 
        'total_value',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stocks() {
        return $this->hasMany(Stock::class);
    }

    public function getTotalQuantityAttribute()
    {
        return $this->stocks()->sum('quantity');
    }

    public function getTotalValueAttribute()
    {
        return $this->stocks()->sum(DB::raw('price * quantity'));
    }
}
