<?php

namespace Modules\Audit\Listeners;

use App\Events\CompanyRegistered;
use Modules\Audit\Entities\AuditSetting;

class CompanyCreatedListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyRegistered $event): void
    {
        $company = $event->company;

        // Create default audit settings for the new company
        AuditSetting::firstOrCreate(
            ['company_id' => $company->id],
            [
                'partial_completion_weight' => 0.50,
                'score_threshold_alert' => 70,
                'send_result_to_manager' => true,
                'send_result_to_auditee' => true,
                'send_result_to_auditor' => true,
                'generate_pdf_report' => true,
            ]
        );

        // Add module settings
        AuditSetting::addModuleSetting($company);
    }
}

