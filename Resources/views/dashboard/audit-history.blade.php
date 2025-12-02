<div class="audit-history-card mb-4 mt-4">
    <div class="card-header">
        <h4>@lang('audit::app.auditHistory')</h4>
        <div class="header-actions">
            <button class="btn-export" id="exportAuditHistory">
                <i class="fa fa-file-export"></i> @lang('app.exportExcel')
            </button>
            @if(user()->permission('add_audit') == 'all' || user()->permission('add_audit') == 'added')
                <a href="{{ route('audits.create') }}" class="btn-new-audit openRightModal">
                    <i class="fa fa-plus"></i> @lang('audit::app.startNewAudit')
                </a>
            @endif
        </div>
    </div>

    <div class="filter-row">
        <div class="filter-item" style="flex: 2;">
            <div class="input-group bg-grey rounded">
                <div class="input-group-prepend">
                    <span class="input-group-text border bg-white" style="border-radius: 8px 0 0 8px;">
                        <i class="fa fa-search text-muted"></i>
                    </span>
                </div>
                <input type="text" class="form-control border" id="dashboard_search" 
                    placeholder="@lang('audit::app.searchPlaceholder')" style="border-radius: 0 8px 8px 0;">
            </div>
        </div>
        <div class="filter-item">
            <select id="dashboard_department" class="form-control select-picker" data-live-search="true" data-size="8">
                <option value="all">@lang('audit::app.allDepartments')</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->team_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-item">
            <div class="select-status d-flex">
                <input type="text" class="position-relative text-dark form-control p-2 text-left f-14 f-w-500"
                    id="datatableRange" placeholder="@lang('placeholders.dateRange')"
                    value="{{ request('start') && request('end') ? request('start') . ' ' . __('app.to') . ' ' . request('end') : '' }}">
            </div>
        </div>
        <div class="filter-item">
            <select id="dashboard_status" class="form-control select-picker" data-size="8">
                <option value="all">@lang('audit::app.allStatuses')</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}">{{ __('audit::app.' . $status) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Recent Audits Table -->
    <div class="table-responsive">
        <table class="audit-table" id="recentAuditsTable">
            <thead>
                <tr>
                    <th>@lang('audit::app.auditId')</th>
                    <th>@lang('audit::app.department')</th>
                    <th>@lang('audit::app.auditor')</th>
                    <th>@lang('audit::app.auditee')</th>
                    <th>@lang('app.date')</th>
                    <th>@lang('audit::app.score')</th>
                    <th>@lang('app.status')</th>
                    <th>@lang('app.action')</th>
                </tr>
            </thead>
            <tbody id="recentAuditsBody">
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fa fa-spinner fa-spin"></i> @lang('app.loading')...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3" id="paginationInfo">
        <span class="text-muted f-13" id="showingText"></span>
        <nav id="paginationNav"></nav>
    </div>
</div>
