<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $fillable = [
        'author_user_id',
        'author_email',
        'title',
        'message',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }
}


