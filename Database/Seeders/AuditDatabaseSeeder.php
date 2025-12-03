<?php

namespace Modules\Audit\Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Modules\Audit\Entities\AuditSetting;

class AuditDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        config(['app.seeding' => true]);

        $companies = Company::all();

        foreach ($companies as $company) {
            // Create default settings for each company
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

            // Add module setting
            AuditSetting::addModuleSetting($company);
        }

        config(['app.seeding' => false]);
    }
}

