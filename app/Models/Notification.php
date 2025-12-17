<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'read_at',
        'related_id',
        'related_type',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: 읽지 않은 알림
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope: 읽은 알림
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }
}
