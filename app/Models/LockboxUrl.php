<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LockboxUrl extends Model
{
    protected $fillable = [
        'start_date',
        'end_date',
        'url',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}


