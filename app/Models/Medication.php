<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Medication extends Model
{
    //
    protected $fillable = [
        'user_id', // Assuming each medication is associated with a user
        'brand_name',
        'generic_name',
        'dosage',
        'drug_form',
        'status',
        'frequency_type',
        'frequency',
        'remaining_stock',
    ];

    protected $casts = [
        'frequency' => 'array',
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

    public function timeSchedules()
    {
        // A Medication has many TimeSchedule records
        return $this->hasMany(TimeSchedule::class);
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
