<?php

namespace Modules\Audit\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Audit\Entities\Audit;
use Modules\Audit\Exports\AuditExport;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;

class AuditDashboardController extends AccountBaseController
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

    /**
     * Dashboard.
     */
    public function index()
    {
        $this->viewPermission = user()->permission('view_audit');
        abort_403($this->viewPermission == 'none');

        $this->pageTitle = __('audit::app.dashboard');

        // Get base query with permission filtering
        $baseQuery = Audit::query();
        $this->applyPermissionFilter($baseQuery);

        // Get completed audits for statistics
        $completedAudits = clone $baseQuery;
        $completedAudits->where('status', Audit::STATUS_COMPLETED);

        // Total audits count
        $this->totalAudits = (clone $baseQuery)->count();

        // Average score (with 1 decimal)
        $this->averageScore = round($completedAudits->avg('score') ?? 0, 1);

        // Audits in progress
        $inProgressQuery = clone $baseQuery;
        $this->auditsInProgress = $inProgressQuery->where('status', Audit::STATUS_IN_PROGRESS)->count();

        // Failed audits (<60%)
        $failedQuery = clone $baseQuery;
        $this->auditsFailed = $failedQuery->where('status', Audit::STATUS_COMPLETED)
            ->where('score', '<', 60)
            ->count();

        // Audits passed (>85%) - kept for other uses
        $passedQuery = clone $baseQuery;
        $this->auditsPassed = $passedQuery->where('status', Audit::STATUS_COMPLETED)
            ->where('score', '>=', 85)
            ->count();

        $this->statuses = array_keys(Audit::STATUSES);

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

        return view('audit::dashboard.dashboard', $this->data);
    }

    public function show () {

    }

    /**
     * Get recent audits for dashboard table (AJAX).
     */
    public function dashboardAudits(Request $request)
    {
        $viewPermission = user()->permission('view_audit');
        abort_403($viewPermission == 'none');

        $perPage = $request->get('per_page', 5);

        $query = Audit::with(['template', 'department', 'auditor', 'auditee']);

        // Apply permission filtering
        $this->applyPermissionFilter($query);

        // Apply filters
        if ($request->department_id && $request->department_id != 'all') {
            $query->where('department_id', $request->department_id);
        }

        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $search = $request->search;
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

        if ($request->start_date && $request->end_date) {
            $startDate = Carbon::createFromFormat(company()->date_format, $request->start_date)->startOfDay();
            $endDate = Carbon::createFromFormat(company()->date_format, $request->end_date)->endOfDay();
            $query->whereBetween('completed_at', [$startDate, $endDate]);
        }

        $audits = $query->orderBy('completed_at', 'desc')->paginate($perPage);

        return response()->json($audits);
    }

    /**
     * Export audit history to Excel
     */
    public function export(Request $request)
    {
        $viewPermission = user()->permission('view_audit');
        abort_403($viewPermission == 'none');

        $departmentId = $request->department_id;
        $status = $request->status;
        $search = $request->search;
        $startDate = null;
        $endDate = null;

        if ($request->start_date && $request->end_date) {
            $startDate = Carbon::createFromFormat(company()->date_format, $request->start_date)->startOfDay();
            $endDate = Carbon::createFromFormat(company()->date_format, $request->end_date)->endOfDay();
        }

        $filename = 'audit-history-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new AuditExport($departmentId, $status, $search, $startDate, $endDate, $viewPermission),
            $filename
        );
    }
}
