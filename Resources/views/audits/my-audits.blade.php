@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <!-- STATUS START -->
        <div class="select-box d-flex py-2 px-lg-3 px-md-3 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.status')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="status" id="filter_status">
                    <option value="all">@lang('app.all')</option>
                    <option value="completed">@lang('audit::app.completed')</option>
                    <option value="in_progress">@lang('audit::app.inProgress')</option>
                </select>
            </div>
        </div>
        <!-- STATUS END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-2 px-lg-3 px-md-3 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->
    </x-filters.filter-box>
@endsection

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Header -->
        <div class="d-grid d-lg-flex d-md-flex action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center d-flex">
                <h4 class="mb-0"></h4>
            </div>
        </div>
        <!-- End Header -->

        <!-- Info Box -->
        <div class="alert alert-info mt-3">
            <i class="fa fa-info-circle mr-2"></i>
            @lang('audit::app.myAuditsInfo')
        </div>

        <!-- Audit Table -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
        <!-- End Audit Table -->
    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#audits-table').on('preXhr.dt', function(e, settings, data) {
            data['status'] = $('#filter_status').val();
            data['my_audits'] = true;
        });

        const showTable = () => {
            window.LaravelDataTables["audits-table"].draw(true);
        }

        $('#filter_status').on('change', function() {
            if ($('#filter_status').val() != "all") {
                $('#reset-filters').removeClass('d-none');
            } else {
                $('#reset-filters').addClass('d-none');
            }
            showTable();
        });

        $('#reset-filters').click(function() {
            $('#filter_status').val('all');
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });
    </script>
@endpush

