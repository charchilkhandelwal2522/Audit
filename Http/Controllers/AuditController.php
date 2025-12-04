<?php

namespace Modules\Audit\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Audit\DataTables\AuditDataTable;
use Modules\Audit\Entities\Audit;
use Modules\Audit\Entities\AuditCheckpointResponse;
use Modules\Audit\Entities\AuditFile;
use Modules\Audit\Entities\AuditSetting;
use Modules\Audit\Entities\AuditTemplate;
use Modules\Audit\Http\Requests\StoreAuditRequest;
use Modules\Audit\Http\Requests\UpdateCheckpointResponseRequest;
use Modules\Audit\Notifications\AuditCompleted;

class AuditController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            abort_403(!in_array(AuditSetting::MODULE_NAME, $this->user->modules));
            $this->pageTitle = __('audit::app.audits');

            return $next($request);
        });
    }

    /**
     * Display a listing of audits.
     */
    public function index(AuditDataTable $dataTable)
    {
        $this->viewPermission = user()->permission('view_audit');
        abort_403($this->viewPermission == 'none');

        $this->departments = Team::all();
        $this->auditors = User::allEmployees();
        $this->statuses = array_keys(Audit::STATUSES);

        return $dataTable->render('audit::audits.index', $this->data);
    }

    /**
     * Show the form for creating a new audit.
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_audit');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->departments = Team::all();
        $this->templates = AuditTemplate::active()->get();
        $this->employees = User::allEmployees();
        $this->view = 'audit::audits.ajax.create';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('audit::audits.create', $this->data);
    }

    /**
     * Store a newly created audit and redirect to execution page.
     */
    public function store(StoreAuditRequest $request)
    {
        $this->addPermission = user()->permission('add_audit');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $template = AuditTemplate::with('checkpoints')->findOrFail($request->audit_template_id);

        $audit = new Audit();
        $audit->company_id = company()->id;
        $audit->audit_template_id = $request->audit_template_id;
        $audit->department_id = $request->department_id;
        $audit->auditor_id = user()->id;
        $audit->auditee_id = $request->auditee_id;
        $audit->location = $request->location;
        $audit->status = Audit::STATUS_IN_PROGRESS;
        $audit->started_at = now();
        $audit->total_checkpoints = $template->checkpoints->count();
        $audit->added_by = user()->id;
        $audit->save();

        // Create checkpoint responses
        foreach ($template->checkpoints as $checkpoint) {
            AuditCheckpointResponse::create([
                'audit_id' => $audit->id,
                'checkpoint_id' => $checkpoint->id,
                'status' => null, // No status pre-selected, user must choose
                'order' => $checkpoint->order,
            ]);
        }

        return Reply::successWithData(__('audit::app.auditStarted'), [
            'redirectUrl' => route('audits.execute', $audit->id)
        ]);
    }

    /**
     * Display the specified audit.
     */
    public function show($id)
    {
        $viewPermission = user()->permission('view_audit');
        abort_403($viewPermission == 'none');

        $this->audit = Audit::with([
            'template',
            'department',
            'auditor',
            'auditee',
            'responses.checkpoint',
            'responses.files',
            'files'
        ])->findOrFail($id);

        // Check ownership permission
        if ($viewPermission == 'owned') {
            abort_403($this->audit->auditor_id != user()->id && $this->audit->auditee_id != user()->id);
        }

        // Get audit history for the same template (excluding current audit)
        $this->auditHistory = Audit::with(['template', 'department', 'auditor', 'auditee'])
            ->where('audit_template_id', $this->audit->audit_template_id)
            ->where('id', '!=', $id)
            ->orderBy('completed_at', 'desc')
            ->limit(5)
            ->get();

        $this->view = 'audit::audits.ajax.show';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('audit::audits.create', $this->data);
    }

    /**
     * Execute/Continue the audit (the main execution page).
     */
    public function execute($id)
    {
        $this->editPermission = user()->permission('edit_audit');

        $this->audit = Audit::with([
            'template',
            'department',
            'auditor',
            'auditee',
            'responses.checkpoint',
            'responses.files'
        ])->findOrFail($id);

        // Only the auditor can execute the audit
        abort_403($this->audit->auditor_id != user()->id);
        abort_403($this->audit->status != Audit::STATUS_IN_PROGRESS);

        $this->pageTitle = __('audit::app.executeAudit') . ' - ' . $this->audit->template->title;

        return view('audit::audits.execute', $this->data);
    }

    /**
     * Update a checkpoint response.
     */
    public function updateCheckpointResponse(UpdateCheckpointResponseRequest $request, $auditId, $responseId)
    {
        $audit = Audit::findOrFail($auditId);
        // abort_403($audit->auditor_id != user()->id);
        // abort_403($audit->status != Audit::STATUS_IN_PROGRESS);

        $response = AuditCheckpointResponse::where('audit_id', $auditId)
            ->where('id', $responseId)
            ->firstOrFail();

        // Only update status if provided
        if ($request->filled('status')) {
            $response->status = $request->status;
            $response->responded_at = now();
        }
        $response->notes = $request->notes;
        $response->save();

        // Handle file uploads
        $uploadedFiles = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $hashname = Files::uploadLocalOrS3($file, 'audit-files');
                $extension = strtolower($file->getClientOriginalExtension());

                $fileType = 'document';
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                    $fileType = 'image';
                } elseif ($extension === 'pdf') {
                    $fileType = 'pdf';
                }

                $auditFile = AuditFile::create([
                    'audit_id' => $audit->id,
                    'checkpoint_response_id' => $response->id,
                    'filename' => $file->getClientOriginalName(),
                    'hashname' => $hashname,
                    'file_type' => $fileType,
                    'size' => $file->getSize(),
                    'added_by' => user()->id,
                ]);

                $uploadedFiles[] = [
                    'id' => $auditFile->id,
                    'filename' => $auditFile->filename,
                    'url' => $auditFile->file_url,
                    'is_image' => $auditFile->isImage(),
                    'icon' => $auditFile->icon,
                ];
            }
        }

        // Update audit progress
        $audit->updateScore();

        $completedCount = $audit->responses()->whereIn('status', ['completed', 'partially_completed'])->count();
        $totalResponses = $audit->responses()->count();

        return Reply::successWithData(__('audit::app.checkpointUpdated'), [
            'progress' => $audit->progress_percentage,
            'responded' => $completedCount,
            'total' => $totalResponses,
            'files' => $uploadedFiles,
        ]);
    }

    /**
     * Complete the audit.
     */
    public function complete($id)
    {
        $audit = Audit::with(['responses.checkpoint', 'responses.files', 'auditor', 'auditee', 'auditee.employeeDetail.reportingTo', 'template', 'department'])
            ->findOrFail($id);

        // abort_403($audit->auditor_id != user()->id);
        // abort_403($audit->status != Audit::STATUS_IN_PROGRESS);

        // Check if all mandatory checkpoints have been responded
        $mandatoryNotResponded = $audit->responses()
            ->whereNull('responded_at')
            ->whereHas('checkpoint', function ($query) {
                $query->where('is_mandatory', true);
            })
            ->count();

        if ($mandatoryNotResponded > 0) {
            return Reply::error(__('audit::app.completeMandatoryFirst'));
        }

        // Check if all requirements are met for each responded checkpoint
        $missingRequirements = [];

        foreach ($audit->responses as $response) {
            $checkpoint = $response->checkpoint;

            // Check photo requirement
            if ($checkpoint->requires_photo) {
                $hasPhoto = $response->files()->where('file_type', 'image')->exists();
                if (!$hasPhoto) {
                    $missingRequirements[] = __('audit::app.photoRequiredFor', ['checkpoint' => $checkpoint->title]);
                }
            }

            // Check file upload requirement
            if ($checkpoint->requires_file_upload && !$checkpoint->requires_photo) {
                $hasFile = $response->files()->exists();
                if (!$hasFile) {
                    $missingRequirements[] = __('audit::app.fileRequiredFor', ['checkpoint' => $checkpoint->title]);
                }
            }

            // Check notes requirement
            if ($checkpoint->requires_notes && empty($response->notes)) {
                $missingRequirements[] = __('audit::app.notesRequiredFor', ['checkpoint' => $checkpoint->title]);
            }
        }

        if (!empty($missingRequirements)) {
            return Reply::error(implode(", \n", $missingRequirements));
        }

        // Complete the audit
        $audit->complete();

        // Send notifications
        $setting = AuditSetting::where('company_id', company()->id)->first();
        info($setting);

        if ($setting) {
            // Notify auditee
            if ($setting->send_result_to_auditee && $audit->auditee) {
                info('Mail send to Auditee');
                $audit->auditee->notify(new AuditCompleted($audit));
            }

            // Notify auditor
            if ($setting->send_result_to_auditor && $audit->auditor) {
                info('Mail send to Auditor');
                $audit->auditor->notify(new AuditCompleted($audit));
            }

            // Notify department manager (could be extended to find actual managers)
            if ($setting->send_result_to_manager) {
                info('Mail send to Reporting Manager');
                if ($audit->auditee && $audit->auditee?->employeeDetail && $audit->auditee?->employeeDetail?->reportingTo) {
                    info('checking...');
                    $audit->auditee?->employeeDetail?->reportingTo->notify(new AuditCompleted($audit));
                }
            }
        }

        return Reply::successWithData(__('audit::app.auditCompleted'), [
            'redirectUrl' => route('audits.show', $audit->id)
        ]);
    }

    /**
     * Cancel the audit.
     */
    public function cancel($id)
    {
        $audit = Audit::findOrFail($id);

        abort_403($audit->auditor_id != user()->id && user()->permission('edit_audit') != 'all');
        abort_403($audit->status != Audit::STATUS_IN_PROGRESS);

        $audit->status = Audit::STATUS_CANCELLED;
        $audit->completed_at = now();
        $audit->save();

        return Reply::successWithData(__('audit::app.auditCancelled'), [
            'redirectUrl' => route('audits.index')
        ]);
    }

    /**
     * Remove the specified audit.
     */
    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_audit');
        abort_403(!in_array($deletePermission, ['all', 'added']));

        $audit = Audit::findOrFail($id);

        // Delete associated files from storage
        foreach ($audit->files as $file) {
            Files::deleteFile($file->hashname, 'audit-files');
        }

        $audit->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Delete a file from an audit.
     */
    public function deleteFile($auditId, $fileId)
    {
        $audit = Audit::findOrFail($auditId);
        abort_403($audit->auditor_id != user()->id && user()->permission('edit_audit') != 'all');

        $file = AuditFile::where('audit_id', $auditId)->where('id', $fileId)->firstOrFail();

        Files::deleteFile($file->hashname, 'audit-files');
        $file->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Get employees by department.
     */
    public function getEmployeesByDepartment($departmentId)
    {
        $employees = User::join('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->where('employee_details.department_id', $departmentId)
            ->select('users.id', 'users.name', 'users.image')
            ->get();

        return Reply::dataOnly(['employees' => $employees]);
    }

    /**
     * Export audit report.
     */
    public function exportPdf($id)
    {
        $viewPermission = user()->permission('view_audit');
        abort_403($viewPermission == 'none');

        $audit = Audit::with([
            'template',
            'department',
            'auditor',
            'auditee',
            'responses.checkpoint',
            'responses.files',
        ])->findOrFail($id);

        if ($viewPermission == 'owned') {
            abort_403($audit->auditor_id != user()->id && $audit->auditee_id != user()->id);
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('audit::audits.pdf.report', ['audit' => $audit]);

        return $pdf->download('audit-report-' . $audit->id . '.pdf');
    }

    /**
     * My audits - audits where current user is the auditee.
     */
    public function myAudits(AuditDataTable $dataTable)
    {
        $this->viewPermission = user()->permission('view_audit');
        $this->myAuditsOnly = true;
        $this->pageTitle = __('audit::app.myAudits');

        return $dataTable->render('audit::audits.my-audits', $this->data);
    }

}

