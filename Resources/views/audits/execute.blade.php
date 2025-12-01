@extends('layouts.app')

@push('styles')
<style>
    .audit-execute-wrapper {
        display: flex;
        gap: 20px;
        min-height: calc(100vh - 150px);
    }
    .audit-main-content {
        flex: 1;
        background: #fff;
        border-radius: 8px;
        padding: 25px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .audit-sidebar {
        width: 320px;
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        height: fit-content;
        position: sticky;
        top: 80px;
    }
    .step-indicator {
        font-size: 13px;
        color: #6c757d;
        margin-bottom: 8px;
    }
    .checkpoint-title {
        font-size: 20px;
        font-weight: 600;
        color: #1a1a2e;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e3e6ef;
    }
    .status-section-title {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        margin-bottom: 12px;
    }
    .status-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
    }
    .status-btn {
        flex: 1;
        padding: 12px 16px;
        border-radius: 8px;
        border: 2px solid #e3e6ef;
        background: #fff;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-weight: 500;
        font-size: 14px;
    }
    .status-btn:hover {
        border-color: #99a5b5;
    }
    .status-btn.completed {
        border-color: #28a745;
        background: #28a745;
        color: #fff;
    }
    .status-btn.partial {
        border-color: #fd7e14;
        background: #fd7e14;
        color: #fff;
    }
    .status-btn.not-completed {
        border-color: #6c757d;
        background: #6c757d;
        color: #fff;
    }
    .status-btn.active {
        transform: scale(1.02);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    .upload-section {
        margin-bottom: 25px;
    }
    .upload-title {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        margin-bottom: 12px;
    }
    .upload-title .required {
        color: #dc3545;
        font-weight: 400;
    }
    .dropzone-area {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 30px;
        text-align: center;
        background: #fafbfc;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .dropzone-area:hover {
        border-color: #99a5b5;
        background: #f5f6f8;
    }
    .dropzone-area.dragover {
        border-color: #007bff;
        background: #e7f1ff;
    }
    .dropzone-area.uploading {
        pointer-events: none;
        opacity: 0.7;
    }
    .dropzone-area.uploading .dropzone-icon i {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .dropzone-icon {
        font-size: 32px;
        color: #99a5b5;
        margin-bottom: 10px;
    }
    .dropzone-text {
        color: #6c757d;
        font-size: 14px;
    }
    .dropzone-hint {
        color: #adb5bd;
        font-size: 12px;
        margin-top: 5px;
    }
    .uploaded-files {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 15px;
    }
    .uploaded-file {
        position: relative;
        width: 100px;
        height: 100px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .uploaded-file img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .uploaded-file .file-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0,0,0,0.6);
        color: #fff;
        padding: 4px 6px;
        font-size: 10px;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
    }
    .uploaded-file .delete-btn {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: rgba(220, 53, 69, 0.9);
        color: #fff;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
    }
    .comments-section {
        margin-bottom: 25px;
    }
    .comments-title {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        margin-bottom: 12px;
    }
    .comments-textarea {
        width: 100%;
        border: 1px solid #e3e6ef;
        border-radius: 8px;
        padding: 12px;
        font-size: 14px;
        resize: vertical;
        min-height: 80px;
    }
    .comments-textarea:focus {
        outline: none;
        border-color: #007bff;
    }
    .navigation-buttons {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 20px;
        border-top: 1px solid #e3e6ef;
    }
    .nav-btn {
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .nav-btn.prev {
        background: #fff;
        border: 1px solid #e3e6ef;
        color: #333;
    }
    .nav-btn.prev:hover {
        background: #f8f9fa;
    }
    .nav-btn.next {
        background: #dc3545;
        border: none;
        color: #fff;
        margin-left: auto;
    }
    .nav-btn.next:hover {
        background: #c82333;
    }
    .nav-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Sidebar Styles */
    .sidebar-title {
        font-size: 16px;
        font-weight: 600;
        color: #1a1a2e;
        margin-bottom: 15px;
    }
    .sidebar-info {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e3e6ef;
    }
    .sidebar-info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 13px;
    }
    .sidebar-info-label {
        color: #6c757d;
    }
    .sidebar-info-value {
        color: #1a1a2e;
        font-weight: 500;
    }
    .timer-section {
        text-align: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e3e6ef;
    }
    .timer-label {
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 5px;
    }
    .timer-display {
        font-size: 32px;
        font-weight: 700;
        font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
        color: #007bff;
    }
    .progress-section {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e3e6ef;
    }
    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    .progress-label {
        font-size: 13px;
        color: #6c757d;
    }
    .progress-count {
        font-size: 13px;
        font-weight: 600;
        color: #dc3545;
    }
    .progress-count.completed {
        color: #28a745;
    }
    .progress-bar-wrapper {
        height: 6px;
        background: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
    }
    .progress-bar-fill {
        height: 100%;
        background: #dc3545;
        border-radius: 3px;
        transition: width 0.3s ease, background 0.3s ease;
    }
    .progress-bar-fill.completed {
        background: #28a745;
    }
    .checkpoints-list-title {
        font-size: 13px;
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
    }
    .checkpoints-list {
        max-height: 250px;
        overflow-y: auto;
    }
    .checkpoint-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 8px;
        margin-bottom: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 13px;
        border-left: 4px solid transparent;
    }
    .checkpoint-item:hover {
        background: #f8f9fa;
    }
    .checkpoint-item.active {
        background: #e7f1ff;
        border-left-color: #007bff;
    }
    .checkpoint-item.status-completed {
        background: #d4edda;
        border-left-color: #28a745;
    }
    .checkpoint-item.status-partial {
        background: #fff3cd;
        border-left-color: #ffc107;
    }
    .checkpoint-item.status-not-completed {
        background: #f8f9fa;
        border-left-color: #6c757d;
    }
    .checkpoint-item.status-pending {
        background: #fff;
        border-left-color: transparent;
    }
    .checkpoint-item .status-icon {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        flex-shrink: 0;
    }
    .checkpoint-item .status-icon.completed {
        background: #28a745;
        color: #fff;
    }
    .checkpoint-item .status-icon.partial {
        background: #ffc107;
        color: #fff;
    }
    .checkpoint-item .status-icon.not-completed {
        background: #6c757d;
        color: #fff;
    }
    .checkpoint-item .status-icon.pending {
        background: #fff;
        color: #adb5bd;
        border: 2px solid #dee2e6;
    }
    .checkpoint-item .status-icon.current {
        background: #007bff;
        color: #fff;
    }
    .checkpoint-item .checkpoint-text {
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #333;
    }
    .checkpoint-item.active .checkpoint-text {
        color: #007bff;
        font-weight: 600;
    }
    .sidebar-actions {
        margin-top: 20px;
    }
    .sidebar-actions .btn {
        width: 100%;
        margin-bottom: 10px;
        padding: 12px;
        font-weight: 500;
    }
    .btn-submit-audit {
        background: #dc3545;
        border: none;
        color: #fff;
    }
    .btn-submit-audit:hover {
        background: #c82333;
        color: #fff;
    }
    .btn-save-exit {
        background: #fff;
        border: 1px solid #e3e6ef;
        color: #333;
    }
    .btn-save-exit:hover {
        background: #f8f9fa;
    }

    @media (max-width: 991px) {
        .audit-execute-wrapper {
            flex-direction: column;
        }
        .audit-sidebar {
            width: 100%;
            position: relative;
            top: 0;
        }
    }
