<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public const RETENTION_YEARS = 5;

    public const DESCRIPTION_VIEW_APPLICATION = 'view_application';
    public const DESCRIPTION_VIEW_AUDIT_LOG = 'view_audit_log';
    public const DESCRIPTION_UPDATE_EVIDENCE_NUMBER = 'update_evidence_number';
    public const DESCRIPTION_EXPORT_APPLICATION_CSV = 'export_application_csv';
    public const DESCRIPTION_EXPORT_APPLICATION_PDF = 'export_application_pdf';
    public const DESCRIPTION_DOWNLOAD_ATTACHMENT = 'download_attachment';
    public const DESCRIPTION_UPLOAD_ATTACHMENT = 'upload_attachment';
    public const DESCRIPTION_DELETE_ATTACHMENT = 'delete_attachment';
    public const DESCRIPTION_ACCEPT_EDUCATION = 'accept_education';
    public const DESCRIPTION_REVERT_EDUCATION = 'revert_education';
    public const DESCRIPTION_ACCEPT_PAYMENT = 'accept_payment';
    public const DESCRIPTION_REVERT_PAYMENT = 'revert_payment';
    public const DESCRIPTION_MOVE_TO_FURTHER_ROUND = 'move_to_further_round';

    const UPDATED_AT = null;

    protected $fillable = [
        'admin_id',
        'action_type_id',
        'application_id',
        'description',
        'not_before',
        'not_after',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'not_before' => 'datetime',
        'not_after' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \LogicException('Audit logs are immutable.');
        });

        static::deleting(function () {
            throw new \LogicException('Audit logs cannot be deleted from the application layer.');
        });
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function actionType()
    {
        return $this->belongsTo(AuditActionType::class);
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
