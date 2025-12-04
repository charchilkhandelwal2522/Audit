<?php

namespace Modules\Audit\Observers;

use Modules\Audit\Entities\Audit;
use Modules\Audit\Entities\AuditSetting;
use Modules\Audit\Notifications\AuditCompleted;

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
        if ($audit->status == Audit::STATUS_COMPLETED) {
            // Send notifications (queued for async processing)
            $setting = AuditSetting::where('company_id', company()->id)->first();

            if ($setting) {
                // Notify auditee
                if ($setting->send_result_to_auditee && $audit->auditee) {
                    $audit->auditee->notify(new AuditCompleted($audit));
                }

                // Notify auditor
                if ($setting->send_result_to_auditor && $audit->auditor) {
                    $audit->auditor->notify(new AuditCompleted($audit));
                }

                // Notify department manager
                if ($setting->send_result_to_manager) {
                    if ($audit->auditee && $audit->auditee?->employeeDetail && $audit->auditee?->employeeDetail?->reportingTo) {
                        $audit->auditee?->employeeDetail?->reportingTo->notify(new AuditCompleted($audit));
                    }
                }
            }
        }
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

