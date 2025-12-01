<?php

namespace Modules\Audit\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Modules\Audit\Entities\Audit;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;

class AuditReportController extends AccountBaseController
{
    public function index()
    {
        $this->pageTitle = __('audit::app.summaryReports');

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

        return view('audit::reports.index', $this->data);
    }

    /**
     * Get paginated audit reports for AJAX table
     */
    public function audits(Request $request)
    {
        $perPage = $request->per_page ?? 4;

        $audits = Audit::with(['template', 'department', 'auditor', 'auditee'])
            ->where('status', Audit::STATUS_COMPLETED);

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
        if ($request->date_range) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $startDate = Carbon::createFromFormat($this->company->date_format, trim($dates[0]))->startOfDay();
                $endDate = Carbon::createFromFormat($this->company->date_format, trim($dates[1]))->endOfDay();
                $audits->whereBetween('completed_at', [$startDate, $endDate]);
            }
        }

        return $audits->orderBy('completed_at', 'desc')->paginate($perPage);
    }

    /**
     * Export audit reports
     */
    public function export(Request $request)
    {
        $audits = Audit::with(['template', 'department', 'auditor', 'auditee'])
            ->where('status', Audit::STATUS_COMPLETED);

        // Apply same filters as audits method
        if ($request->department_id && $request->department_id != 'all') {
            $audits->where('department_id', $request->department_id);
        }

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

        if ($request->search) {
            $search = $request->search;
            $audits->where(function ($query) use ($search) {
                $query->whereHas('auditee', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        if ($request->date_range) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $startDate = Carbon::createFromFormat($this->company->date_format, trim($dates[0]))->startOfDay();
                $endDate = Carbon::createFromFormat($this->company->date_format, trim($dates[1]))->endOfDay();
                $audits->whereBetween('completed_at', [$startDate, $endDate]);
            }
        }

        $audits = $audits->orderBy('completed_at', 'desc')->get();

        // For now, redirect back. You can implement actual export logic here
        // using Maatwebsite Excel or similar package
        if ($request->format === 'excel') {
            // TODO: Implement Excel export
            return back()->with('success', 'Excel export coming soon');
        }

        if ($request->format === 'pdf') {
            // TODO: Implement PDF export
            return back()->with('success', 'PDF export coming soon');
        }

        return back();
    }
}