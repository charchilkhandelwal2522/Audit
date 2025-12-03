<?php

namespace Modules\Audit\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Audit\Entities\Audit;
use Modules\Audit\Exports\AuditReportExport;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;

class AuditReportController extends AccountBaseController
{
    /**
     * Apply permission-based filtering to audit query.
     */
    protected function applyPermissionFilter($query)
    {
        $viewPermission = user()->permission('view_audit');

        if ($viewPermission == 'owned') {
            $query->where(function ($q) {
                $q->where('auditor_id', user()->id)
                    ->orWhere('auditee_id', user()->id);
            });
        } elseif ($viewPermission == 'added') {
            $query->where('added_by', user()->id);
        } elseif ($viewPermission == 'both') {
            $query->where(function ($q) {
                $q->where('auditor_id', user()->id)
                    ->orWhere('auditee_id', user()->id)
                    ->orWhere('added_by', user()->id);
            });
        }
        // 'all' permission doesn't need filtering

        return $query;
    }

    public function index()
    {
        $this->viewPermission = user()->permission('view_audit');
        abort_403($this->viewPermission == 'none');

        $this->pageTitle = __('audit::app.summaryReports');

        // Get base query with permission filtering
        $baseQuery = Audit::query();
        $this->applyPermissionFilter($baseQuery);

        // Get completed audits for statistics
        $completedAudits = clone $baseQuery;
        $completedAudits->where('status', Audit::STATUS_COMPLETED);

        // Total audits count
        $this->totalAudits = (clone $baseQuery)->count();

        // Average score
        $this->averageScore = round($completedAudits->avg('score') ?? 0, 0);

        // Audits passed (>85%)
        $passedQuery = clone $baseQuery;
        $this->auditsPassed = $passedQuery->where('status', Audit::STATUS_COMPLETED)
            ->where('score', '>=', 85)
            ->count();

        // Audits failed (<60%)
        $failedQuery = clone $baseQuery;
        $this->auditsFailed = $failedQuery->where('status', Audit::STATUS_COMPLETED)
            ->where('score', '<', 60)
            ->count();

        // Score distribution for chart
        $scoreDistributionQuery = clone $baseQuery;
        $scoreDistributionQuery->where('status', Audit::STATUS_COMPLETED);

        $this->scoreDistribution = [
            '0-20' => (clone $scoreDistributionQuery)->whereBetween('score', [0, 20])->count(),
            '21-40' => (clone $scoreDistributionQuery)->whereBetween('score', [21, 40])->count(),
            '41-60' => (clone $scoreDistributionQuery)->whereBetween('score', [41, 60])->count(),
            '61-80' => (clone $scoreDistributionQuery)->whereBetween('score', [61, 80])->count(),
            '81-100' => (clone $scoreDistributionQuery)->whereBetween('score', [81, 100])->count(),
        ];

        // Performance by department
        $departments = Team::select('id', 'team_name')->get();
        $departmentPerformance = [];

        foreach ($departments as $department) {
            $deptQuery = clone $baseQuery;
            $avgScore = $deptQuery->where('status', Audit::STATUS_COMPLETED)
                ->where('department_id', $department->id)
                ->avg('score');

            if ($avgScore !== null) {
                $departmentPerformance[$department->team_name] = round($avgScore, 0);
            }
        }

        $this->departmentPerformance = $departmentPerformance;

        // Monthly performance data (last 12 months)
        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('M');

            $monthAudits = clone $baseQuery;
            $monthAudits->where('status', Audit::STATUS_COMPLETED)
                ->whereYear('completed_at', $date->year)
                ->whereMonth('completed_at', $date->month);

            $monthlyData[$monthKey] = [
                'count' => $monthAudits->count(),
                'avgScore' => round($monthAudits->avg('score') ?? 0, 0),
            ];
        }

        $this->monthlyData = $monthlyData;

        // For filters
        $this->departments = Team::all();
        $this->auditors = User::allEmployees();
        $this->auditees = User::allEmployees();

