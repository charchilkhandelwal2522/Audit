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
        <div class="filter-item" style="flex: 2;">
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
        <div class="filter-item">
            <input type="text" class="form-control" id="report_date_range" placeholder="mm/dd/yyyy">
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

@push('scripts')
<script>
    // Report Date Range Picker
    $('#report_date_range').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: '@lang("audit::app.clear")',
            applyLabel: '@lang("audit::app.apply")',
            format: '{{ company()->date_format }}'
        }
    });

    $('#report_date_range').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('{{ company()->moment_date_format }}') + ' - ' + picker.endDate.format('{{ company()->moment_date_format }}'));
        loadAuditReports(1);
    });

    $('#report_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        loadAuditReports(1);
    });

    let reportCurrentPage = 1;

    function loadAuditReports(page = 1) {
        reportCurrentPage = page;

        $.ajax({
            url: "{{ route('audit-reports.audits') }}",
            data: {
                page: page,
                department_id: $('#report_department').val(),
                score_range: $('#report_score').val(),
                search: $('#report_search').val(),
                date_range: $('#report_date_range').val(),
                per_page: 4
            },
            success: function(response) {
                renderAuditReportTable(response);
            },
            error: function() {
                $('#auditReportBody').html('<tr><td colspan="6" class="text-center text-danger py-4">@lang("messages.errorOccured")</td></tr>');
            }
        });
    }

    function renderAuditReportTable(response) {
        let html = '';
        const audits = response.data || [];

        if (audits.length === 0) {
            html = '<tr><td colspan="6" class="text-center text-muted py-4">@lang("messages.noRecordFound")</td></tr>';
        } else {
            audits.forEach(function(audit) {
                let scoreClass = 'high';
                let scoreValue = Math.round(audit.score);
                if (scoreValue < 60) scoreClass = 'low';
                else if (scoreValue < 85) scoreClass = 'medium';

                let scoreColor = scoreClass === 'high' ? 'text-success' : (scoreClass === 'medium' ? 'text-warning' : 'text-danger');

                html += `
                    <tr>
                        <td>${audit.auditee?.name || '-'}</td>
                        <td>${audit.department?.team_name || '-'}</td>
                        <td>${audit.auditor?.name || '-'}</td>
                        <td><span class="${scoreColor} font-weight-bold">${scoreValue}%</span></td>
                        <td>${audit.completed_at ? new Date(audit.completed_at).toLocaleDateString() : '-'}</td>
                        <td>
                            <a href="{{ url('account/audit/audits') }}/${audit.id}" class="action-btn openRightModal" title="@lang('app.view')">
                                <i class="fa fa-eye"></i>
                            </a>
                            <a href="{{ url('account/audit/audits') }}/${audit.id}/export-pdf" class="action-btn" title="@lang('audit::app.exportPdf')">
                                <i class="fa fa-download"></i>
                            </a>
                        </td>
                    </tr>
                `;
            });
        }

        $('#auditReportBody').html(html);

        // Update pagination info
        if (response.total) {
            const from = response.from || 0;
            const to = response.to || 0;
            $('#reportShowingText').text(`Showing ${from} to ${to} of ${response.total} entries`);

            // Build pagination
            let paginationHtml = '<ul class="pagination pagination-sm mb-0">';
            
            // Previous button
            if (response.current_page > 1) {
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadAuditReports(${response.current_page - 1}); return false;">Previous</a></li>`;
            } else {
                paginationHtml += `<li class="page-item disabled"><span class="page-link">Previous</span></li>`;
            }

            // Page numbers
            let startPage = Math.max(1, response.current_page - 1);
            let endPage = Math.min(response.last_page, startPage + 2);
            
            for (let i = startPage; i <= endPage; i++) {
                if (response.current_page === i) {
                    paginationHtml += `<li class="page-item active"><span class="page-link" style="background-color: #ef4444; border-color: #ef4444;">${i}</span></li>`;
                } else {
                    paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadAuditReports(${i}); return false;">${i}</a></li>`;
                }
            }

            // Ellipsis and last page
            if (endPage < response.last_page - 1) {
                paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            if (endPage < response.last_page) {
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadAuditReports(${response.last_page}); return false;">${response.last_page}</a></li>`;
            }

            // Next button
            if (response.current_page < response.last_page) {
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadAuditReports(${response.current_page + 1}); return false;">Next</a></li>`;
            } else {
                paginationHtml += `<li class="page-item disabled"><span class="page-link">Next</span></li>`;
            }

            paginationHtml += '</ul>';
            $('#reportPaginationNav').html(paginationHtml);
        } else {
            $('#reportShowingText').text('');
            $('#reportPaginationNav').html('');
        }
    }

    // Filter events
    $('#report_search').on('keyup', function() {
        loadAuditReports(1);
    });

    $('#report_department, #report_score').on('change', function() {
        loadAuditReports(1);
    });

    $('#clearReportFilters').on('click', function() {
        $('#report_search').val('');
        $('#report_date_range').val('');
        $('#report_department').val('all').selectpicker('refresh');
        $('#report_score').val('all').selectpicker('refresh');
        loadAuditReports(1);
    });

    // Export buttons
    $('#exportExcel').on('click', function() {
        let params = new URLSearchParams({
            department_id: $('#report_department').val(),
            score_range: $('#report_score').val(),
            search: $('#report_search').val(),
            date_range: $('#report_date_range').val(),
            format: 'excel'
        });
        window.location.href = "{{ route('audit-reports.export') }}?" + params.toString();
    });

    $('#exportPdf').on('click', function() {
        let params = new URLSearchParams({
            department_id: $('#report_department').val(),
            score_range: $('#report_score').val(),
            search: $('#report_search').val(),
            date_range: $('#report_date_range').val(),
            format: 'pdf'
        });
        window.location.href = "{{ route('audit-reports.export') }}?" + params.toString();
    });

    // Initial load
    $(document).ready(function() {
        loadAuditReports(1);
    });
</script>
@endpush
