<?php

namespace Modules\Audit\Providers;

use App\Events\CompanyRegistered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Audit\Entities\Audit;
use Modules\Audit\Entities\AuditTemplate;
use Modules\Audit\Listeners\CompanyCreatedListener;
use Modules\Audit\Observers\AuditObserver;
use Modules\Audit\Observers\AuditTemplateObserver;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CompanyRegistered::class => [
            CompanyCreatedListener::class,
        ],
    ];

    public function boot()
    {
        Audit::observe(AuditObserver::class);
        AuditTemplate::observe(AuditTemplateObserver::class);
    }
}

