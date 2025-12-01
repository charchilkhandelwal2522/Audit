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

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: none;
            background: #f1f5f9;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 2px;
            cursor: pointer;
        }
        .action-btn:hover {
            background: #e2e8f0;
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
                        <p class="stat-value">{{ $totalAudits }}</p>
                    </div>
                    <div class="stat-icon blue">
                        <i class="fa fa-clipboard-check f-20 text-blue"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-info">
                        <h5>@lang('audit::app.averageScore')</h5>
                        <p class="stat-value">{{ $averageScore }}%</p>
                    </div>
                    <div class="stat-icon yellow">
                        <i class="fa fa-star-half-alt f-20 text-yellow"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-info">
                        <h5>@lang('audit::app.auditsPassed') (>85%)</h5>
                        <p class="stat-value">{{ $auditsPassed }}</p>
                    </div>
                    <div class="stat-icon green">
                        <i class="fa fa-thumbs-up f-20 text-dark-green"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-info">
                        <h5>@lang('audit::app.auditsFailed') (<60%)</h5>
                        <p class="stat-value">{{ $auditsFailed }}</p>
                    </div>
                    <div class="stat-icon red">
                        <i class="fa fa-thumbs-down f-20 text-red"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-3">
                @include('audit::reports.score-distribution-chart')
            </div>
            <div class="col-lg-6 mb-3">
                @include('audit::reports.performance-by-department-chart')
            </div>
        </div>

        <!-- Audit Reports Section -->
        @include('audit::reports.audit-reports')

    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/jquery/daterangepicker.min.js') }}"></script>
    <script>
        
        // Date Range Picker
        $('#dashboard_date_range').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: '@lang("audit::app.clear")',
                applyLabel: '@lang("audit::app.apply")',
                format: '{{ company()->date_format }}'
            }
        });

        $('#dashboard_date_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('{{ company()->moment_date_format }}') + ' - ' + picker.endDate.format('{{ company()->moment_date_format }}'));
        });

        $('#dashboard_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        // Dashboard filters and table
        let currentPage = 1;

        function loadRecentAudits(page = 1) {
            currentPage = page;

            $.ajax({
                url: "{{ route('audit-dashboard.audits') }}",
                data: {
                    page: page,
                    department_id: $('#dashboard_department').val(),
                    auditor_id: $('#dashboard_auditor').val(),
                    auditee_id: $('#dashboard_auditee').val(),
                    date_range: $('#dashboard_date_range').val(),
                    per_page: 5
                },
                success: function(response) {
                    renderAuditsTable(response);
                }
            });
        }

        function renderAuditsTable(response) {
            let html = '';
            const audits = response.data || [];

            if (audits.length === 0) {
                html = '<tr><td colspan="7" class="text-center text-muted py-4">@lang("messages.noRecordFound")</td></tr>';
            } else {
                audits.forEach(function(audit) {
                    let scoreClass = 'high';
                    if (audit.score < 60) scoreClass = 'low';
                    else if (audit.score < 85) scoreClass = 'medium';

                    html += `
                        <tr>
                            <td>${audit.template?.title || '-'}</td>
                            <td>${audit.department?.team_name || '-'}</td>
                            <td>${audit.auditor?.name || '-'}</td>
                            <td>${audit.auditee?.name || '-'}</td>
                            <td><span class="score-badge ${scoreClass}">${Math.round(audit.score)}%</span></td>
                            <td>${audit.completed_at ? new Date(audit.completed_at).toLocaleDateString() : '-'}</td>
                            <td>
                                <a href="/account/audits/${audit.id}" class="action-btn" title="@lang('app.view')">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="/account/audits/${audit.id}/export-pdf" class="action-btn" title="@lang('audit::app.exportPdf')">
                                    <i class="fa fa-file-pdf"></i>
                                </a>
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
                $('#showingText').text(`Showing ${from} to ${to} of ${response.total} results`);

                // Simple pagination
                let paginationHtml = '<ul class="pagination pagination-sm mb-0">';
                if (response.current_page > 1) {
                    paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadRecentAudits(${response.current_page - 1}); return false;"><i class="fa fa-chevron-left"></i></a></li>`;
                }
                for (let i = 1; i <= response.last_page && i <= 3; i++) {
                    paginationHtml += `<li class="page-item ${response.current_page === i ? 'active' : ''}"><a class="page-link" href="#" onclick="loadRecentAudits(${i}); return false;">${i}</a></li>`;
                }
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
        $('#applyDashboardFilters').on('click', function() {
            loadRecentAudits(1);
        });

        $('#clearDashboardFilters').on('click', function() {
            $('#dashboard_date_range').val('');
            $('#dashboard_department').val('all').selectpicker('refresh');
            $('#dashboard_auditor').val('all').selectpicker('refresh');
            $('#dashboard_auditee').val('all').selectpicker('refresh');
            loadRecentAudits(1);
        });

        // Initial load
        $(document).ready(function() {
            loadRecentAudits(1);
        });
    </script>
@endpush

