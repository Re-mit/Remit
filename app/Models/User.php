<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements CanResetPasswordContract
{
    use HasFactory, Notifiable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'student_id',
        'password',
        'google_id',
        'role',
        'warning',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'terms_agreed_at' => 'datetime',
        'suspended_at' => 'datetime',
    ];

    public function reservations()
    {
        return $this->belongsToMany(Reservation::class, 'reservation_users')
            ->withPivot('is_representative')
            ->withTimestamps();
    }

    public function usageLogs()
    {
        return $this->hasMany(UsageLog::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
