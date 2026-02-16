<?php

namespace Modules\Audit\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\StorageSetting;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
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
        $audit->resumed_at = $audit->started_at;
        $audit->total_checkpoints = $template->checkpoints->count();
        $audit->added_by = user()->id;

        if ($request->hasFile('audit_photo')) {
            $audit->photo = Files::uploadLocalOrS3($request->file('audit_photo'), 'audit-photos', 400);
        }

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

        // Resume timer when continuing (Save & Exit had paused it)
        if ($this->audit->resumed_at === null) {
            if ($this->audit->total_elapsed_seconds == 0 && $this->audit->started_at) {
                $this->audit->resumed_at = $this->audit->started_at;
            } else {
                $this->audit->resumed_at = now();
            }
            $this->audit->save();
        }

        $this->pageTitle = __('audit::app.executeAudit') . ' - ' . $this->audit->template->title;

        return view('audit::audits.execute', $this->data);
    }

    /**
     * Pause the audit timer (Save & Exit).
     */
    public function pause($id)
    {
        $audit = Audit::findOrFail($id);

        abort_403($audit->auditor_id != user()->id);
        abort_403($audit->status != Audit::STATUS_IN_PROGRESS);

        $now = now();
        $elapsedThisSession = $audit->resumed_at ? $audit->resumed_at->diffInSeconds($now) : 0;
        $audit->total_elapsed_seconds = (int) $audit->total_elapsed_seconds + $elapsedThisSession;
        $audit->resumed_at = null;
        $audit->save();

        return Reply::success(__('audit::app.auditPaused'));
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

        // Check that every checkpoint has a completion status (completed, partially_completed, or not_completed)
        $validStatuses = ['completed', 'partially_completed', 'not_completed'];
        $missingStatusCount = $audit->responses()
            ->where(function ($q) use ($validStatuses) {
                $q->whereNull('status')->orWhereNotIn('status', $validStatuses);
            })
            ->count();

        if ($missingStatusCount > 0) {
            return Reply::error(__('audit::app.completeAllCheckpointsFirst'));
        }

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
     * Start async PDF export (for audits with many files - avoids timeout).
     */
    public function startExportPdf($id)
    {
        $viewPermission = user()->permission('view_audit');
        abort_403($viewPermission == 'none');

        $audit = Audit::findOrFail($id);

        if ($viewPermission == 'owned') {
            abort_403($audit->auditor_id != user()->id && $audit->auditee_id != user()->id);
        }

        $exportToken = 'audit_' . $audit->id . '_' . uniqid();

        \Illuminate\Support\Facades\Cache::put('audit_pdf_export_' . $exportToken, [
            'progress' => 0,
            'message' => __('audit::app.loadingData'),
            'status' => 'processing',
        ], \Modules\Audit\Jobs\ExportAuditPdfJob::CACHE_TTL);

        \Modules\Audit\Jobs\ExportAuditPdfJob::dispatch($audit->id, $exportToken, user()->id);

        return Reply::successWithData(__('audit::app.pdfExportStarted'), [
            'export_token' => $exportToken,
        ]);
    }

    /**
     * Get PDF export progress/status.
     */
    public function exportPdfStatus($token)
    {
        $data = \Illuminate\Support\Facades\Cache::get('audit_pdf_export_' . $token);

        if (!$data) {
            return Reply::error(__('audit::app.exportNotFound'));
        }

        return Reply::dataOnly($data);
    }

    /**
     * Download the generated PDF.
     * Add ?inline=1 to open in browser (for print preview) instead of download.
     */
    public function exportPdfDownload($token)
    {
        $data = \Illuminate\Support\Facades\Cache::get('audit_pdf_export_' . $token);

        if (!$data || ($data['status'] ?? '') !== 'ready') {
            return Reply::error(__('audit::app.pdfNotReady'));
        }

        $path = $data['download_path'] ?? null;
        $filename = $data['filename'] ?? 'audit-report.pdf';

        if (!$path || !\Illuminate\Support\Facades\Storage::disk('storage')->exists($path)) {
            return Reply::error(__('audit::app.pdfFileNotFound'));
        }

        $content = \Illuminate\Support\Facades\Storage::disk('storage')->get($path);
        $disposition = request('inline') ? 'inline' : 'attachment';

        return \Illuminate\Support\Facades\Response::make($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export audit report (direct download - for small audits or when queue is sync).
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

        $totalFiles = $audit->responses->sum(fn ($r) => $r->files->count());

        // For audits with many files, use the async flow (startExportPdf + progress modal)
        if ($totalFiles > 30) {
            return Reply::error(__('audit::app.useGeneratePdfButton'));
        }

        $pdf = app('dompdf.wrapper');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->loadView('audit::audits.pdf.report', ['audit' => $audit]);

        return $pdf->download('audit-report-' . $audit->id . '.pdf');
    }

    public function print($id)
    {
        set_time_limit(600);
        @ini_set('memory_limit', '512M');

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
            abort_403(
                $audit->auditor_id != user()->id &&
                $audit->auditee_id != user()->id
            );
        }

        $embedMap = $this->buildPrintEmbedMap($audit);

        return view('audit::audits.pdf.preview', compact('audit', 'embedMap'));
    }

    /**
     * Build embed map with base64 data URLs for print view (resized images for fast loading).
     */
    protected function buildPrintEmbedMap(Audit $audit): array
    {
        $embedMap = [];
        $maxDimension = 300;
        $quality = 75;
        $path = 'audit-files/';

        foreach ($audit->responses as $response) {
            foreach ($response->files as $file) {
                if (! $file->isImage()) {
                    continue;
                }

                $content = null;
                if (in_array(config('filesystems.default'), StorageSetting::S3_COMPATIBLE_STORAGE)) {
                    try {
                        $content = Storage::disk(config('filesystems.default'))->get($path . $file->hashname);
                    } catch (\Throwable) {
                        continue;
                    }
                } else {
                    $localPath = public_path(Files::UPLOAD_FOLDER . '/' . $path . $file->hashname);
                    if (! File::exists($localPath)) {
                        continue;
                    }
                    $content = File::get($localPath);
                }

                $ext = strtolower(pathinfo($file->hashname, PATHINFO_EXTENSION));
                if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    continue;
                }

                try {
                    $img = Image::make($content)
                        ->resize($maxDimension, $maxDimension, function ($c) {
                            $c->aspectRatio();
                            $c->upsize();
                        })
                        ->encode($ext === 'jpg' || $ext === 'jpeg' ? 'jpg' : $ext, $quality);
                    $embedMap[$file->id] = 'data:' . ($img->mime() ?? 'image/jpeg') . ';base64,' . base64_encode((string) $img);
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        return $embedMap;
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

