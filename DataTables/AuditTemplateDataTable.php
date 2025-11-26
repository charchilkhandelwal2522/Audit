<?php

namespace Modules\Audit\DataTables;

use App\DataTables\BaseDataTable;
use Modules\Audit\Entities\AuditTemplate;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class AuditTemplateDataTable extends BaseDataTable
{
    protected $editPermission;
    protected $deletePermission;
    protected $viewPermission;

    public function __construct()
    {
        parent::__construct();
        $this->editPermission = user()->permission('edit_audit_template');
        $this->deletePermission = user()->permission('delete_audit_template');
        $this->viewPermission = user()->permission('view_audit_template');
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

                $actions .= '<a href="' . route('audit-templates.show', [$row->id]) . '" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->editPermission == 'all' || ($this->editPermission == 'added' && user()->id == $row->added_by)) {
                    $actions .= '<a class="dropdown-item openRightModal" href="' . route('audit-templates.edit', [$row->id]) . '">
                                    <i class="fa fa-edit mr-2"></i>' . __('app.edit') . '</a>';
                }

                if ($this->deletePermission == 'all' || ($this->deletePermission == 'added' && user()->id == $row->added_by)) {
                    $actions .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-template-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>' . __('app.delete') . '</a>';
                }

                $actions .= '</div></div></div>';

                return $actions;
            })
            ->editColumn('title', function ($row) {
                return '<a href="' . route('audit-templates.show', [$row->id]) . '" class="openRightModal text-darkest-grey">' . $row->title . '</a>';
            })
            ->editColumn('department', function ($row) {
                return $row->department ? $row->department->team_name : '--';
            })
            ->editColumn('status', function ($row) {
                $class = $row->status == 'active' ? 'text-light-green' : 'text-red';
                return '<i class="fa fa-circle mr-1 ' . $class . ' f-10"></i>' . ucfirst($row->status);
            })
            ->addColumn('checkpoints', function ($row) {
                return $row->checkpoints->count();
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->translatedFormat($this->company->date_format);
            })
            ->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'title', 'status']);
    }

    /**
     * Get query source of dataTable.
     */
    public function query(AuditTemplate $model)
    {
        $request = $this->request();

        $templates = $model->with(['department', 'checkpoints'])
            ->withCount('checkpoints')
            ->select('audit_templates.*');

        if ($this->viewPermission == 'added') {
            $templates->where('added_by', user()->id);
        }

        if ($request->department_id && $request->department_id != 'all') {
            $templates->where('department_id', $request->department_id);
        }

        if ($request->status && $request->status != 'all') {
            $templates->where('status', $request->status);
        }

        if ($request->searchText) {
            $templates->where('title', 'like', '%' . $request->searchText . '%');
        }

        return $templates;
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html()
    {
        return parent::setBuilder('audit-templates-table')
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["audit-templates-table"].buttons().container()
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
            __('audit::app.templateTitle') => ['data' => 'title', 'name' => 'title', 'title' => __('audit::app.templateTitle')],
            __('audit::app.department') => ['data' => 'department', 'name' => 'department.team_name', 'title' => __('audit::app.department')],
            __('audit::app.checkpoints') => ['data' => 'checkpoints', 'name' => 'checkpoints_count', 'title' => __('audit::app.checkpoints')],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'title' => __('app.status')],
            __('app.createdOn') => ['data' => 'created_at', 'name' => 'created_at', 'title' => __('app.createdOn')],
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

