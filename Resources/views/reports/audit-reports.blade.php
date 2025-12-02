<div class="audit-history-card mt-4">
    <div class="card-header">
        <h4>@lang('audit::app.auditReports')</h4>
        <div class="header-actions">
            <button class="btn btn-success" id="exportExcel">
                <i class="fa fa-file-excel mr-1"></i> @lang('app.exportExcel')
            </button>
            <button class="btn btn-dark" id="exportPdf">
                <i class="fa fa-file-pdf mr-1"></i> @lang('audit::app.exportPdf')
            </button>
        </div>
    </div>

    <!-- Filters Row -->
    <div class="filter-row">
        <div class="filter-item border" style="flex: 2;">
            <div class="input-group bg-grey rounded">
                <div class="input-group-prepend">
                    <span class="input-group-text border-0 bg-white" style="border-radius: 8px 0 0 8px;">
                        <i class="fa fa-search text-muted"></i>
                    </span>
                </div>
                <input type="text" class="form-control border-0" id="report_search" 
                    placeholder="@lang('audit::app.searchByAuditee')" style="border-radius: 0 8px 8px 0;">
            </div>
        </div>
        <div class="select-status d-flex pr-2">
            <div class="select-status d-flex border">
                <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                    id="datatableRange" placeholder="@lang('placeholders.dateRange')"
                    value="">
            </div>
        </div>
        <div class="filter-item">
            <select class="form-control select-picker" id="report_department" data-live-search="true" data-size="8">
                <option value="all">@lang('audit::app.allDepartments')</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->team_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-item">
            <select class="form-control select-picker" id="report_score" data-size="8">
                <option value="all">@lang('audit::app.allScores')</option>
                <option value="high">@lang('audit::app.highScore') (≥85%)</option>
                <option value="medium">@lang('audit::app.mediumScore') (60-84%)</option>
                <option value="low">@lang('audit::app.lowScore') (<60%)</option>
            </select>
        </div>
        <div class="filter-buttons">
            <button class="btn-clear" id="clearReportFilters">@lang('app.clearFilters')</button>
        </div>
    </div>

    <!-- Audit Table -->
    <table class="audit-table">
        <thead>
            <tr>
                <th>@lang('audit::app.auditee')</th>
                <th>@lang('audit::app.department')</th>
                <th>@lang('audit::app.auditor')</th>
                <th>@lang('audit::app.score')</th>
                <th>@lang('app.date')</th>
                <th>@lang('app.action')</th>
            </tr>
        </thead>
        <tbody id="auditReportBody">
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    <i class="fa fa-spinner fa-spin"></i> @lang('app.loading')...
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted f-14" id="reportShowingText"></div>
        <nav id="reportPaginationNav"></nav>
    </div>
</div>
