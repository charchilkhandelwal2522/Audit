<?php

namespace Modules\Audit\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Audit\DataTables\AuditTemplateDataTable;
use Modules\Audit\Entities\AuditSetting;
use Modules\Audit\Entities\AuditTemplate;
use Modules\Audit\Entities\AuditTemplateCheckpoint;
use Modules\Audit\Http\Requests\StoreAuditTemplateRequest;
use Modules\Audit\Http\Requests\UpdateAuditTemplateRequest;

class AuditTemplateController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            abort_403(!in_array(AuditSetting::MODULE_NAME, $this->user->modules));
            $this->pageTitle = __('audit::app.auditTemplates');

            return $next($request);
        });
    }

    /**
     * Display a listing of audit templates.
     */
    public function index(AuditTemplateDataTable $dataTable)
    {
        $this->viewPermission = user()->permission('view_audit_template');
        abort_403($this->viewPermission == 'none');

        $this->departments = Team::all();
        $this->totalTemplates = AuditTemplate::count();
        $this->activeTemplates = AuditTemplate::where('status', 'active')->count();
        // Count of unique departments that have at least one template
        $this->departmentCount = AuditTemplate::whereNotNull('department_id')
            ->pluck('department_id')
            ->unique()
            ->count();
        // Get all templates with their checkpoints count and calculate the average
        $templates = AuditTemplate::withCount('checkpoints')->get();
        $totalTemplates = $templates->count();
        $totalCheckpoints = $templates->sum('checkpoints_count');
        $this->averageCheckpoints = $totalTemplates > 0 ? round($totalCheckpoints / $totalTemplates, 2) : 0;

        if($this->viewPermission == 'added') {
            $this->templates = AuditTemplate::where('added_by', user()->id)->withCount('checkpoints')->get();

            $this->totalTemplates = $this->templates->count();
            $this->activeTemplates = $this->templates->where('status', 'active')->count();

            $this->departmentCount = $this->templates->pluck('department_id')->unique()->filter()->count();

            $totalCheckpoints = $this->templates->sum('checkpoints_count');
            $this->averageCheckpoints = $this->totalTemplates > 0 ?
            round($totalCheckpoints / $this->totalTemplates, 2) : 0;
        }
        return $dataTable->render('audit::audit-templates.index', $this->data);
    }

    /**
     * Show the form for creating a new template.
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_audit_template');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->departments = Team::all();
        $this->view = 'audit::audit-templates.ajax.create';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('audit::audit-templates.create', $this->data);
    }

    /**
     * Store a newly created template.
     */
    public function store(StoreAuditTemplateRequest $request)
    {
        $departmentIds = $request->department_ids ?? [];
        $createdTemplates = [];

        // Create a template for each selected department
        foreach ($departmentIds as $departmentId) {
            $template = new AuditTemplate();
            $template->company_id = company()->id;
            $template->title = $request->title;
            $template->description = $request->description;
            $template->department_id = $departmentId;
            $template->status = $request->status ?? 'active';
            $template->added_by = user()->id;
            $template->save();

            // Save checkpoints with their order from drag-and-drop
            if ($request->has('checkpoints')) {
                foreach ($request->checkpoints as $checkpoint) {
                    // Use the order from the form (set by drag-and-drop), or default to 0
                    $order = isset($checkpoint['order']) ? (int)$checkpoint['order'] : 0;

                    AuditTemplateCheckpoint::create([
                        'audit_template_id' => $template->id,
                        'title' => $checkpoint['title'],
                        'description' => $checkpoint['description'] ?? null,
                        'order' => $order,
                        'requires_file_upload' => isset($checkpoint['requires_file_upload']),
                        'requires_photo' => isset($checkpoint['requires_photo']),
                        'requires_notes' => isset($checkpoint['requires_notes']),
                        'is_mandatory' => isset($checkpoint['is_mandatory']),
                    ]);
                }
            }

            $createdTemplates[] = $template->id;
        }

        $count = count($createdTemplates);
        if ($count > 1) {
            $message = str_replace(':count', $count, __('audit::app.templatesCreated'));
        } else {
            $message = __('audit::app.templateCreated');
        }

        return Reply::successWithData($message, ['redirectUrl' => route('audit-templates.index')]);
    }

    /**
     * Display the specified template.
     */
    public function show($id)
    {
        $viewPermission = user()->permission('view_audit_template');
        abort_403($viewPermission == 'none');

        // Load checkpoints sorted by order for correct display
        $this->template = AuditTemplate::with(['checkpoints' => function($query) {
            $query->orderBy('order', 'asc');
        }, 'department', 'addedByUser'])->findOrFail($id);
        $this->view = 'audit::audit-templates.ajax.show';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('audit::audit-templates.show', $this->data);
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_audit_template');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        // Load checkpoints sorted by order for correct display after drag-and-drop
        $this->template = AuditTemplate::with(['checkpoints' => function($query) {
            $query->orderBy('order', 'asc');
        }])->findOrFail($id);
        $this->departments = Team::all();
        $this->view = 'audit::audit-templates.ajax.edit';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('audit::audit-templates.edit', $this->data);
    }

    /**
     * Update the specified template.
     */
    public function update(UpdateAuditTemplateRequest $request, $id)
    {
        $template = AuditTemplate::findOrFail($id);
        $template->title = $request->title;
        $template->department_id = $request->department_id;
        $template->description = $request->description;
        $template->status = $request->status ?? 'active';
        $template->last_updated_by = user()->id;
        $template->save();

        // Delete old checkpoints and add new ones
        $template->checkpoints()->delete();

        // Save checkpoints with their order from drag-and-drop
        if ($request->has('checkpoints')) {
            foreach ($request->checkpoints as $checkpoint) {
                // Use the order from the form (set by drag-and-drop), or default to 0
                $order = isset($checkpoint['order']) ? (int)$checkpoint['order'] : 0;

                AuditTemplateCheckpoint::create([
                    'audit_template_id' => $template->id,
                    'title' => $checkpoint['title'],
                    'description' => $checkpoint['description'] ?? null,
                    'order' => $order,
                    'requires_file_upload' => isset($checkpoint['requires_file_upload']),
                    'requires_photo' => isset($checkpoint['requires_photo']),
                    'requires_notes' => isset($checkpoint['requires_notes']),
                    'is_mandatory' => isset($checkpoint['is_mandatory']),
                ]);
            }
        }

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('audit-templates.index')]);
    }

    /**
     * Remove the specified template.
     */
    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_audit_template');
        abort_403(!in_array($deletePermission, ['all', 'added']));

        AuditTemplate::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Get templates for a specific department.
     */
    public function getByDepartment($departmentId)
    {
        $templates = AuditTemplate::where('department_id', $departmentId)
            ->where('status', 'active')
            ->withCount('checkpoints')
            ->get(['id', 'title', 'description']);

        return Reply::dataOnly(['templates' => $templates]);
    }
}

