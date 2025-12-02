<?php

namespace Modules\Audit\Exports;

use Modules\Audit\Entities\Audit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $departmentId;
    protected $scoreRange;
    protected $search;
    protected $startDate;
    protected $endDate;

    public function __construct($departmentId = null, $scoreRange = null, $search = null, $startDate = null, $endDate = null)
    {
        $this->departmentId = $departmentId;
        $this->scoreRange = $scoreRange;
        $this->search = $search;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = Audit::with(['template', 'department', 'auditor', 'auditee'])
            ->where('status', Audit::STATUS_COMPLETED);

        if ($this->departmentId && $this->departmentId != 'all') {
            $query->where('department_id', $this->departmentId);
        }

        if ($this->scoreRange && $this->scoreRange != 'all') {
            switch ($this->scoreRange) {
                case 'high':
                    $query->where('score', '>=', 85);
                    break;
                case 'medium':
                    $query->whereBetween('score', [60, 84]);
                    break;
                case 'low':
                    $query->where('score', '<', 60);
                    break;
            }
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('auditee', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('auditor', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('department', function ($q) use ($search) {
                    $q->where('team_name', 'like', '%' . $search . '%');
                });
            });
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('completed_at', [$this->startDate, $this->endDate]);
        }

        return $query->orderBy('completed_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            __('audit::app.auditee'),
            __('audit::app.department'),
            __('audit::app.auditor'),
            __('audit::app.score'),
            __('app.date'),
        ];
    }

    public function map($audit): array
    {
        return [
            $audit->auditee?->name ?? '-',
            $audit->department?->team_name ?? '-',
            $audit->auditor?->name ?? '-',
            round($audit->score) . '%',
            $audit->completed_at ? $audit->completed_at->format(company()->date_format) : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