        return view('audit::reports.index', $this->data);
    }

    /**
     * Get paginated audit reports for AJAX table
     */
    public function audits(Request $request)
    {
        $viewPermission = user()->permission('view_audit');
        abort_403($viewPermission == 'none');

        $perPage = $request->per_page ?? 4;

        $audits = Audit::with(['template', 'department', 'auditor', 'auditee'])
            ->where('status', Audit::STATUS_COMPLETED);

        // Apply permission filtering
        $this->applyPermissionFilter($audits);

        // Filter by department
        if ($request->department_id && $request->department_id != 'all') {
            $audits->where('department_id', $request->department_id);
        }

        // Filter by score range
        if ($request->score_range && $request->score_range != 'all') {
            switch ($request->score_range) {
                case 'high':
                    $audits->where('score', '>=', 85);
                    break;
                case 'medium':
                    $audits->whereBetween('score', [60, 84]);
                    break;
                case 'low':
                    $audits->where('score', '<', 60);
                    break;
            }
        }

        // Filter by search (auditee name)
        if ($request->search) {
            $search = $request->search;
            $audits->where(function ($query) use ($search) {
                $query->whereHas('auditee', function ($q) use ($search) {
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

        // Filter by date range
        if ($request->start_date && $request->end_date) {
            $startDate = Carbon::createFromFormat($this->company->date_format, $request->start_date)->startOfDay();
            $endDate = Carbon::createFromFormat($this->company->date_format, $request->end_date)->endOfDay();
            $audits->whereBetween('completed_at', [$startDate, $endDate]);
        }

        return $audits->orderBy('completed_at', 'desc')->paginate($perPage);
    }

    /**
     * Export audit reports
     */
    public function export(Request $request)
    {
        $viewPermission = user()->permission('view_audit');
        abort_403($viewPermission == 'none');

        $departmentId = $request->department_id;
        $scoreRange = $request->score_range;
        $search = $request->search;
        $startDate = null;
        $endDate = null;

        if ($request->start_date && $request->end_date) {
            $startDate = Carbon::createFromFormat($this->company->date_format, $request->start_date)->startOfDay();
            $endDate = Carbon::createFromFormat($this->company->date_format, $request->end_date)->endOfDay();
        }

        if ($request->format === 'excel') {
            $filename = 'audit-reports-' . now()->format('Y-m-d') . '.xlsx';

            return Excel::download(
                new AuditReportExport($departmentId, $scoreRange, $search, $startDate, $endDate, $viewPermission),
                $filename
            );
        }

        if ($request->format === 'pdf') {
            // Build query with filters
            $audits = Audit::with(['template', 'department', 'auditor', 'auditee'])
                ->where('status', Audit::STATUS_COMPLETED);

            // Apply permission filtering
            $this->applyPermissionFilter($audits);

            if ($departmentId && $departmentId != 'all') {
                $audits->where('department_id', $departmentId);
            }

            if ($scoreRange && $scoreRange != 'all') {
                switch ($scoreRange) {
                    case 'high':
                        $audits->where('score', '>=', 85);
                        break;
                    case 'medium':
                        $audits->whereBetween('score', [60, 84]);
                        break;
                    case 'low':
                        $audits->where('score', '<', 60);
                        break;
                }
            }

            if ($search) {
                $searchTerm = $search;
                $audits->where(function ($query) use ($searchTerm) {
                    $query->whereHas('auditee', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhereHas('auditor', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhereHas('department', function ($q) use ($searchTerm) {
                        $q->where('team_name', 'like', '%' . $searchTerm . '%');
                    });
                });
            }

            if ($startDate && $endDate) {
                $audits->whereBetween('completed_at', [$startDate, $endDate]);
            }

            $audits = $audits->orderBy('completed_at', 'desc')->get();

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('audit::reports.pdf', [
                'audits' => $audits,
                'company' => company()
            ]);

            $filename = 'audit-reports-' . now()->format('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        }

        return back();
    }
}
