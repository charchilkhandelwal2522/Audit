<?php

namespace Modules\Audit\Observers;

use Modules\Audit\Entities\AuditTemplate;

class AuditTemplateObserver
{
    /**
     * Handle the AuditTemplate "creating" event.
     */
    public function creating(AuditTemplate $template): void
    {
        if (company()) {
            $template->company_id = company()->id;
        }

        if (user()) {
            $template->added_by = user()->id;
        }
    }

    /**
     * Handle the AuditTemplate "updating" event.
     */
    public function updating(AuditTemplate $template): void
    {
        if (user()) {
            $template->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the AuditTemplate "created" event.
     */
    public function created(AuditTemplate $template): void
    {
        //
    }

    /**
     * Handle the AuditTemplate "updated" event.
     */
    public function updated(AuditTemplate $template): void
    {
        //
    }

    /**
     * Handle the AuditTemplate "deleted" event.
     */
    public function deleted(AuditTemplate $template): void
    {
        // Checkpoints are deleted via cascade in database
    }
}

