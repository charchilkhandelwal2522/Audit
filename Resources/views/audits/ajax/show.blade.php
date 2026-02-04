<style>
    .audit-result-wrapper {
        padding: 20px;
    }
    .audit-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 25px;
    }
    .audit-title-section h2 {
        font-size: 24px;
        font-weight: 600;
        color: #1a1a2e;
        margin-bottom: 5px;
    }
    .audit-title-section .completion-date {
        font-size: 14px;
        color: #6c757d;
    }
    .audit-actions .btn {
        margin-left: 10px;
    }
    .btn-print {
        background: #fff;
        border: 1px solid #dee2e6;
        color: #333;
    }
    .btn-print:hover {
        background: #f8f9fa;
    }
    .btn-download-pdf {
        background: #28a745;
        border: none;
        color: #fff;
    }
    .btn-download-pdf:hover {
        background: #218838;
        color: #fff;
    }
    .audit-summary-card {
        background: #fff;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .summary-content {
        display: flex;
        gap: 40px;
    }
    .score-circle-wrapper {
        flex-shrink: 0;
        position: relative;
        width: 160px;
        height: 160px;
    }
    .score-circle-svg {
        width: 160px;
        height: 160px;
    }
    .score-segment {
        fill: none;
        stroke-width: 10;
        stroke-linecap: round;
    }
    .score-segment.bg {
        stroke: #d1fae5;
    }
    .score-segment.filled.score-high {
        stroke: #10b981;
    }
    .score-segment.filled.score-medium {
        stroke: #f59e0b;
    }
    .score-segment.filled.score-low {
        stroke: #ef4444;
    }
    .score-circle-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }
    .score-circle-content .score-value {
        font-size: 25px;
        font-weight: 700;
        color: #10b981;
        line-height: 1;
    }
    .score-circle-content.score-medium .score-value {
        color: #f59e0b;
    }
    .score-circle-content.score-low .score-value {
        color: #ef4444;
    }
    .score-circle-content .score-label {
        font-size: 16px;
        color: #10b981;
        margin-top: 5px;
        font-weight: 500;
    }
    .score-circle-content.score-medium .score-label {
        color: #f59e0b;
    }
    .score-circle-content.score-low .score-label {
        color: #ef4444;
    }
    .stats-section {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 12px;
    }
    .stat-item {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .stat-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }
    .stat-icon.time {
        background: #e3f2fd;
        color: #1976d2;
    }
    .stat-icon.checkpoints {
        background: #e8f5e9;
        color: #388e3c;
    }
    .stat-icon.score {
        background: #fff3e0;
        color: #f57c00;
    }
    .stat-content .stat-label {
        font-size: 12px;
        color: #6c757d;
    }
    .stat-content .stat-value {
        font-size: 16px;
        font-weight: 600;
        color: #1a1a2e;
    }
    .audit-photo-img {
        max-width: 120px;
        max-height: 140px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e3e6ef;
    }
    .participants-section {
        border-left: 1px solid #e3e6ef;
        padding-left: 30px;
        min-width: 200px;
    }
    .participants-title {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
    }
    .participant-item {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 15px;
    }
    .participant-item img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    .participant-info .participant-name {
        font-size: 14px;
        font-weight: 500;
        color: #1a1a2e;
    }
    .participant-info .participant-role {
        font-size: 12px;
        color: #6c757d;
    }
    .checkpoint-results-card {
        background: #fff;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a2e;
        margin-bottom: 20px;
    }
    .checkpoint-table {
        width: 100%;
        border-collapse: collapse;
    }
    .checkpoint-table thead th {
        background: #f8f9fa;
        padding: 12px 15px;
        font-size: 13px;
        font-weight: 600;
        color: #6c757d;
        text-align: left;
        border-bottom: 1px solid #e3e6ef;
    }
    .checkpoint-table tbody td {
        padding: 15px;
        font-size: 14px;
        color: #333;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }
    .checkpoint-table tbody tr:last-child td {
        border-bottom: none;
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    .status-badge.completed {
        background: #d4edda;
        color: #155724;
    }
    .status-badge.partial {
        background: #fff3cd;
        color: #856404;
    }
    .status-badge.not-completed {
        background: #f8d7da;
        color: #721c24;
    }
    .evidence-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #007bff;
        font-size: 13px;
        text-decoration: none;
    }
    .evidence-link:hover {
        text-decoration: underline;
    }
    .action-items-card {
        background: #fff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .action-items-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 18px;
        font-weight: 600;
        color: #1a1a2e;
        margin-bottom: 20px;
    }
    .action-items-title i {
        color: #fd7e14;
    }
    .action-item {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        padding: 16px 20px;
        border-radius: 8px;
        margin-bottom: 12px;
        border-left: 4px solid;
    }
    .action-item:last-child {
        margin-bottom: 0;
    }
    .action-item.partial {
        background: #fffbeb;
        border-left-color: #f59e0b;
    }
    .action-item.not-completed {
        background: #fef2f2;
        border-left-color: #ef4444;
    }
    .action-item .action-icon {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 12px;
    }
    .action-item.partial .action-icon {
        background: #f59e0b;
        color: #fff;
    }
    .action-item.not-completed .action-icon {
        background: #ef4444;
        color: #fff;
    }
    .action-content .action-title {
        font-size: 15px;
        font-weight: 600;
        color: #1a1a2e;
        margin-bottom: 4px;
    }
    .action-content .action-description {
        font-size: 14px;
        color: #6c757d;
    }
    @media (max-width: 991px) {
        .summary-content {
            flex-direction: column;
        }
        .participants-section {
            border-left: none;
            border-top: 1px solid #e3e6ef;
            padding-left: 0;
            padding-top: 20px;
            margin-top: 20px;
        }
    }
</style>

<div class="audit-result-wrapper">
    <!-- Threshold Alert -->
    @if($audit->isBelowThreshold())
        <div class="alert alert-warning alert-dismissible fade show" role="alert" style="margin-bottom: 20px; border-left: 4px solid #f59e0b;">
            <div class="d-flex align-items-center">
                <i class="fa fa-exclamation-triangle mr-2" style="font-size: 20px;"></i>
                <div>
                    <strong>@lang('audit::app.thresholdAlert')</strong>
                    <p class="mb-0">@lang('audit::app.scoreThresholdWarning') {{ $audit->getThresholdAlertMessage() }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Summary Card -->
    <div class="audit-summary-card">
    <!-- Header -->
        <div class="audit-header">
            <div class="audit-title-section">
                <h2>{{ $audit->template->title }}</h2>
                @if($audit->completed_at)
                    <div class="completion-date">
                        @lang('audit::app.completedOn') {{ $audit->completed_at->translatedFormat(company()->date_format) }}
                    </div>
                @elseif($audit->started_at)
                    <div class="completion-date">
                        @lang('audit::app.startedOn') {{ $audit->started_at->translatedFormat(company()->date_format) }}
                    </div>
                @endif
            </div>
            <div class="audit-actions">
                <button type="button" class="btn btn-print" id="printAudit">
                    <i class="fa fa-print mr-1"></i> @lang('app.print')
                </button>
                <a href="{{ route('audits.export-pdf', $audit->id) }}" class="btn btn-download-pdf">
                    <i class="fa fa-download mr-1"></i> @lang('audit::app.downloadPdf')
                </a>
            </div>
        </div>

        <div class="summary-content">
            <!-- Score Circle -->
            <div class="score-circle-wrapper">
                @php
                    $score = $audit->score ?? 0;
                    $scoreClass = $score >= 80 ? 'score-high' : ($score >= 60 ? 'score-medium' : 'score-low');
                    $scoreLabel = $score >= 80 ? __('audit::app.pass') : ($score >= 60 ? __('audit::app.acceptable') : __('audit::app.fail'));
                    $totalSegments = 20;
                    $filledSegments = round(($score / 100) * $totalSegments);
                    $radius = 65;
                    $cx = 80;
                    $cy = 80;
                @endphp
                <svg class="score-circle-svg" viewBox="0 0 160 160">
                    @for($i = 0; $i < $totalSegments; $i++)
                        @php
                            $angle = ($i * 360 / $totalSegments) - 90;
                            $angleRad = deg2rad($angle);
                            $nextAngle = (($i + 1) * 360 / $totalSegments) - 90 - 5;
                            $nextAngleRad = deg2rad($nextAngle);

                            $x1 = $cx + $radius * cos($angleRad);
                            $y1 = $cy + $radius * sin($angleRad);
                            $x2 = $cx + $radius * cos($nextAngleRad);
                            $y2 = $cy + $radius * sin($nextAngleRad);

                            $isFilled = $i < $filledSegments;
                        @endphp
                        <path class="score-segment {{ $isFilled ? 'filled ' . $scoreClass : 'bg' }}"
                            d="M {{ $x1 }} {{ $y1 }} A {{ $radius }} {{ $radius }} 0 0 1 {{ $x2 }} {{ $y2 }}"
                        />
                    @endfor
                </svg>
                <div class="score-circle-content {{ $scoreClass }}">
                    <div class="score-value">{{ $score }}%</div>
                    <div class="score-label">{{ $scoreLabel }}</div>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-section">
                <div class="stat-item">
                    <div class="stat-icon time">
                        <i class="fa fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">@lang('audit::app.totalTimeTaken')</div>
                        <div class="stat-value">{{ $audit->duration_formatted ?? '--' }}</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon checkpoints">
                        <i class="fa fa-check-double"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">@lang('audit::app.checkpoints')</div>
                        <div class="stat-value">{{ $audit->completed_checkpoints ?? 0 }} / {{ $audit->total_checkpoints }} @lang('audit::app.completed')</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon score">
                        <i class="fa fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">@lang('audit::app.finalScore')</div>
                        <div class="stat-value">{{ $audit->score ?? 0 }} / 100</div>
                    </div>
                </div>
            </div>

            <!-- Audit Photo -->
            @if($audit->photo)
                <div class="audit-photo-section">
                    <div class="participants-title">Live @lang('audit::app.photo')</div>
                    <a href="{{ asset('storage/' . $audit->photo) }}" target="_blank" class="d-block">
                        <img src="{{ asset('storage/' . $audit->photo) }}" alt="@lang('audit::app.photo')" class="audit-photo-img">
                    </a>
                </div>
            @endif

            <!-- Participants -->
            <div class="participants-section">
                <div class="participants-title">@lang('audit::app.participants')</div>
                @if($audit->auditor)
                    <div class="participant-item">
                        <img src="{{ $audit->auditor->image_url }}" alt="{{ $audit->auditor->name }}">
                        <div class="participant-info">
                            <div class="participant-name">{{ $audit->auditor->name }}</div>
                            <div class="participant-role">@lang('audit::app.auditor')</div>
                        </div>
                    </div>
                @endif
                @if($audit->auditee)
                    <div class="participant-item">
                        <img src="{{ $audit->auditee->image_url }}" alt="{{ $audit->auditee->name }}">
                        <div class="participant-info">
                            <div class="participant-name">{{ $audit->auditee->name }}</div>
                            <div class="participant-role">@lang('audit::app.auditee') @if($audit->department)({{ $audit->department->team_name }})@endif</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Detailed Checkpoint Results -->
    <div class="checkpoint-results-card">
        <h3 class="section-title">@lang('audit::app.detailedCheckpointResults')</h3>
        <table class="checkpoint-table">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>@lang('audit::app.checkpointDescription')</th>
                    <th width="200">@lang('app.status')</th>
                    <th width="120">@lang('audit::app.evidence')</th>
                    <th>@lang('audit::app.notes')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($audit->responses as $index => $response)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $response->checkpoint->title }}</td>
                    <td>
                        @if($response->status == 'completed')
                            <span class="status-badge completed">@lang('audit::app.completed')</span>
                        @elseif($response->status == 'partially_completed')
                            <span class="status-badge partial">@lang('audit::app.partial') @lang('app.completed')</span>
                        @elseif($response->status == 'not_completed')
                            <span class="status-badge not-completed">@lang('audit::app.notCompleted')</span>
                        @else
                            <span class="text-muted">--</span>
                        @endif
                    </td>
                    <td>
                        @if($response->files->count() > 0)
                            @foreach($response->files as $file)
                                <a href="{{ $file->file_url }}" target="_blank" class="evidence-link">
                                    <i class="fa {{ $file->isImage() ? 'fa-image' : 'fa-file' }}"></i>
                                    @lang('audit::app.viewFile')
                                </a>
                                @if(!$loop->last)<br>@endif
                            @endforeach
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td>{{ $response->notes ?: '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Action Items Required -->
    @php
        $actionItems = $audit->responses->filter(function($response) {
            return in_array($response->status, ['partially_completed', 'not_completed']) && $response->notes;
        });
    @endphp
    @if($actionItems->count() > 0)
    <div class="action-items-card">
        <h3 class="action-items-title">
            <i class="fa fa-exclamation-triangle"></i>
            @lang('audit::app.actionItemsRequired')
        </h3>
        @foreach($actionItems as $item)
        <div class="action-item {{ $item->status == 'partially_completed' ? 'partial' : 'not-completed' }}">
            <div class="action-icon">
                <i class="fa {{ $item->status == 'partially_completed' ? 'fa-exclamation' : 'fa-times' }}"></i>
            </div>
            <div class="action-content">
                <div class="action-title">{{ $item->checkpoint->title }}</div>
                <div class="action-description">{{ $item->notes }}</div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Audit History -->
    @if($auditHistory && $auditHistory->count() > 0)
    <div class="audit-history-card" style="background: #fff; border-radius: 12px; padding: 25px; margin-top: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 class="section-title" style="margin: 0; font-size: 18px; font-weight: 600; color: #1a1a2e;">@lang('audit::app.auditHistory')</h3>
            <a href="{{ route('audits.index') }}" style="color: #007bff; text-decoration: none; font-size: 14px; font-weight: 500;">
                @lang('audit::app.viewAllAudits') <i class="fa fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="audit-history-list">
            @foreach($auditHistory as $historyAudit)
            @php
                $score = round($historyAudit->score ?? 0);
                $isPass = $score >= 60;
            @endphp
            <div class="audit-history-item" style="background: #fff; border: 1px solid #e3e6ef; border-radius: 8px; padding: 16px 20px; margin-bottom: 12px; display: flex; align-items: center; gap: 20px; transition: all 0.2s;">
                <div class="score-badge-history" style="width: 56px; height: 56px; background: {{ $isPass ? '#d1fae5' : '#fee2e2' }}; color: {{ $isPass ? '#10b981' : '#ef4444' }}; border-radius: %; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 15px; flex-shrink: 0;">
                    {{ $score }}%
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 15px; font-weight: 500; color: #1a1a2e; margin-bottom: 6px;">
                        {{ $historyAudit->completed_at ? $historyAudit->completed_at->translatedFormat('F j, Y') : '--' }}
                    </div>
                    <div style="font-size: 13px; color: #6c757d; line-height: 1.4;">
                        {{ $historyAudit->template->title ?? '--' }}
                    </div>
                </div>
                <div style="color: {{ $isPass ? '#10b981' : '#ef4444' }}; font-weight: 500; font-size: 14px; flex-shrink: 0;">
                    {{ $isPass ? __('audit::app.pass') : __('audit::app.fail') }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<script>
    $('#printAudit').on('click', function () {
        let iframe = document.createElement('iframe');
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';

        document.body.appendChild(iframe);

        iframe.onload = function () {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();

            iframe.contentWindow.onafterprint = function () {
                document.body.removeChild(iframe);
            };
        };

        iframe.src = "{{ route('audits.print', $audit->id) }}";
    });

</script>
