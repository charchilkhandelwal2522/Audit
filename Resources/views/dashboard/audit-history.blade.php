<div class="audit-history-card mb-4 mt-4">
    <div class="card-header">
        <h4>@lang('audit::app.auditHistory')</h4>
        <div class="header-actions">
            <a href="{{ route('audits.index') }}" class="btn-export">
                <i class="fa fa-list"></i> @lang('audit::app.viewAllAudits')
            </a>
            @if(user()->permission('add_audit') == 'all' || user()->permission('add_audit') == 'added')
                <a href="{{ route('audits.create') }}" class="btn-new-audit openRightModal">
                    <i class="fa fa-plus"></i> @lang('audit::app.startNewAudit')
                </a>
            @endif
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-row">
        <div class="filter-item">
            <label>@lang('audit::app.dateRange')</label>
            <input type="text" id="dashboard_date_range" class="form-control" placeholder="@lang('audit::app.selectDateRange')">
        </div>
        <div class="filter-item">
            <label>@lang('audit::app.department')</label>
            <select id="dashboard_department" class="form-control select-picker" data-live-search="true">
                <option value="all">@lang('audit::app.allDepartments')</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->team_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-item">
            <label>@lang('audit::app.auditor')</label>
            <select id="dashboard_auditor" class="form-control select-picker" data-live-search="true">
                <option value="all">@lang('audit::app.allAuditors')</option>
                @foreach($auditors as $auditor)
                    <option value="{{ $auditor->id }}">{{ $auditor->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-item">
            <label>@lang('audit::app.auditee')</label>
            <select id="dashboard_auditee" class="form-control select-picker" data-live-search="true">
                <option value="all">@lang('audit::app.allAuditees')</option>
                @foreach($auditees as $auditee)
                    <option value="{{ $auditee->id }}">{{ $auditee->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-buttons">
            <button type="button" class="btn-filter" id="applyDashboardFilters">
                <i class="fa fa-filter"></i> @lang('audit::app.filter')
            </button>
            <button type="button" class="btn-clear" id="clearDashboardFilters">
                @lang('audit::app.clear')
            </button>
        </div>
    </div>

    <!-- Recent Audits Table -->
    <div class="table-responsive">
        <table class="audit-table" id="recentAuditsTable">
            <thead>
                <tr>
                    <th>@lang('audit::app.auditTitle')</th>
                    <th>@lang('audit::app.department')</th>
                    <th>@lang('audit::app.auditor')</th>
                    <th>@lang('audit::app.auditee')</th>
                    <th>@lang('audit::app.score')</th>
                    <th>@lang('app.date')</th>
                    <th>@lang('app.action')</th>
                </tr>
            </thead>
            <tbody id="recentAuditsBody">
                <!-- Data loaded via AJAX -->
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3" id="paginationInfo">
        <span class="text-muted f-13" id="showingText"></span>
        <nav id="paginationNav"></nav>
    </div>
</div>
