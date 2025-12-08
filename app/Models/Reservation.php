<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'start_at',
        'end_at',
        'key_code',
        'status',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'reservation_users')
                    ->withPivot('is_representative')
                    ->withTimestamps();
    }

    public function usageLogs()
    {
        return $this->hasMany(UsageLog::class);
    }

    /**
     * Helper methods
     */
    public function getRepresentative()
    {
        return $this->users()->wherePivot('is_representative', true)->first();
    }
}
