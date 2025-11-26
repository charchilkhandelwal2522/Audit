<?php

namespace Modules\Audit\Console;

use App\Models\Company;
use Illuminate\Console\Command;
use Modules\Audit\Entities\AuditSetting;

class ActivateModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:activate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate Audit module for all companies';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            AuditSetting::addModuleSetting($company);
        }

        $this->info('Audit module activated successfully for all companies.');

        return 0;
    }
}

