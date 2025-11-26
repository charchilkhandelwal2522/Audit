@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <!-- DEPARTMENT START -->
        <div class="select-box d-flex py-2 pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('audit::app.department')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="department_id" id="department_id" data-live-search="true" data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->team_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- DEPARTMENT END -->

        <!-- STATUS START -->
        <div class="select-box d-flex py-2 px-lg-3 px-md-3 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.status')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="status" id="filter_status">
                    <option value="all">@lang('app.all')</option>
                    <option value="active">@lang('app.active')</option>
                    <option value="inactive">@lang('app.inactive')</option>
                </select>
            </div>
        </div>
        <!-- STATUS END -->

        <!-- SEARCH START -->
        <div class="task-search d-flex py-1 px-lg-3 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field" placeholder="@lang('app.startTyping')">
                </div>
            </form>
        </div>
        <!-- SEARCH END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-2 px-lg-3 px-md-3 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->
    </x-filters.filter-box>
@endsection

@php
    $addPermission = user()->permission('add_audit_template');
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">

        <div class="row row-cols-lg-4 my-3">
            <div class="col mb-4">
                <x-cards.widget :title="__('audit::app.totalTemplates')"
                    value="{{ $totalTemplates }}" icon="list" widgetId="totalTemplates"/>
            </div>

            <div class="col mb-4">
                <x-cards.widget :title="__('audit::app.activeTemplates')"
                    value="{{ $activeTemplates }}" icon="check-circle" widgetId="activeTemplates"/>
            </div>

            <div class="col mb-4">
                <x-cards.widget :title="__('audit::app.departmentCount')"
                    value="{{ $departmentCount }}" icon="building" widgetId="departmentCount"/>
            </div>

            <div class="col mb-4">
                <x-cards.widget :title="__('audit::app.averageCheckpoints')"
                    value="{{ $averageCheckpoints }}" icon="clipboard-check" widgetId="averageCheckpoints"/>
            </div>
        </div>

        <!-- Add Template Button -->
        <div class="d-grid d-lg-flex d-md-flex action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center d-flex">
                @if ($addPermission == 'all' || $addPermission == 'added')
                    <x-forms.link-primary :link="route('audit-templates.create')" class="mr-3 openRightModal float-left" icon="plus">
                        @lang('audit::app.addTemplate')
                    </x-forms.link-primary>
                @endif
            </div>
        </div>
        <!-- End Add Template Button -->

        <!-- Template Table -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
        <!-- End Template Table -->
    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#audit-templates-table').on('preXhr.dt', function(e, settings, data) {
            data['department_id'] = $('#department_id').val();
            data['status'] = $('#filter_status').val();
            data['searchText'] = $('#search-text-field').val();
        });

        const showTable = () => {
            window.LaravelDataTables["audit-templates-table"].draw(true);
        }

        $('#department_id, #filter_status').on('change', function() {
            if ($('#department_id').val() != "all" || $('#filter_status').val() != "all") {
                $('#reset-filters').removeClass('d-none');
            } else {
                $('#reset-filters').addClass('d-none');
            }
            showTable();
        });

        $('#search-text-field').on('keyup', function() {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
            }
            showTable();
        });

        $('#reset-filters').click(function() {
            $('#department_id').val('all');
            $('#filter_status').val('all');
            $('#search-text-field').val('');
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('template-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('audit-templates.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        blockUI: true,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                showTable();
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush

