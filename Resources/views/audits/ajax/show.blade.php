<div class="row">
    <div class="col-sm-12">
        <div class="card bg-white border-0 b-shadow-4">
            <div class="card-header bg-white border-bottom-grey text-capitalize justify-content-between p-20">
                <div class="row">
                    <div class="col-md-8">
                        <h3 class="heading-h1 mb-0">{{ $audit->template->title }}</h3>
                    </div>
                    <div class="col-md-4 text-right">
                        @php
                            $statusClass = [
                                'in_progress' => 'badge-warning',
                                'completed' => 'badge-success',
                                'cancelled' => 'badge-danger',
                            ][$audit->status] ?? 'badge-secondary';
                        @endphp
                        <span class="badge {{ $statusClass }} f-14">{{ ucwords(str_replace('_', ' ', $audit->status)) }}</span>
                        @if($audit->status == 'completed')
                            <span class="badge {{ $audit->score >= 80 ? 'badge-success' : ($audit->score >= 60 ? 'badge-warning' : 'badge-danger') }} f-14 ml-2">
                                {{ $audit->score }}%
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Audit Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <x-cards.data-row :label="__('audit::app.department')" :value="$audit->department ? $audit->department->team_name : '--'" />
                    </div>
                    <div class="col-md-6">
                        <x-cards.data-row :label="__('audit::app.location')" :value="$audit->location ?: '--'" />
                    </div>
                    <div class="col-md-6">
                        <div class="card-text f-14 text-dark-grey mb-3">
                            <span class="font-weight-bold">@lang('audit::app.auditor'):</span>
                            @if($audit->auditor)
                                <div class="d-inline-flex align-items-center ml-2">
                                    <img src="{{ $audit->auditor->image_url }}" class="rounded-circle" width="30" height="30">
                                    <span class="ml-2">{{ $audit->auditor->name }}</span>
                                </div>
                            @else
                                --
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card-text f-14 text-dark-grey mb-3">
                            <span class="font-weight-bold">@lang('audit::app.auditee'):</span>
                            @if($audit->auditee)
                                <div class="d-inline-flex align-items-center ml-2">
                                    <img src="{{ $audit->auditee->image_url }}" class="rounded-circle" width="30" height="30">
                                    <span class="ml-2">{{ $audit->auditee->name }}</span>
                                </div>
                            @else
                                --
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <x-cards.data-row :label="__('audit::app.startedAt')" :value="$audit->started_at ? $audit->started_at->translatedFormat(company()->date_format . ' ' . company()->time_format) : '--'" />
                    </div>
                    <div class="col-md-6">
                        <x-cards.data-row :label="__('audit::app.completedAt')" :value="$audit->completed_at ? $audit->completed_at->translatedFormat(company()->date_format . ' ' . company()->time_format) : '--'" />
                    </div>
                    <div class="col-md-6">
                        <x-cards.data-row :label="__('audit::app.duration')" :value="$audit->duration_formatted" />
                    </div>
                </div>

                <!-- Score Summary (if completed) -->
                @if($audit->status == 'completed')
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card border">
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <h2 class="{{ $audit->score_color }}">{{ $audit->score }}%</h2>
                                        <p class="text-muted mb-0">@lang('audit::app.overallScore')</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h2 class="text-success">{{ $audit->completed_checkpoints }}</h2>
                                        <p class="text-muted mb-0">@lang('audit::app.completed')</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h2 class="text-warning">{{ $audit->partially_completed_checkpoints }}</h2>
                                        <p class="text-muted mb-0">@lang('audit::app.partiallyCompleted')</p>
                                    </div>
                                    <div class="col-md-3">
                                        <h2 class="text-danger">{{ $audit->total_checkpoints - $audit->completed_checkpoints - $audit->partially_completed_checkpoints }}</h2>
                                        <p class="text-muted mb-0">@lang('audit::app.notCompleted')</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Checkpoint Responses -->
                <div class="row">
                    <div class="col-md-12">
                        <h5 class="f-18 font-weight-bold mb-3">
                            <i class="fa fa-list-check mr-2"></i>@lang('audit::app.checkpointResponses')
                        </h5>
                    </div>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>@lang('audit::app.checkpoint')</th>
                                        <th width="150">@lang('app.status')</th>
                                        <th>@lang('audit::app.notes')</th>
                                        <th width="150">@lang('audit::app.files')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($audit->responses as $index => $response)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $response->checkpoint->title }}</strong>
                                            @if($response->checkpoint->description)
                                                <br><small class="text-muted">{{ $response->checkpoint->description }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $statusInfo = \Modules\Audit\Entities\AuditCheckpointResponse::STATUSES[$response->status];
                                            @endphp
                                            <span class="badge badge-{{ $statusInfo['color'] }}">
                                                <i class="fa fa-{{ $statusInfo['icon'] }} mr-1"></i>
                                                {{ $statusInfo['label'] }}
                                            </span>
                                        </td>
                                        <td>{{ $response->notes ?: '--' }}</td>
                                        <td>
                                            @if($response->files->count() > 0)
                                                @foreach($response->files as $file)
                                                    <a href="{{ $file->file_url }}" target="_blank" class="btn btn-sm btn-outline-secondary mb-1" title="{{ $file->filename }}">
                                                        <i class="fa {{ $file->icon }}"></i>
                                                    </a>
                                                @endforeach
                                            @else
                                                --
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                @if($audit->summary)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5 class="f-18 font-weight-bold mb-3">@lang('audit::app.summary')</h5>
                        <p>{{ $audit->summary }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

