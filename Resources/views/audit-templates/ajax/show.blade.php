<div class="row">
    <div class="col-sm-8">
        <div class="card bg-white border-0 b-shadow-4">
            <div class="card-header bg-white border-bottom-grey text-capitalize justify-content-between p-20">
                <div class="row">
                    <div class="col-md-10">
                        <h3 class="heading-h1 mb-0">{{ $template->title }}</h3>
                    </div>
                    <div class="col-md-2 text-right">
                        @php
                            $statusClass = $template->status == 'active' ? 'badge-success' : 'badge-secondary';
                        @endphp
                        <span class="badge {{ $statusClass }} f-14">{{ ucfirst($template->status) }}</span>
                    </div>
                </div>
            </div>
            <div class="card-body">

                <!-- Checkpoints -->
                <div class="row">
                    <div class="col-md-12">
                        <h5 class="f-18 font-weight-bold mb-3">
                            <i class="fa fa-list-check mr-2"></i>@lang('audit::app.checkpoints') ({{ $template->checkpoints->count() }})
                        </h5>
                    </div>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>@lang('audit::app.checkpointTitle')</th>
                                        <th>@lang('app.description')</th>
                                        <th>@lang('audit::app.requirements')</th>
                                        <th width="100">@lang('audit::app.mandatory')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($template->checkpoints as $index => $checkpoint)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $checkpoint->title }}</td>
                                        <td>{{ $checkpoint->description ?: '--' }}</td>
                                        <td>
                                            @if($checkpoint->requires_file_upload)
                                                <span class="badge badge-info mr-1"><i class="fa fa-file mr-1"></i>@lang('audit::app.file')</span>
                                            @endif
                                            @if($checkpoint->requires_photo)
                                                <span class="badge badge-info mr-1"><i class="fa fa-camera mr-1"></i>@lang('audit::app.photo')</span>
                                            @endif
                                            @if($checkpoint->requires_notes)
                                                <span class="badge badge-info"><i class="fa fa-sticky-note mr-1"></i>@lang('audit::app.notes')</span>
                                            @endif
                                            @if(!$checkpoint->requires_file_upload && !$checkpoint->requires_photo && !$checkpoint->requires_notes)
                                                <span class="text-muted">@lang('app.none')</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($checkpoint->is_mandatory)
                                                <span class="text-success"><i class="fa fa-check-circle"></i></span>
                                            @else
                                                <span class="text-secondary"><i class="fa fa-minus-circle"></i></span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card bg-white border-0 b-shadow-4">
            <div class="card-header bg-white border-bottom-grey text-capitalize justify-content-between p-20">
                <h3 class="heading-h1 mb-0">@lang('audit::app.templateInfo')</h3>
            </div>
            <div class="card-body">
                <!-- Template Info -->
                <div class="row mb-4 p-20">
                    <x-cards.data-row :label="__('audit::app.department')" :value="$template->department ? $template->department->team_name : '--'" />
                    <x-cards.data-row :label="__('app.createdBy')" :value="$template->addedByUser ? $template->addedByUser->name : '--'" />
                    @if($template->description)
                        <x-cards.data-row :label="__('app.description')" :value="$template->description" />
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

