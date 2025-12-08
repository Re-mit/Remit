<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reservation_id',
        'entry_at',
        'exit_at',
        'duration',
    ];

    protected $casts = [
        'entry_at' => 'datetime',
        'exit_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Helper methods
     */
    public function calculateDuration()
    {
        if ($this->exit_at && $this->entry_at) {
            $this->duration = $this->exit_at->diffInSeconds($this->entry_at);
            $this->save();
        }
    }
}
