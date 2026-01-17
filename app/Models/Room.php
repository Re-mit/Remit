<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Reservation;
use App\Models\Seat;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    /**
     * Relationships
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }
}
