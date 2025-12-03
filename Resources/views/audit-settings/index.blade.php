@extends('layouts.app')

@section('content')
    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        <x-setting-sidebar :activeMenu="$activeSettingMenu"/>

        <x-setting-card method="POST">
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                        @lang('audit::app.auditSettings')
                    </h2>
                </div>
            </x-slot>

            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4">
                <div class="row">
                    <!-- Scoring Settings -->
                    <div class="col-md-12">
                        <h5 class="f-16 font-weight-bold pb-2">
                            @lang('audit::app.scoringSettings')
                        </h5>
                    </div>

                    <div class="col-md-6">
                        <x-forms.number fieldId="partial_completion_weight"
                            :fieldLabel="__('audit::app.partialCompletionWeight')"
                            fieldName="partial_completion_weight"
                            fieldRequired="true"
                            :fieldValue="$setting->partial_completion_weight"
                            fieldStep="0.01"
                            fieldMin="0"
                            fieldMax="1"
                            :fieldHelp="__('audit::app.partialCompletionWeightHelp')">
                        </x-forms.number>
                    </div>

                    <!-- To-Do: Add score threshold alert -->
                    {{-- <div class="col-md-6">
                        <x-forms.number fieldId="score_threshold_alert"
                            :fieldLabel="__('audit::app.scoreThresholdAlert')"
                            fieldName="score_threshold_alert"
                            fieldRequired="true"
                            :fieldValue="$setting->score_threshold_alert"
                            fieldMin="0"
                            fieldMax="100"
                            :fieldHelp="__('audit::app.scoreThresholdAlertHelp')">
                        </x-forms.number>
                    </div> --}}

                    <!-- Notification Settings -->
                    <div class="col-md-12 mt-4">
                        <h5 class="f-16 font-weight-bold pb-2 mb-3">
                            @lang('audit::app.notificationSettings')
                        </h5>
                    </div>

                    <div class="col-md-4">
                        <x-forms.checkbox fieldId="send_result_to_manager"
                            :fieldLabel="__('audit::app.sendResultToManager')"
                            fieldName="send_result_to_manager"
                            :checked="$setting->send_result_to_manager" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.checkbox fieldId="send_result_to_auditee"
                            :fieldLabel="__('audit::app.sendResultToAuditee')"
                            fieldName="send_result_to_auditee"
                            :checked="$setting->send_result_to_auditee" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.checkbox fieldId="send_result_to_auditor"
                            :fieldLabel="__('audit::app.sendResultToAuditor')"
                            fieldName="send_result_to_auditor"
                            :checked="$setting->send_result_to_auditor" />
                    </div>

                    <!-- To-Do: Add generate PDF report -->
                    {{-- <div class="col-md-4">
                        <x-forms.checkbox fieldId="generate_pdf_report"
                            :fieldLabel="__('audit::app.generatePdfReport')"
                            fieldName="generate_pdf_report"
                            :checked="$setting->generate_pdf_report" />
                    </div> --}}
                </div>
            </div>

            <x-slot name="action">
                <!-- Buttons Start -->
                <div class="w-100 border-top-grey">
                    <x-setting-form-actions>
                        <x-forms.button-primary id="save-settings" icon="check">
                            @lang('app.save')
                        </x-forms.button-primary>
                    </x-setting-form-actions>
                </div>
                <!-- Buttons End -->
            </x-slot>

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->
@endsection

@push('scripts')
<script>
    $('#save-settings').click(function() {
        $.easyAjax({
            url: "{{ route('audit-settings.update') }}",
            container: '#editSettings',
            type: "POST",
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-settings",
            data: $('#editSettings').serialize(),
        });
    });
</script>
@endpush

