<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'user_id',
        'is_representative',
    ];

    protected $casts = [
        'is_representative' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
