<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Map to existing MySQL table and keys (lowercase per DB script)
    protected $table = 'users';
    protected $primaryKey = 'UserID';
    public $timestamps = false; // Table has no created_at/updated_at

    protected $fillable = [
        'FullName',
        'Username',
        'PasswordHash',
        'GroupID',
        'IsActive',
    ];

    protected $hidden = [
        'PasswordHash',
    ];

    // Accessor to keep using $user->name in views
    public function getNameAttribute(): ?string
    {
        return $this->attributes['FullName'] ?? null;
    }

    /**
     * Authentication: return the password column value.
     */
    public function getAuthPassword()
    {
        return $this->PasswordHash;
    }

    // JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'uid'  => $this->UserID,
            'name' => $this->FullName,
        ];
    }
}
