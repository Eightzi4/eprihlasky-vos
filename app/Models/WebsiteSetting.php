<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    public const DEFAULT_APPLICATION_FEE = 300;
    public const DEFAULT_NOTIFICATION_EMAIL = 'admin@oauh.cz';
    public const DEFAULT_BANK_ACCOUNT = '1234567890/0800';
    public const DEFAULT_VARIABLE_SYMBOL = '202600';
    public const DEFAULT_APPLICANT_NOTIFICATION_DELAY_MINUTES = 5;

    protected $fillable = [
        'application_fee',
        'notification_email',
        'bank_account',
        'variable_symbol',
        'applicant_notification_delay_minutes',
    ];

    protected $casts = [
        'application_fee' => 'integer',
        'applicant_notification_delay_minutes' => 'integer',
    ];

    public static function defaults(): array
    {
        return [
            'application_fee' => self::DEFAULT_APPLICATION_FEE,
            'notification_email' => self::DEFAULT_NOTIFICATION_EMAIL,
            'bank_account' => self::DEFAULT_BANK_ACCOUNT,
            'variable_symbol' => self::DEFAULT_VARIABLE_SYMBOL,
            'applicant_notification_delay_minutes' => self::DEFAULT_APPLICANT_NOTIFICATION_DELAY_MINUTES,
        ];
    }

    public static function current(): self
    {
        return static::query()->first() ?? static::query()->create(static::defaults());
    }
}
