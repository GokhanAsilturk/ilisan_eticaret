<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'birth_date',
        'gender',
        'role',
        'preferences',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'password' => 'hashed',
            'birth_date' => 'date',
            'gender' => \App\Enums\Gender::class,
            'role' => \App\Enums\UserRole::class,
            'preferences' => 'array',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Full name accessor
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Name accessor for Filament compatibility
     */
    public function getNameAttribute(): string
    {
        return $this->getFullNameAttribute();
    }

    /**
     * Gender mutator - Türkçe değerleri İngilizce'ye çevirir
     */
    public function setGenderAttribute($value): void
    {
        if (is_string($value)) {
            $genderEnum = \App\Enums\Gender::fromTurkish($value);
            $this->attributes['gender'] = $genderEnum?->value ?? $value;
        } else {
            $this->attributes['gender'] = $value;
        }
    }

    /**
     * Determine if the user can access the given Filament panel.
     */
    /**
     * Admin kontrolü - Filament için
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === \App\Enums\UserRole::ADMIN;
    }

    /**
     * Admin mi kontrol et
     */
    public function isAdmin(): bool
    {
        return $this->role === \App\Enums\UserRole::ADMIN;
    }

    /**
     * Get the user's display name for Filament
     */
    public function getFilamentName(): string
    {
        return $this->name ?? $this->email;
    }
}
