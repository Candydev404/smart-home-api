<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    // This tells Laravel it is safe to insert data into these columns
    protected $fillable = ['name', 'room', 'is_on'];
}