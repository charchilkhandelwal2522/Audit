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

class AuditDashboard extends AccountBaseController
{

    /**
     * Dashboard.
     */
    public function index()
    {
        $this->pageTitle = __('audit::app.dashboard');

        // Get completed audits for statistics
        $completedAudits = Audit::where('status', Audit::STATUS_COMPLETED);

        // Total audits count
        $this->totalAudits = Audit::count();

        // Average score (with 1 decimal)
        $this->averageScore = round($completedAudits->avg('score') ?? 0, 1);

        // Audits in progress
        $this->auditsInProgress = Audit::where('status', Audit::STATUS_IN_PROGRESS)->count();

        // Failed audits (<60%)
        $this->auditsFailed = Audit::where('status', Audit::STATUS_COMPLETED)
            ->where('score', '<', 60)
            ->count();

        // Audits passed (>85%) - kept for other uses
        $this->auditsPassed = Audit::where('status', Audit::STATUS_COMPLETED)
            ->where('score', '>=', 85)
            ->count();

        $this->statuses = array_keys(Audit::STATUSES);

        // Score distribution for chart
        $this->scoreDistribution = [
            '0-20' => Audit::where('status', Audit::STATUS_COMPLETED)->whereBetween('score', [0, 20])->count(),
            '21-40' => Audit::where('status', Audit::STATUS_COMPLETED)->whereBetween('score', [21, 40])->count(),
            '41-60' => Audit::where('status', Audit::STATUS_COMPLETED)->whereBetween('score', [41, 60])->count(),
            '61-80' => Audit::where('status', Audit::STATUS_COMPLETED)->whereBetween('score', [61, 80])->count(),
            '81-100' => Audit::where('status', Audit::STATUS_COMPLETED)->whereBetween('score', [81, 100])->count(),
        ];

        // Performance by department
        $departments = Team::select('id', 'team_name')->get();
        $departmentPerformance = [];

        foreach ($departments as $department) {
            $avgScore = Audit::where('status', Audit::STATUS_COMPLETED)
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

            $monthAudits = Audit::where('status', Audit::STATUS_COMPLETED)
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
        $perPage = $request->get('per_page', 5);

        $query = Audit::with(['template', 'department', 'auditor', 'auditee']);

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

        $audits = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($audits);
    }

    /**
     * Export audit history to Excel
     */
    public function export(Request $request)
    {
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
            new AuditExport($departmentId, $status, $search, $startDate, $endDate),
            $filename
        );
    }
}