</style>
@endpush

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <div class="audit-execute-wrapper">
            <!-- Main Content -->
            <div class="audit-main-content">
                <form id="checkpoint-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="current_index" id="current_index" value="0">

                    @foreach($audit->responses as $index => $response)
                    <div class="checkpoint-step" data-index="{{ $index }}" data-response-id="{{ $response->id }}" style="{{ $index > 0 ? 'display: none;' : '' }}">
                        <div class="step-indicator">@lang('audit::app.step') {{ $index + 1 }} @lang('audit::app.of') {{ $audit->responses->count() }}</div>
                        <h2 class="checkpoint-title">
                            {{ $response->checkpoint->title }}
                        </h2>

                        <!-- Completion Status -->
                        <div class="status-section-title">@lang('audit::app.completionStatus')</div>
                        <div class="status-buttons">
                            <button type="button" class="status-btn {{ $response->status == 'completed' ? 'completed active' : '' }}" data-status="completed" data-response-id="{{ $response->id }}">
                                <i class="fa fa-check-circle"></i> @lang('audit::app.completed')
                            </button>
                            <button type="button" class="status-btn {{ $response->status == 'partially_completed' ? 'partial active' : '' }}" data-status="partially_completed" data-response-id="{{ $response->id }}">
                                <i class="fa fa-adjust"></i> @lang('audit::app.partial')
                            </button>
                            <button type="button" class="status-btn {{ $response->status == 'not_completed' ? 'not-completed active' : '' }}" data-status="not_completed" data-response-id="{{ $response->id }}">
                                <i class="fa fa-times-circle"></i> @lang('audit::app.notCompleted')
                            </button>
                        </div>
                        <input type="hidden" name="status_{{ $response->id }}" id="status_{{ $response->id }}" value="{{ $response->status ?? '' }}">

                        @if($response->checkpoint->description)
                        <p class="text-muted mb-4">{{ $response->checkpoint->description }}</p>
                        @endif

                        <!-- Upload Evidence -->
                        @if($response->checkpoint->requires_file_upload || $response->checkpoint->requires_photo)
                        <div class="upload-section">
                            <div class="upload-title">
                                @lang('audit::app.uploadEvidence')
                                @if($response->checkpoint->requires_photo)
                                    <span class="required">(@lang('audit::app.photoRequired'))</span>
                                @endif
                            </div>
                            <div class="dropzone-area" id="dropzone-{{ $response->id }}" data-response-id="{{ $response->id }}">
                                <div class="dropzone-icon"><i class="fa fa-cloud-upload-alt"></i></div>
                                <div class="dropzone-text">@lang('audit::app.dragDropText')</div>
                                <div class="dropzone-hint">@lang('audit::app.maxFileSize')</div>
                            </div>
                            <input type="file" name="files_{{ $response->id }}[]" id="file-input-{{ $response->id }}" multiple @if($response->checkpoint->requires_photo) accept="image/*" @endif style="display: none;">
                            <div class="uploaded-files" id="uploaded-files-{{ $response->id }}">
                                @foreach($response->files as $file)
                                <div class="uploaded-file" id="file-{{ $file->id }}">
                                    @if($file->isImage())
                                        <img src="{{ $file->file_url }}" alt="{{ $file->filename }}">
                                    @else
                                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                            <i class="fa {{ $file->icon }} fa-2x text-secondary"></i>
                                        </div>
                                    @endif
                                    <div class="file-overlay">{{ $file->filename }}</div>
                                    <button type="button" class="delete-btn" data-file-id="{{ $file->id }}" data-audit-id="{{ $audit->id }}">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Optional Comments -->
                        <div class="comments-section">
                            <div class="comments-title">
                                @lang('audit::app.optionalComments')
                                @if($response->checkpoint->requires_notes)
                                    <span class="text-danger">*</span>
                                @endif
                            </div>
                            <textarea class="comments-textarea" name="notes_{{ $response->id }}" id="notes_{{ $response->id }}" placeholder="@lang('audit::app.addNotesPlaceholder')">{{ $response->notes }}</textarea>
                        </div>
                    </div>
                    @endforeach

                    <!-- Navigation Buttons -->
                    <div class="navigation-buttons">
                        <button type="button" class="nav-btn prev" id="prev-step" style="display: none;">
                            <i class="fa fa-arrow-left"></i> @lang('audit::app.previousStep')
                        </button>
                        <button type="button" class="nav-btn next" id="next-step">
                            @lang('audit::app.nextStep') <i class="fa fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="audit-sidebar">
                <div class="sidebar-title">{{ $audit->template->title }}</div>

                <div class="sidebar-info">
                    <div class="sidebar-info-row">
                        <span class="sidebar-info-label">@lang('app.department'):</span>
                        <span class="sidebar-info-value">{{ $audit->department ? $audit->department->team_name : '--' }}</span>
                    </div>
                    <div class="sidebar-info-row">
                        <span class="sidebar-info-label">@lang('audit::app.auditee'):</span>
                        <span class="sidebar-info-value">{{ $audit->auditee ? $audit->auditee->name : '--' }}</span>
                    </div>
                    <div class="sidebar-info-row">
                        <span class="sidebar-info-label">@lang('audit::app.auditor'):</span>
                        <span class="sidebar-info-value">{{ $audit->auditor ? $audit->auditor->name : '--' }}</span>
                    </div>
                </div>

                <div class="timer-section">
                    <div class="timer-label">@lang('audit::app.timeElapsed')</div>
                    <div class="timer-display" id="timer">00:00:00</div>
                </div>

                <div class="progress-section">
                    <div class="progress-header">
                        <span class="progress-label">@lang('audit::app.progress')</span>
                        @php
                            $completedCount = $audit->responses->whereNotNull('responded_at')->count();
                            $totalCount = $audit->responses->count();
                            $isCompleted = $completedCount == $totalCount && $totalCount > 0;
                        @endphp
                        <span class="progress-count {{ $isCompleted ? 'completed' : '' }}" id="progress-count-wrapper">
                            <span id="progress-count">{{ $completedCount }}</span> / {{ $totalCount }} @lang('audit::app.completed')
                        </span>
                    </div>
                    <div class="progress-bar-wrapper">
                        <div class="progress-bar-fill {{ $isCompleted ? 'completed' : '' }}" id="progress-bar" style="width: {{ $totalCount > 0 ? ($completedCount / $totalCount * 100) : 0 }}%"></div>
                    </div>
                </div>

                <div class="checkpoints-list-title">@lang('audit::app.checkpoints')</div>
                <div class="checkpoints-list">
                    @foreach($audit->responses as $index => $response)
                    @php
                        $itemStatusClass = 'status-pending';
                        if ($index == 0) {
                            $itemStatusClass = 'active';
                        } elseif ($response->responded_at) {
                            if ($response->status == 'completed') {
                                $itemStatusClass = 'status-completed';
                            } elseif ($response->status == 'partially_completed') {
                                $itemStatusClass = 'status-partial';
                            } elseif ($response->status == 'not_completed') {
                                $itemStatusClass = 'status-not-completed';
                            }
                        }
                    @endphp
                    <div class="checkpoint-item {{ $itemStatusClass }}" data-index="{{ $index }}" data-response-id="{{ $response->id }}">
                        <span class="status-icon {{ $index == 0 ? 'current' : ($response->responded_at ? ($response->status == 'completed' ? 'completed' : ($response->status == 'partially_completed' ? 'partial' : 'not-completed')) : 'pending') }}" id="sidebar-icon-{{ $response->id }}">
                            @if($index == 0)
                                <i class="fa fa-arrow-right"></i>
                            @elseif($response->responded_at && $response->status == 'completed')
                                <i class="fa fa-check"></i>
                            @elseif($response->responded_at && $response->status == 'partially_completed')
                                <i class="fa fa-exclamation"></i>
                            @elseif($response->responded_at && $response->status == 'not_completed')
                                <i class="fa fa-times"></i>
                            @endif
                        </span>
                        <span class="checkpoint-text" title="{{ $response->checkpoint->title }}">{{ $index + 1 }}. {{ $response->checkpoint->title }}</span>
                    </div>
                    @endforeach
                </div>

                <div class="sidebar-actions">
                    <button type="button" class="btn btn-submit-audit" id="submit-audit">
                        @lang('audit::app.submitAudit')
                    </button>
                    <button type="button" class="btn btn-save-exit" id="save-exit">
                        @lang('audit::app.saveAndExit')
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentIndex = 0;
    const totalSteps = {{ $audit->responses->count() }};
    const responses = @json($audit->responses->pluck('id'));

    // Timer
    const startTime = new Date('{{ $audit->started_at->toISOString() }}');

    function updateTimer() {
        const now = new Date();
        const diff = Math.floor((now - startTime) / 1000);
        const hours = Math.floor(diff / 3600);
        const minutes = Math.floor((diff % 3600) / 60);
        const seconds = diff % 60;

        $('#timer').text(
            String(hours).padStart(2, '0') + ':' +
            String(minutes).padStart(2, '0') + ':' +
            String(seconds).padStart(2, '0')
        );
    }
    setInterval(updateTimer, 1000);
    updateTimer();

    // Status button click
    $(document).on('click', '.status-btn', function() {
        const $this = $(this);
        const status = $this.data('status');
        const responseId = $this.data('response-id');

        // Update button states
        $this.closest('.status-buttons').find('.status-btn').removeClass('completed partial not-completed active');
        $this.addClass(status === 'completed' ? 'completed' : (status === 'partially_completed' ? 'partial' : 'not-completed')).addClass('active');

        // Update hidden input
        $('#status_' + responseId).val(status);

        // Auto-save
        saveCheckpoint(responseId);
    });

    // Dropzone click
    $(document).on('click', '.dropzone-area', function(e) {
        e.preventDefault();
        const $dropzone = $(this);
        // Prevent clicking while uploading
        if ($dropzone.hasClass('uploading')) {
            return;
        }
        const responseId = $dropzone.data('response-id');
        $('#file-input-' + responseId).trigger('click');
    });

    // File input change
    $(document).on('change', 'input[type="file"]', function() {
        const responseId = $(this).attr('id').replace('file-input-', '');
        console.log('File input changed for response:', responseId);
        console.log('Files selected:', this.files.length);
        if (this.files.length > 0) {
            saveCheckpoint(responseId);
        }
    });

    // Drag and drop
    $(document).on('dragover', '.dropzone-area', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });

    $(document).on('dragleave', '.dropzone-area', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });

    $(document).on('drop', '.dropzone-area', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
        const responseId = $(this).data('response-id');
        const files = e.originalEvent.dataTransfer.files;
        console.log('Files dropped:', files.length);
        if (files.length > 0) {
            const input = $('#file-input-' + responseId)[0];
            // Create a DataTransfer object to set files
            const dataTransfer = new DataTransfer();
            for (let i = 0; i < files.length; i++) {
                dataTransfer.items.add(files[i]);
            }
            input.files = dataTransfer.files;
            saveCheckpoint(responseId);
        }
    });

    // Delete file
    $(document).on('click', '.delete-btn', function(e) {
        e.stopPropagation();
        const fileId = $(this).data('file-id');
        const auditId = $(this).data('audit-id');
        const $fileEl = $(this).closest('.uploaded-file');

        const url = "{{ route('audits.delete-file', [':audit', ':file']) }}"
            .replace(':audit', auditId)
            .replace(':file', fileId);

        $.ajax({
            url: url,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.status == 'success') {
                    $fileEl.fadeOut(300, function() { $(this).remove(); });
                }
            }
        });
    });

    // Save checkpoint
    function saveCheckpoint(responseId) {
        console.log('saveCheckpoint called for response:', responseId);

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('status', $('#status_' + responseId).val());
        formData.append('notes', $('#notes_' + responseId).val());

        const fileInput = $('#file-input-' + responseId)[0];
        const $dropzone = $('#dropzone-' + responseId);
        const hasFiles = fileInput && fileInput.files.length > 0;

        console.log('File input found:', !!fileInput);
        console.log('Has files:', hasFiles);

        if (hasFiles) {
            console.log('Appending', fileInput.files.length, 'files to formData');
            for (let i = 0; i < fileInput.files.length; i++) {
                console.log('File', i + 1, ':', fileInput.files[i].name, fileInput.files[i].size, 'bytes');
                formData.append('files[]', fileInput.files[i]);
            }
            // Show uploading state
            $dropzone.addClass('uploading');
            $dropzone.find('.dropzone-text').text('@lang("audit::app.uploading")...');
        }

        const url = "{{ route('audits.update-checkpoint', [$audit->id, ':response']) }}".replace(':response', responseId);
        console.log('Upload URL:', url);

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Upload response:', response);
                // Reset dropzone state
                $dropzone.removeClass('uploading');
                $dropzone.find('.dropzone-text').text('@lang("audit::app.dragDropText")');

                if (response.status == 'success') {
                    updateSidebarIcon(responseId, $('#status_' + responseId).val());
                    updateProgress(response.responded, response.total);

                    // Clear file input after upload
                    if (fileInput) {
                        fileInput.value = '';
                    }

                    // Reload uploaded files if new files were added
                    if (response.files && response.files.length > 0) {
                        const $container = $('#uploaded-files-' + responseId);
                        response.files.forEach(function(file) {
                            if ($('#file-' + file.id).length === 0) {
                                let fileHtml = `
                                    <div class="uploaded-file" id="file-${file.id}">
                                        ${file.is_image ? `<img src="${file.url}" alt="${file.filename}">` : `<div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f8f9fa;"><i class="fa ${file.icon} fa-2x text-secondary"></i></div>`}
                                        <div class="file-overlay">${file.filename}</div>
                                        <button type="button" class="delete-btn" data-file-id="${file.id}" data-audit-id="{{ $audit->id }}">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                `;
                                $container.append(fileHtml);
                            }
                        });
                    }
                }
            },
            error: function(xhr, status, error) {
                // Reset dropzone state
                $dropzone.removeClass('uploading');
                $dropzone.find('.dropzone-text').text('@lang("audit::app.dragDropText")');

                // Clear file input on error
                if (fileInput) {
                    fileInput.value = '';
                }

                console.error('Upload error:', error);
                console.error('Response:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: '@lang("audit::app.uploadError")',
                    text: xhr.responseJSON ? xhr.responseJSON.message : '@lang("audit::app.failedToSave")',
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false
                });
            }
        });
    }

    // Update sidebar icon and item background
    function updateSidebarIcon(responseId, status) {
        const $icon = $('#sidebar-icon-' + responseId);
        const $item = $icon.closest('.checkpoint-item');

        // Update icon
        $icon.removeClass('completed partial not-completed pending current');

        if (status === 'completed') {
            $icon.addClass('completed').html('<i class="fa fa-check"></i>');
        } else if (status === 'partially_completed') {
            $icon.addClass('partial').html('<i class="fa fa-exclamation"></i>');
        } else if (status === 'not_completed') {
            $icon.addClass('not-completed').html('<i class="fa fa-times"></i>');
        } else {
            $icon.addClass('pending').html('');
        }

        // Update item background
        $item.removeClass('status-completed status-partial status-not-completed status-pending active');

        if (status === 'completed') {
            $item.addClass('status-completed');
        } else if (status === 'partially_completed') {
            $item.addClass('status-partial');
        } else if (status === 'not_completed') {
            $item.addClass('status-not-completed');
        } else {
            $item.addClass('status-pending');
        }
    }

    // Update progress
    function updateProgress(responded, total) {
        $('#progress-count').text(responded);
        const percentage = Math.round((responded / total) * 100);
        $('#progress-bar').css('width', percentage + '%');

        // Update color based on completion
        const isCompleted = responded === total && total > 0;
        if (isCompleted) {
            $('#progress-count-wrapper').addClass('completed');
            $('#progress-bar').addClass('completed');
        } else {
            $('#progress-count-wrapper').removeClass('completed');
            $('#progress-bar').removeClass('completed');
        }
    }

    // Navigation
    function showStep(index) {
        $('.checkpoint-step').hide();
        $('.checkpoint-step[data-index="' + index + '"]').show();

        // Update previous active item - restore its status color
        $('.checkpoint-item .status-icon.current').each(function() {
            const respId = $(this).closest('.checkpoint-item').data('response-id');
            const status = $('#status_' + respId).val();
            $(this).removeClass('current');
            updateSidebarIcon(respId, status);
        });

        // Update sidebar active state - remove active from all, add to current
        $('.checkpoint-item').removeClass('active');
        const $currentItem = $('.checkpoint-item[data-index="' + index + '"]');
        $currentItem.removeClass('status-completed status-partial status-not-completed status-pending').addClass('active');

        // Update current icon to show arrow
        const currentResponseId = responses[index];
        const $currentIcon = $('#sidebar-icon-' + currentResponseId);
        $currentIcon.removeClass('completed partial not-completed pending').addClass('current').html('<i class="fa fa-arrow-right"></i>');

        // Update button states
        if (index === 0) {
            $('#prev-step').hide();
        } else {
            $('#prev-step').show();
        }

        if (index === totalSteps - 1) {
            $('#next-step').hide();
        } else {
            $('#next-step').show().html('@lang("audit::app.nextStep") <i class="fa fa-arrow-right"></i>');
        }

        currentIndex = index;

        // Scroll sidebar item into view
        const $activeItem = $('.checkpoint-item[data-index="' + index + '"]');
        if ($activeItem.length) {
            $activeItem[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    // Previous step
    $('#prev-step').on('click', function() {
        if (currentIndex > 0) {
            // Save current before moving
            const currentResponseId = responses[currentIndex];
            saveCheckpoint(currentResponseId);
            showStep(currentIndex - 1);
        }
    });

    // Next step
    $('#next-step').on('click', function() {
        const currentResponseId = responses[currentIndex];
        saveCheckpoint(currentResponseId);

        if (currentIndex < totalSteps - 1) {
            showStep(currentIndex + 1);
        }
    });

    // Click on sidebar checkpoint
    $(document).on('click', '.checkpoint-item', function() {
        const index = $(this).data('index');
        // Save current before moving
        const currentResponseId = responses[currentIndex];
        saveCheckpoint(currentResponseId);
        showStep(index);
    });

    // Submit audit
    $('#submit-audit').on('click', function() {
        // Save current checkpoint first
        const currentResponseId = responses[currentIndex];
        saveCheckpoint(currentResponseId);

        Swal.fire({
            title: "@lang('audit::app.completeAuditConfirm')",
            text: "@lang('audit::app.completeAuditConfirmText')",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: "@lang('audit::app.yesComplete')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-success mr-3',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('audits.complete', $audit->id) }}",
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.status == 'success') {
                            window.location.href = response.redirectUrl;
                        } else {
                            Swal.fire({
                                icon: 'error',
                                text: response.message,
                                customClass: { confirmButton: 'btn btn-primary' },
                                buttonsStyling: false
                            });
                        }
                    }
                });
            }
        });
    });

    // Save and Exit
    $('#save-exit').on('click', function() {
        // Save current checkpoint first
        const currentResponseId = responses[currentIndex];
        saveCheckpoint(currentResponseId);

        setTimeout(function() {
            window.location.href = "{{ route('audits.index') }}";
        }, 500);
    });
});
</script>
@endpush
