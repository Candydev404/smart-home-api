<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'appliance_name', 
        'wattage', 
        'turned_on_at', 
        'turned_off_at', 
        'total_kwh'
    ];

    protected $casts = [
        'turned_on_at' => 'datetime',
        'turned_off_at' => 'datetime',
    ];
}