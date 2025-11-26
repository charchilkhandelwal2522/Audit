<?php

namespace Modules\Audit\Entities;

use App\Models\BaseModel;
use App\Models\ModuleSetting;
use App\Traits\HasCompany;

class AuditSetting extends BaseModel
{
    use HasCompany;

    protected $table = 'audit_settings';

    const MODULE_NAME = 'audit';

    protected $fillable = [
        'company_id',
        'partial_completion_weight',
        'score_threshold_alert',
        'send_result_to_manager',
        'send_result_to_auditee',
        'generate_pdf_report',
    ];

    protected $casts = [
        'partial_completion_weight' => 'decimal:2',
        'score_threshold_alert' => 'integer',
        'send_result_to_manager' => 'boolean',
        'send_result_to_auditee' => 'boolean',
        'generate_pdf_report' => 'boolean',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public static function addModuleSetting($company)
    {
        $roles = ['employee', 'admin'];
        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);

        // Create default settings for the company
        self::firstOrCreate(
            ['company_id' => $company->id],
            [
                'partial_completion_weight' => 0.50,
                'score_threshold_alert' => 70,
                'send_result_to_manager' => true,
                'send_result_to_auditee' => true,
                'generate_pdf_report' => true,
            ]
        );
    }
}

