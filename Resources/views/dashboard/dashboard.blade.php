@extends('layouts.app')

@push('styles')
    <script src="{{ asset('vendor/jquery/Chart.min.js') }}"></script>
    <style>
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .stat-card .stat-info h5 {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 4px;
            font-weight: 500;
        }
        .stat-card .stat-info .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .stat-icon.blue { background: #dbeafe; }
        .stat-icon.yellow { background: #fef3c7; }
        .stat-icon.green { background: #d1fae5; }
        .stat-icon.red { background: #fee2e2; }
        .stat-icon.blue i { color: #3b82f6; }
        .stat-icon.yellow i { color: #f59e0b; }
        .stat-icon.green i { color: #10b981; }
        .stat-icon.red i { color: #ef4444; }

        .chart-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .chart-card h4 {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
        }
        .chart-container {
            position: relative;
            height: 220px;
            width: 100%;
        }
        .chart-container-large {
            position: relative;
            height: 280px;
            width: 100%;
        }

        .score-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .score-badge.high { background: #d1fae5; color: #059669; }
        .score-badge.medium { background: #fef3c7; color: #d97706; }
        .score-badge.low { background: #fee2e2; color: #dc2626; }

        .audit-history-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .audit-history-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .audit-history-card .card-header h4 {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        .audit-history-card .header-actions {
            display: flex;
            gap: 10px;
        }

        .filter-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .filter-item {
            flex: 1;
            min-width: 150px;
        }
        .filter-item label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 4px;
            display: block;
        }
        .filter-item select,
        .filter-item input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
        }
        .filter-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-filter {
            background: #10b981;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-clear {
            background: #374151;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        .btn-export {
            background: white;
            border: 1px solid #e2e8f0;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-new-audit {
            background: #10b981;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .audit-table {
            width: 100%;
        }
        .audit-table th {
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            padding: 12px 8px;
            border-bottom: 1px solid #e2e8f0;
            text-align: center;
        }
        .audit-table td {
            padding: 16px 8px;
            border-bottom: 1px solid #f1f5f9;
            text-align: center;
            font-size: 14px;
        }
        .audit-table tr:hover {
            background: #f8fafc;
        }

    </style>
@endpush

@section('content')
    <div class="content-wrapper">
        <!-- Stats Cards Row -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-info">
                        <h5>@lang('audit::app.totalAudits')</h5>
                        <p class="stat-value">{{ number_format($totalAudits) }}</p>
                    </div>
                    <div class="stat-icon blue">
                        <i class="fa fa-clipboard-list f-20 text-blue"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-info">
                        <h5>@lang('audit::app.averageScore')</h5>
                        <p class="stat-value">{{ number_format($averageScore, 1) }}%</p>
                    </div>
                    <div class="stat-icon green">
                        <i class="fa fa-star-half-alt f-20 text-dark-green"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-info">
                        <h5>@lang('audit::app.auditsInProgress')</h5>
                        <p class="stat-value">{{ $auditsInProgress }}</p>
                    </div>
                    <div class="stat-icon yellow">
                        <i class="fa fa-spinner f-20 text-yellow"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-info">
                        <h5>@lang('audit::app.failedAudits')</h5>
                        <p class="stat-value">{{ $auditsFailed }}</p>
                    </div>
                    <div class="stat-icon red">
                        <i class="fa fa-times-circle f-20 text-red"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Overview Chart -->
        @include('audit::dashboard.permormance-overview-chart')

        <!-- Audit History Section -->
        @include('audit::dashboard.audit-history')

    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/jquery/daterangepicker.min.js') }}"></script>
    <script>

        $('#exportAuditHistory').click(function() {
            var dateRangePicker = $('#datatableRange').data('daterangepicker');
            var dateRangeVal = $('#datatableRange').val();
            var startDate = '';
            var endDate = '';

            if (dateRangeVal !== '') {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            // Build export URL with current filters
            let params = new URLSearchParams({
                department_id: $('#dashboard_department').val() || 'all',
                status: $('#dashboard_status').val() || 'all',
                search: $('#dashboard_search').val() || '',
                start_date: startDate,
                end_date: endDate
            });
            
            window.location.href = "{{ route('audit-dashboard.export') }}?" + params.toString();
        });

        // Dashboard filters and table
        let currentPage = 1;

        function loadRecentAudits(page = 1) {
            currentPage = page;

            var dateRangePicker = $('#datatableRange').data('daterangepicker');
            var dateRangeVal = $('#datatableRange').val();
            var startDate = null;
            var endDate = null;

            if (dateRangeVal !== '') {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            $.ajax({
                url: "{{ route('audit-dashboard.audits') }}",
                data: {
                    page: page,
                    department_id: $('#dashboard_department').val() || 'all',
                    status: $('#dashboard_status').val() || 'all',
                    search: $('#dashboard_search').val() || '',
                    start_date: startDate,
                    end_date: endDate,
                    per_page: 5
                },
                success: function(response) {
                    renderAuditsTable(response);
                },
                error: function(xhr, status, error) {
                    $('#recentAuditsBody').html('<tr><td colspan="8" class="text-center text-danger py-4">@lang("messages.errorOccured")</td></tr>');
                }
            });
        }

        function getStatusBadge(status, score) {
            if (status === 'completed') {
                if (score < 60) {
                    return '<span class="badge badge-danger" style="background: #fee2e2; color: #dc2626; padding: 4px 12px; border-radius: 4px;">@lang("audit::app.failed")</span>';
                }
                return '<span class="badge badge-success" style="background: #d1fae5; color: #059669; padding: 4px 12px; border-radius: 4px;">@lang("audit::app.completed")</span>';
            } else if (status === 'in_progress') {
                return '<span class="badge badge-warning" style="background: #fef3c7; color: #d97706; padding: 4px 12px; border-radius: 4px;">@lang("audit::app.inProgress")</span>';
            } else if (status === 'cancelled') {
                return '<span class="badge badge-secondary" style="background: #e2e8f0; color: #64748b; padding: 4px 12px; border-radius: 4px;">@lang("audit::app.cancelled")</span>';
            }
            return '-';
        }

        function formatAuditId(id) {
            return '#AUD-' + String(id).padStart(4, '0');
        }

        function renderAuditsTable(response) {
            let html = '';
            const audits = response.data || [];

            if (audits.length === 0) {
                html = '<tr><td colspan="8" class="text-center text-muted py-4">@lang("messages.noRecordFound")</td></tr>';
            } else {
                audits.forEach(function(audit) {
                    let scoreDisplay = audit.status === 'completed' ? `<span class="font-weight-bold">${Math.round(audit.score)}%</span>` : '-';
                    let dateDisplay = audit.completed_at ? new Date(audit.completed_at).toLocaleDateString('en-CA') : '-';

                    html += `
                        <tr>
                            <td class="font-weight-bold">${formatAuditId(audit.id)}</td>
                            <td>${audit.department?.team_name || '-'}</td>
                            <td>${audit.auditor?.name || '-'}</td>
                            <td>${audit.auditee?.name || '-'}</td>
                            <td>${dateDisplay}</td>
                            <td>${scoreDisplay}</td>
                            <td>${getStatusBadge(audit.status, audit.score)}</td>
                            <td>
                                <div class="task_view">
                                    <div class="dropdown">
                                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link" id="dropdownMenuLink-${audit.id}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="icon-options-vertical icons"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-${audit.id}" tabindex="0">
                                            <a href="{{ url('account/audits') }}/${audit.id}" class="dropdown-item openRightModal">
                                                <i class="fa fa-eye mr-2"></i>@lang('app.view')
                                            </a>
                                            ${audit.status === 'completed' ? `<a href="{{ url('account/audits') }}/${audit.id}/export-pdf" class="dropdown-item">
                                                <i class="fa fa-download mr-2"></i>@lang('audit::app.exportPdf')
                                            </a>` : ''}
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            }

            $('#recentAuditsBody').html(html);

            // Update pagination info
            if (response.total) {
                const from = response.from || 0;
                const to = response.to || 0;
                $('#showingText').text(`Showing ${from} to ${to} of ${response.total} audits`);

                // Build pagination
                let paginationHtml = '<ul class="pagination pagination-sm mb-0">';
                
                // Previous button
                if (response.current_page > 1) {
                    paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadRecentAudits(${response.current_page - 1}); return false;"><i class="fa fa-chevron-left"></i></a></li>`;
                }

                // Page numbers
                let startPage = Math.max(1, response.current_page - 1);
                let endPage = Math.min(response.last_page, startPage + 2);
                
                for (let i = startPage; i <= endPage; i++) {
                    if (response.current_page === i) {
                        paginationHtml += `<li class="page-item active"><span class="page-link" style="background-color: #10b981; border-color: #10b981;">${i}</span></li>`;
                    } else {
                        paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadRecentAudits(${i}); return false;">${i}</a></li>`;
                    }
                }

                // Ellipsis and last page
                if (endPage < response.last_page - 1) {
                    paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                if (endPage < response.last_page) {
                    paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadRecentAudits(${response.last_page}); return false;">${response.last_page}</a></li>`;
                }

                // Next button
                if (response.current_page < response.last_page) {
                    paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadRecentAudits(${response.current_page + 1}); return false;"><i class="fa fa-chevron-right"></i></a></li>`;
                }

                paginationHtml += '</ul>';
                $('#paginationNav').html(paginationHtml);
            } else {
                $('#showingText').text('');
                $('#paginationNav').html('');
            }
        }

        // Filter events
        $('#dashboard_search').on('keyup', function() {
            loadRecentAudits(1);
        });

        $('#dashboard_department, #dashboard_status').on('change', function() {
            loadRecentAudits(1);
        });

        // Initial load
        $(document).ready(function() {
            loadRecentAudits(1);
        });
    </script>
@endpush

