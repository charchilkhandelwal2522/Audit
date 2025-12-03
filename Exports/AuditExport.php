<?php

namespace Modules\Audit\Exports;

use Modules\Audit\Entities\Audit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $departmentId;
    protected $status;
    protected $search;
    protected $startDate;
    protected $endDate;
    protected $viewPermission;

    public function __construct($departmentId = null, $status = null, $search = null, $startDate = null, $endDate = null, $viewPermission = 'all')
    {
        $this->departmentId = $departmentId;
        $this->status = $status;
        $this->search = $search;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->viewPermission = $viewPermission;
    }

    public function collection()
    {
        $query = Audit::with(['template', 'department', 'auditor', 'auditee']);

        // Apply permission filtering
        if ($this->viewPermission == 'owned') {
            $query->where(function ($q) {
                $q->where('auditor_id', user()->id)
                    ->orWhere('auditee_id', user()->id);
            });
        } elseif ($this->viewPermission == 'added') {
            $query->where('added_by', user()->id);
        } elseif ($this->viewPermission == 'both') {
            $query->where(function ($q) {
                $q->where('auditor_id', user()->id)
                    ->orWhere('auditee_id', user()->id)
                    ->orWhere('added_by', user()->id);
            });
        }
        // 'all' permission doesn't need filtering

        if ($this->departmentId && $this->departmentId != 'all') {
            $query->where('department_id', $this->departmentId);
        }

        if ($this->status && $this->status != 'all') {
            $query->where('status', $this->status);
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                    ->orWhereHas('auditor', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('auditee', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('department', function ($q) use ($search) {
                        $q->where('team_name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            __('audit::app.auditId'),
            __('audit::app.department'),
            __('audit::app.auditor'),
            __('audit::app.auditee'),
            __('app.date'),
            __('audit::app.score'),
            __('app.status'),
        ];
    }

    public function map($audit): array
    {
        $status = ucwords(str_replace('_', ' ', $audit->status));
        if ($audit->status === Audit::STATUS_COMPLETED && $audit->score < 60) {
            $status = __('audit::app.failed');
        }

        return [
            '#AUD-' . str_pad($audit->id, 4, '0', STR_PAD_LEFT),
            $audit->department?->team_name ?? '-',
            $audit->auditor?->name ?? '-',
            $audit->auditee?->name ?? '-',
            $audit->completed_at ? $audit->completed_at->format(company()->date_format) : ($audit->created_at ? $audit->created_at->format(company()->date_format) : '-'),
            $audit->status === Audit::STATUS_COMPLETED ? round($audit->score) . '%' : '-',
            $status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

