<?php

namespace Modules\Audit\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Modules\Audit\Entities\Audit;
use App\Models\Team;
use App\Models\User;

class AuditDashboard extends AccountBaseController
{

    /**
     * Get recent audits for dashboard table (AJAX).
     */
    public function dashboardAudits(Request $request)
    {
        $perPage = $request->get('per_page', 5);

        $query = Audit::with(['template', 'department', 'auditor', 'auditee'])
            ->where('status', Audit::STATUS_COMPLETED);

        // Apply filters
        if ($request->department_id && $request->department_id != 'all') {
            $query->where('department_id', $request->department_id);
        }

        if ($request->auditor_id && $request->auditor_id != 'all') {
            $query->where('auditor_id', $request->auditor_id);
        }

        if ($request->auditee_id && $request->auditee_id != 'all') {
            $query->where('auditee_id', $request->auditee_id);
        }

        if ($request->date_range) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $startDate = \Carbon\Carbon::createFromFormat(company()->date_format, trim($dates[0]))->startOfDay();
                $endDate = \Carbon\Carbon::createFromFormat(company()->date_format, trim($dates[1]))->endOfDay();
                $query->whereBetween('completed_at', [$startDate, $endDate]);
            }
        }

        $audits = $query->orderBy('completed_at', 'desc')->paginate($perPage);

        return response()->json($audits);
    }

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

        // Average score
        $this->averageScore = round($completedAudits->avg('score') ?? 0, 0);

        // Audits passed (>85%)
        $this->auditsPassed = Audit::where('status', Audit::STATUS_COMPLETED)
            ->where('score', '>=', 85)
            ->count();

        // Audits failed (<60%)
        $this->auditsFailed = Audit::where('status', Audit::STATUS_COMPLETED)
            ->where('score', '<', 60)
            ->count();

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
}
