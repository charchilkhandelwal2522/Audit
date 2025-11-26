<?php

namespace Modules\Audit\Observers;

use Modules\Audit\Entities\Audit;

class AuditObserver
{
    /**
     * Handle the Audit "creating" event.
     */
    public function creating(Audit $audit): void
    {
        if (company()) {
            $audit->company_id = company()->id;
        }

        if (user()) {
            $audit->added_by = user()->id;
        }
    }

    /**
     * Handle the Audit "created" event.
     */
    public function created(Audit $audit): void
    {
        //
    }

    /**
     * Handle the Audit "updated" event.
     */
    public function updated(Audit $audit): void
    {
        //
    }

    /**
     * Handle the Audit "deleted" event.
     */
    public function deleted(Audit $audit): void
    {
        // Delete related files from storage
        foreach ($audit->files as $file) {
            if (file_exists(public_path('user-uploads/audit-files/' . $file->hashname))) {
                unlink(public_path('user-uploads/audit-files/' . $file->hashname));
            }
        }
    }
}

