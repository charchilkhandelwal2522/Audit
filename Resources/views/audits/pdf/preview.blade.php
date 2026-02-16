<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@lang('audit::app.auditReports') #{{ $audit->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #007bff;
            margin-bottom: 5px;
        }
        .score-box {
            background-color: {{ $audit->score >= 80 ? '#d4edda' : ($audit->score >= 60 ? '#fff3cd' : '#f8d7da') }};
            border: 1px solid {{ $audit->score >= 80 ? '#c3e6cb' : ($audit->score >= 60 ? '#ffeeba' : '#f5c6cb') }};
            padding: 15px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .score-box .score {
            font-size: 48px;
            font-weight: bold;
            color: {{ $audit->score >= 80 ? '#155724' : ($audit->score >= 60 ? '#856404' : '#721c24') }};
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 8px;
            border: 1px solid #dee2e6;
        }
        .info-table td:first-child {
            background-color: #f8f9fa;
            font-weight: bold;
            width: 30%;
        }
        .section-title {
            color: grey;
            padding: 10px;
            margin: 20px 0 10px 0;
            border: 1px solid #DBDBDB;
            background-color: #f1f1f3;
            font-weight: 700;
        }
        .checkpoint-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .checkpoint-table thead {
            display: table-header-group;
        }
        .checkpoint-table th {
            background-color: #f8f9fa;
            padding: 10px;
            border: 1px solid #dee2e6;
            text-align: left;
        }
        .checkpoint-table td {
            padding: 10px;
            border: 1px solid #dee2e6;
            vertical-align: top;
            page-break-inside: auto;
            overflow: visible;
        }
        .checkpoint-table tbody tr {
            page-break-inside: auto;
        }
        .status-completed {
            color: #155724;
            background-color: #d4edda;
            padding: 3px 8px;
            border-radius: 3px;
        }
        .status-partial {
            color: #856404;
            background-color: #fff3cd;
            padding: 3px 8px;
            border-radius: 3px;
        }
        .status-not-completed {
            color: #721c24;
            background-color: #f8d7da;
            padding: 3px 8px;
            border-radius: 3px;
        }
        .file-list {
            margin-top: 8px;
            font-size: 10px;
            page-break-inside: auto;
        }
        .file-item {
            margin-bottom: 6px;
            padding: 6px;
            background-color: #f8f9fa;
            border-left: 3px solid #007bff;
            page-break-inside: auto;
        }
        .file-item-image {
            display: block;
            margin-bottom: 12px;
            text-align: center;
            page-break-inside: avoid;
            page-break-after: auto;
        }
        .file-item-image img {
            max-width: 120px;
            max-height: 120px;
            border: 1px solid #dee2e6;
            padding: 3px;
            background-color: #fff;
        }
        .file-cell {
            page-break-inside: auto;
        }
        .file-item-image small {
            display: block;
            margin-top: 3px;
            color: #6c757d;
            word-break: break-word;
        }
        .file-link {
            color: #007bff;
            text-decoration: underline;
            word-break: break-all;
        }
        .file-icon {
            margin-right: 4px;
            color: #6c757d;
        }
        .file-size {
            color: #6c757d;
            font-size: 9px;
        }
        .summary-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            /* border-top: 1px solid #dee2e6; */
            font-size: 10px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>@lang('audit::app.auditReport')</h1>
        <p>@lang('audit::app.generatedOn') {{ now()->format('F d, Y H:i') }}</p>
    </div>

    <!-- Score Box -->
    <div class="score-box">
        <div class="score">{{ $audit->score }}%</div>
        <p>@lang('audit::app.overallScore')</p>
    </div>

    <!-- Audit Information -->
    <h3 class="section-title">@lang('audit::app.auditInformation')</h3>
    <table class="info-table">
        <tr>
            <td>@lang('app.id')</td>
            <td>#{{ $audit->id }}</td>
        </tr>
        <tr>
            <td>@lang('audit::app.department')</td>
            <td>{{ $audit->department ? $audit->department->team_name : '--' }}</td>
        </tr>
        <tr>
            <td>@lang('audit::app.template')</td>
            <td>{{ $audit->template->title }}</td>
        </tr>
        <tr>
            <td>@lang('audit::app.auditor')</td>
            <td>{{ $audit->auditor ? $audit->auditor->name : '--' }}</td>
        </tr>
        <tr>
            <td>@lang('audit::app.auditee')</td>
            <td>{{ $audit->auditee ? $audit->auditee->name : '--' }}</td>
        </tr>
        <tr>
            <td>@lang('audit::app.startedOn')</td>
            <td>{{ $audit->started_at ? $audit->started_at->format('F d, Y H:i') : '--' }}</td>
        </tr>
        <tr>
            <td>@lang('audit::app.completedOn')</td>
            <td>{{ $audit->completed_at ? $audit->completed_at->format('F d, Y H:i') : '--' }}</td>
        </tr>
        <tr>
            <td>@lang('audit::app.totalTimeTaken')</td>
            <td>{{ $audit->duration_formatted }}</td>
        </tr>
    </table>

    <!-- Score Summary -->
    <h3 class="section-title">@lang('audit::app.scoreSummary')</h3>
    <table class="info-table">
        <tr>
            <td>@lang('audit::app.totalCheckpoints')</td>
            <td>{{ $audit->total_checkpoints }}</td>
        </tr>
        <tr>
            <td>@lang('audit::app.completed')</td>
            <td>{{ $audit->completed_checkpoints }}</td>
        </tr>
        <tr>
            <td>@lang('audit::app.partiallyCompleted')</td>
            <td>{{ $audit->partially_completed_checkpoints }}</td>
        </tr>
        <tr>
            <td>@lang('audit::app.notCompleted')</td>
            <td>{{ $audit->total_checkpoints - $audit->completed_checkpoints - $audit->partially_completed_checkpoints }}</td>
        </tr>
    </table>

    <!-- Checkpoint Details -->
    <h3 class="section-title">@lang('audit::app.checkpointDetails')</h3>
    <table class="checkpoint-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="25%">@lang('audit::app.checkpoint')</th>
                <th width="12%">@lang('app.status')</th>
                <th width="28%">@lang('app.notes')</th>
                <th width="30%">@lang('audit::app.files')</th>
            </tr>
        </thead>
        <tbody>
            @php $embedMap = $embedMap ?? []; @endphp
            @foreach($audit->responses as $index => $response)
            @php
                $files = $response->files ?? collect();
                $fileCount = $files->count();
            @endphp
            @if($fileCount > 0)
                @foreach($files as $fileIndex => $file)
                <tr>
                    @if($fileIndex === 0)
                        <td rowspan="{{ $fileCount }}">{{ $index + 1 }}</td>
                        <td rowspan="{{ $fileCount }}">
                            <strong>{{ $response->checkpoint->title }}</strong>
                            @if($response->checkpoint->description)
                                <br><small>{{ $response->checkpoint->description }}</small>
                            @endif
                        </td>
                        <td rowspan="{{ $fileCount }}">
                            @if($response->status == 'completed')
                                @lang('audit::app.completed')
                            @elseif($response->status == 'partially_completed')
                                @lang('audit::app.partiallyCompleted')
                            @else
                                @lang('audit::app.notCompleted')
                            @endif
                        </td>
                        <td rowspan="{{ $fileCount }}">{{ $response->notes ?: '--' }}</td>
                    @endif
                    <td class="file-cell">
                        @if($file->isImage())
                            @if(!empty($embedMap[$file->id]))
                                <div class="file-item-image">
                                    @if(str_starts_with($embedMap[$file->id], 'data:'))
                                        <img src="{{ $embedMap[$file->id] }}" alt="{{ $file->filename }}" />
                                    @else
                                        <img src="file://{{ str_replace('\\', '/', $embedMap[$file->id]) }}" alt="{{ $file->filename }}" />
                                    @endif
                                </div>
                            @elseif(empty($embedMap))
                                <div class="file-item-image">
                                    <img src="{{ $file->file_url }}" alt="{{ $file->filename }}" />
                                </div>
                            @else
                                <div class="file-item"><span class="file-icon">🖼</span> {{ $file->filename }}</div>
                            @endif
                        @else
                            <div class="file-item">
                                <span class="file-icon">📎</span>
                                <span class="file-link">{{ $file->filename }}</span>
                                <span class="file-size">({{ $file->formatted_size }})</span>
                            </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            @else
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $response->checkpoint->title }}</strong>
                    @if($response->checkpoint->description)
                        <br><small>{{ $response->checkpoint->description }}</small>
                    @endif
                </td>
                <td>
                    @if($response->status == 'completed')
                        @lang('audit::app.completed')
                    @elseif($response->status == 'partially_completed')
                        @lang('audit::app.partiallyCompleted')
                    @else
                        @lang('audit::app.notCompleted')
                    @endif
                </td>
                <td>{{ $response->notes ?: '--' }}</td>
                <td><span style="color: #6c757d;">--</span></td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>

    @if($audit->summary)
    <div class="summary-box">
        <h4>@lang('app.summary')</h4>
        <p>{{ $audit->summary }}</p>
    </div>
    @endif

    <div class="footer">
        <p>@lang('audit::messages.auditReportGenerated')</p>
        <p>Report ID: {{ $audit->id }} | Generated: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>

