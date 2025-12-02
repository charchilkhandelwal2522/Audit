<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@lang('audit::app.auditReports')</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        td {
            border: 1px solid #d1d5db;
            padding: 8px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>@lang('audit::app.auditReports')</h1>
        <p>{{ now()->format(company()->date_format) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>@lang('audit::app.auditee')</th>
                <th>@lang('audit::app.department')</th>
                <th>@lang('audit::app.auditor')</th>
                <th class="text-center">@lang('audit::app.score')</th>
                <th class="text-center">@lang('app.date')</th>
            </tr>
        </thead>
        <tbody>
            @forelse($audits as $audit)
            <tr>
                <td>{{ $audit->auditee?->name ?? '-' }}</td>
                <td>{{ $audit->department?->team_name ?? '-' }}</td>
                <td>{{ $audit->auditor?->name ?? '-' }}</td>
                <td class="text-center">{{ round($audit->score) }}%</td>
                <td class="text-center">{{ $audit->completed_at ? $audit->completed_at->format(company()->date_format) : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">@lang('messages.noRecordFound')</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 10px; color: #6c757d;">
        <p>@lang('app.total'): {{ $audits->count() }} @lang('audit::app.auditReports')</p>
    </div>
</body>
</html>

