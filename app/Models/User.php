<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Contracts\Auth\CanResetPassword; // Add this
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait; // Add this trait
use Illuminate\Support\Facades\Mail;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
  
    use HasFactory, Notifiable, HasApiTokens, HasRoles, CanResetPasswordTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fullname',
        'email',
        'password',
        'is_blocked',
        'firebase_uid',
        'timezone',
        'email_verification_token',
        'email_verified_at',
        'token_expires_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'token_expires_at' => 'datetime',
            'is_blocked' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * The accessors to append to the model's array form.
     * This makes 'roles' and 'permissions' available when the user model is converted to an array/JSON.
     *
     * @var array
     */
    protected $appends = [
        // 'roles', // Spatie adds roles directly, no need to append.
        'all_permissions', // Custom accessor to flatten all permissions
        // 'can_access', // (Optional) For a simple boolean check 
    ];

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        // Get user's email and name for the Mailable
        $userEmail = $this->getEmailForPasswordReset();

        $userName = $this->fullname ?? $this->email; // Use 'fullname' as defined in your fillable properties

        // Send your custom Mailable
        Mail::to($userEmail)->queue(new \App\Mail\PasswordResetMailable($token, $userEmail, $userName));

    }

    // public function sendEmailVerificationNotification()
    // {
    //     $this->notify(new VerifyEmailNotification);
    // }

    /**
     * Get all permissions of the user, including those from roles.
     * This consolidates permissions into a single array for easier frontend consumption.
     */
    public function getAllPermissionsAttribute()
    {
        // Spatie provides this helper to get all permissions directly
        return $this->getAllPermissions()->pluck('name');
    }

    // Optional: Add a helper method
    public function isBlocked(): bool
    {
        return (bool) $this->is_blocked;
    }

    public function isSocialUser(): bool
    {
        return (bool) $this->firebase_uid !== null;
    }

    public function medications()
    {
        return $this->hasMany(Medication::class);
    }

    
    // public function reminders()
    // {
    //     return $this->hasMany(Reminder::class);
    // }

}
