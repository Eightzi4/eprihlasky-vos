<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditActionType extends Model
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const EXPORT = 'export';
    public const DELETE = 'delete';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'label',
    ];

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
