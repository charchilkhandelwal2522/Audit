<?php

namespace Modules\Audit\DataTables;

use App\DataTables\BaseDataTable;
use Modules\Audit\Entities\Audit;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class AuditDataTable extends BaseDataTable
{
    protected $editPermission;
    protected $deletePermission;
    protected $viewPermission;

    public function __construct()
    {
        parent::__construct();
        $this->editPermission = user()->permission('edit_audit');
        $this->deletePermission = user()->permission('delete_audit');
        $this->viewPermission = user()->permission('view_audit');
    }

    /**
     * Build DataTable class.
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($row) {
                $actions = '<div class="task_view">
                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link" id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $actions .= '<a href="' . route('audits.show', [$row->id]) . '" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                // Continue audit if in progress and user is auditor
                if ($row->status == Audit::STATUS_IN_PROGRESS && $row->auditor_id == user()->id) {
                    $actions .= '<a href="' . route('audits.execute', [$row->id]) . '" class="dropdown-item"><i class="fa fa-play mr-2"></i>' . __('audit::app.continueAudit') . '</a>';
                }

                // Export PDF for completed audits
                if ($row->status == Audit::STATUS_COMPLETED) {
                    $actions .= '<a href="' . route('audits.export-pdf', [$row->id]) . '" class="dropdown-item"><i class="fa fa-file-pdf mr-2"></i>' . __('audit::app.exportPdf') . '</a>';
                }

                if ($this->deletePermission == 'all' || ($this->deletePermission == 'added' && user()->id == $row->added_by)) {
                    $actions .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-audit-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>' . __('app.delete') . '</a>';
                }

                $actions .= '</div></div></div>';

                return $actions;
            })
            ->editColumn('template', function ($row) {
                return $row->template ? $row->template->title : '--';
            })
            ->editColumn('department', function ($row) {
                return $row->department ? $row->department->team_name : '--';
            })
            ->editColumn('auditor', function ($row) {
                if ($row->auditor) {
                    return view('components.employee', ['user' => $row->auditor]);
                }
                return '--';
            })
            ->editColumn('auditee', function ($row) {
                if ($row->auditee) {
                    return view('components.employee', ['user' => $row->auditee]);
                }
                return '--';
            })
            ->editColumn('status', function ($row) {
                $class = Audit::STATUSES[$row->status] ?? 'text-secondary';
                $label = ucwords(str_replace('_', ' ', $row->status));
                return '<i class="fa fa-circle mr-1 ' . $class . ' f-10"></i>' . $label;
            })
            ->editColumn('score', function ($row) {
                if ($row->status != Audit::STATUS_COMPLETED) {
                    return '--';
                }
                $colorClass = $row->score_color;
                return '<span class="' . $colorClass . ' font-weight-bold">' . $row->score . '%</span>';
            })
            ->editColumn('progress', function ($row) {
                if ($row->status != Audit::STATUS_IN_PROGRESS) {
                    return '--';
                }
                $progress = $row->progress_percentage;
                $colorClass = $progress >= 80 ? 'bg-success' : ($progress >= 50 ? 'bg-warning' : 'bg-danger');
                return '<div class="progress" style="height: 20px;">
                    <div class="progress-bar ' . $colorClass . '" role="progressbar" style="width: ' . $progress . '%" aria-valuenow="' . $progress . '" aria-valuemin="0" aria-valuemax="100">' . $progress . '%</div>
                </div>';
            })
            ->editColumn('duration', function ($row) {
                return $row->duration_formatted;
            })
            ->editColumn('started_at', function ($row) {
                return $row->started_at ? $row->started_at->translatedFormat($this->company->date_format . ' ' . $this->company->time_format) : '--';
            })
            ->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'status', 'score', 'progress', 'auditor', 'auditee']);
    }

    /**
     * Get query source of dataTable.
     */
    public function query(Audit $model)
    {
        $request = $this->request();

        $audits = $model->with(['template', 'department', 'auditor', 'auditee', 'responses'])
            ->select('audits.*');

        // Permission-based filtering
        if ($this->viewPermission == 'owned') {
            $audits->where(function ($query) {
                $query->where('auditor_id', user()->id)
                    ->orWhere('auditee_id', user()->id);
            });
        } elseif ($this->viewPermission == 'added') {
            $audits->where('added_by', user()->id);
        }

        // Filter for "my audits" page (where user is auditee)
        if ($request->my_audits) {
            $audits->where('auditee_id', user()->id);
        }

        if ($request->department_id && $request->department_id != 'all') {
            $audits->where('department_id', $request->department_id);
        }

        if ($request->auditor_id && $request->auditor_id != 'all') {
            $audits->where('auditor_id', $request->auditor_id);
        }

        if ($request->status && $request->status != 'all') {
            $audits->where('status', $request->status);
        }

        if ($request->startDate && $request->endDate) {
            $audits->whereBetween('started_at', [$request->startDate, $request->endDate]);
        }

        if ($request->searchText) {
            $audits->whereHas('template', function ($query) use ($request) {
                $query->where('title', 'like', '%' . $request->searchText . '%');
            });
        }

        return $audits->orderBy('created_at', 'desc');
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html()
    {
        return parent::setBuilder('audits-table')
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["audits-table"].buttons().container()
                     .appendTo("#table-actions")
                 }',
                'fnDrawCallback' => 'function(oSettings) {
                   $(".select-picker").selectpicker();
                 }',
            ])
            ->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
    }

    /**
     * Get columns.
     */
    protected function getColumns()
    {
        return [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'title' => '#', 'visible' => !showId()],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id'), 'visible' => showId()],
            __('audit::app.template') => ['data' => 'template', 'name' => 'template.title', 'title' => __('audit::app.template')],
            __('audit::app.department') => ['data' => 'department', 'name' => 'department.team_name', 'title' => __('audit::app.department')],
            __('audit::app.auditor') => ['data' => 'auditor', 'name' => 'auditor.name', 'title' => __('audit::app.auditor'), 'exportable' => false],
            __('audit::app.auditee') => ['data' => 'auditee', 'name' => 'auditee.name', 'title' => __('audit::app.auditee'), 'exportable' => false],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'title' => __('app.status')],
            __('audit::app.progress') => ['data' => 'progress', 'name' => 'progress', 'title' => __('audit::app.progress'), 'orderable' => false, 'searchable' => false],
            __('audit::app.score') => ['data' => 'score', 'name' => 'score', 'title' => __('audit::app.score')],
            __('audit::app.duration') => ['data' => 'duration', 'name' => 'duration_seconds', 'title' => __('audit::app.duration')],
            __('audit::app.startedAt') => ['data' => 'started_at', 'name' => 'started_at', 'title' => __('audit::app.startedAt')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(150)
                ->addClass('text-right pr-20'),
        ];
    }
}

