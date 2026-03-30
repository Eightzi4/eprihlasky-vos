<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationStatus extends Model
{
    public const DRAFT = 'draft';
    public const SUBMITTED = 'submitted';
    public const COMPLETED_IN_TIME = 'completed_in_time';
    public const INCOMPLETE_AFTER_DEADLINE = 'incomplete_after_deadline';
    public const MOVED_TO_FURTHER_ROUND = 'moved_to_further_round';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'label',
    ];

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public static function idFor(string $code): int
    {
        static $cache = [];

        return $cache[$code] ??= (int) static::query()
            ->where('code', $code)
            ->value('id');
    }
}
