<!-- For sortable content -->
<link rel="stylesheet" href="{{ asset('vendor/css/jquery-ui.css') }}">

<style>
    .checkpoint-item {
        cursor: default;
        transition: all 0.2s ease;
    }
    .checkpoint-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .checkpoint-item.ui-sortable-helper {
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        transform: rotate(1deg);
    }
    .checkpoint-item .drag-handle {
        cursor: grab;
        color: #999;
        font-size: 18px;
        padding: 10px;
    }
    .checkpoint-item .drag-handle:hover {
        color: #333;
    }
    .checkpoint-item .drag-handle:active {
        cursor: grabbing;
    }
    .ui-sortable-placeholder {
        border: 2px dashed #ccc !important;
        background: #f9f9f9 !important;
        visibility: visible !important;
        margin-bottom: 15px;
        border-radius: 4px;
    }
    .checkpoint-number {
        background: #4e73df;
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
        flex-shrink: 0;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-template-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    @lang('audit::app.createTemplate')
                </h4>

                <div class="row p-20">
                    <!-- Template Info -->
                    <div class="col-md-6">
                        <x-forms.text fieldId="title" :fieldLabel="__('audit::app.templateTitle')" fieldName="title" fieldRequired="true" :fieldPlaceholder="__('audit::app.enterTemplateTitle')">
                        </x-forms.text>
                    </div>

                    <div class="col-md-6">
                        <x-forms.label class="my-3" fieldId="department_id" :fieldLabel="__('audit::app.department')" fieldRequired="true">
                        </x-forms.label>
                        <select class="form-control select-picker" name="department_id" id="department_id" data-live-search="true">
                            <option value="">--</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->team_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12">
                        <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.description')" fieldName="description" fieldId="description" :fieldPlaceholder="__('audit::app.templateDescription')">
                        </x-forms.textarea>
                    </div>

                    <div class="col-md-6">
                        <x-forms.label class="my-3" fieldId="status" :fieldLabel="__('app.status')">
                        </x-forms.label>
                        <div class="d-flex">
                            <x-forms.radio fieldId="status-active" fieldValue="active" :checked="true" :fieldLabel="__('app.active')" fieldName="status" />
                            <x-forms.radio fieldId="status-inactive" fieldValue="inactive" :fieldLabel="__('app.inactive')" fieldName="status" />
                        </div>
                    </div>
                </div>

                <!-- Checkpoints Section -->
                <div class="row p-20 border-top-grey">
                    <div class="col-md-12">
                        <h5 class="f-18 font-weight-bold mb-3">
                            <i class="fa fa-list-check mr-2"></i>@lang('audit::app.checkpoints')
                        </h5>
                        <p class="text-muted mb-3">@lang('audit::app.checkpointsHelp')</p>
                    </div>

                    <div class="col-md-12" id="checkpoints-container">
                        <!-- Checkpoint template will be added here dynamically -->
                        <p class="text-muted small mb-2"><i class="fa fa-arrows-alt mr-1"></i> @lang('audit::app.dragToReorder')</p>
                    </div>

                    <div class="col-md-12 mt-3">
                        <x-forms.button-secondary id="add-checkpoint" icon="plus">
                            @lang('audit::app.addCheckpoint')
                        </x-forms.button-secondary>
                    </div>
                </div>

                <div class="w-100 border-top-grey d-flex justify-content-start px-4 py-3">
                    <x-forms.button-primary class="mr-3" id="save-template" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('audit-templates.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </div>
            </div>
        </x-form>
    </div>
</div>

<!-- Checkpoint Template -->
<template id="checkpoint-template">
    <div class="checkpoint-item border rounded p-3 mb-3 bg-light" data-index="__INDEX__">
        <input type="hidden" name="checkpoints[__INDEX__][order]" class="checkpoint-order" value="__INDEX__">
        <div class="row">
            <div class="col-auto d-flex align-items-start pt-3">
                <div class="drag-handle" title="@lang('audit::app.dragToReorder')">
                    <i class="fa fa-grip-vertical"></i>
                </div>
                <span class="checkpoint-number ml-2">__NUMBER__</span>
            </div>
            <div class="col">
                <div class="row">
                    <div class="col-md-6">
                        <x-forms.text fieldId="checkpoints[__INDEX__][title]" :fieldLabel="__('audit::app.checkpointTitle')" fieldName="checkpoints[__INDEX__][title]" fieldRequired="true" :fieldPlaceholder="__('audit::app.enterCheckpointTitle')">
                        </x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="checkpoints[__INDEX__][description]" :fieldLabel="__('app.description')" fieldName="checkpoints[__INDEX__][description]" :fieldPlaceholder="__('audit::app.checkpointDescription')">
                        </x-forms.text>
                    </div>
                    <div class="col-md-12 mt-2">
                        <div class="d-flex flex-wrap">
                            <div class="mr-4">
                                <x-forms.checkbox fieldId="checkpoints[__INDEX__][requires_file_upload]" :fieldLabel="__('audit::app.requiresFileUpload')" fieldName="checkpoints[__INDEX__][requires_file_upload]" />
                            </div>
                            <div class="mr-4">
                                <x-forms.checkbox fieldId="checkpoints[__INDEX__][requires_photo]" :fieldLabel="__('audit::app.requiresPhoto')" fieldName="checkpoints[__INDEX__][requires_photo]" />
                            </div>
                            <div class="mr-4">
                                <x-forms.checkbox fieldId="checkpoints[__INDEX__][requires_notes]" :fieldLabel="__('audit::app.requiresNotes')" fieldName="checkpoints[__INDEX__][requires_notes]" />
                            </div>
                            <div>
                                <x-forms.checkbox fieldId="checkpoints[__INDEX__][is_mandatory]" :fieldLabel="__('audit::app.mandatory')" fieldName="checkpoints[__INDEX__][is_mandatory]" checked="true" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-auto d-flex align-items-center">
                <button type="button" class="btn btn-danger btn-sm remove-checkpoint" data-toggle="tooltip" title="@lang('app.remove')">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<!-- For sortable content -->
<script src="{{ asset('vendor/jquery/jquery-ui.min.js') }}"></script>

<script>
$(document).ready(function() {
    let checkpointIndex = 0;

    function addCheckpoint() {
        const template = $('#checkpoint-template').html();
        const checkpointNumber = $('.checkpoint-item').length + 1;
        let html = template.replace(/__INDEX__/g, checkpointIndex);
        html = html.replace(/__NUMBER__/g, checkpointNumber);
        $('#checkpoints-container').append(html);
        checkpointIndex++;

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Update order values
        updateCheckpointOrder();
    }

    // Update checkpoint numbers and order
    function updateCheckpointOrder() {
        $('.checkpoint-item').each(function(index) {
            $(this).find('.checkpoint-number').text(index + 1);
            $(this).find('.checkpoint-order').val(index);
        });
    }

    // Initialize sortable
    function initSortable() {
        $('#checkpoints-container').sortable({
            items: '.checkpoint-item',
            handle: '.drag-handle',
            placeholder: 'ui-sortable-placeholder',
            tolerance: 'pointer',
            cursor: 'grabbing',
            opacity: 0.8,
            revert: 150,
            update: function(event, ui) {
                updateCheckpointOrder();
            }
        });
    }

    // Add first checkpoint
    addCheckpoint();

    // Initialize sortable
    initSortable();

    // Add checkpoint button
    $('#add-checkpoint').click(function() {
        addCheckpoint();
    });

    // Remove checkpoint
    $(document).on('click', '.remove-checkpoint', function() {
        if ($('.checkpoint-item').length > 1) {
            $(this).closest('.checkpoint-item').fadeOut(300, function() {
                $(this).remove();
                updateCheckpointOrder();
            });
        } else {
            Swal.fire({
                icon: 'warning',
                text: "@lang('audit::app.atLeastOneCheckpoint')",
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            });
        }
    });

    // Save template
    $('#save-template').click(function() {
        const url = "{{ route('audit-templates.store') }}";

        $.easyAjax({
            url: url,
            container: '#save-template-form',
            type: "POST",
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-template",
            data: $('#save-template-form').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    window.location.href = response.redirectUrl;
                }
            }
        });
    });

    init(RIGHT_MODAL);
});
</script>

