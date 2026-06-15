<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_main_admin',
        'two_factor_secret',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'is_main_admin'           => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
    ];

    public function isMainAdmin(): bool
    {
        return (bool) $this->is_main_admin;
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null
            && $this->two_factor_secret !== null;
    }

    public function recoveryCodes(): array
    {
        if (! $this->two_factor_recovery_codes) {
            return [];
        }

        try {
            $decrypted = decrypt($this->two_factor_recovery_codes);
            $codes = json_decode($decrypted, true);
            return is_array($codes) ? $codes : [];
        } catch (\Throwable) {
            return [];
        }
    }

    public function setRecoveryCodes(array $hashedCodes): void
    {
        $this->two_factor_recovery_codes = encrypt(json_encode($hashedCodes));
    }

    public function clearTwoFactor(): void
    {
        $this->two_factor_secret = null;
        $this->two_factor_confirmed_at = null;
        $this->two_factor_recovery_codes = null;
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
