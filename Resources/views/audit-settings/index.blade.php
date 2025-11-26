@extends('layouts.app')

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12">
                <x-form id="save-settings-form">
                    <div class="card bg-white border-0 b-shadow-4">
                        <div class="card-header bg-white border-bottom-grey">
                            <h4 class="mb-0"><i class="fa fa-cog mr-2"></i>@lang('audit::app.auditSettings')</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Scoring Settings -->
                                <div class="col-md-12 mb-4">
                                    <h5 class="f-16 font-weight-bold border-bottom pb-2 mb-3">
                                        <i class="fa fa-calculator mr-2"></i>@lang('audit::app.scoringSettings')
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

                                <div class="col-md-6">
                                    <x-forms.number fieldId="score_threshold_alert"
                                        :fieldLabel="__('audit::app.scoreThresholdAlert')"
                                        fieldName="score_threshold_alert"
                                        fieldRequired="true"
                                        :fieldValue="$setting->score_threshold_alert"
                                        fieldMin="0"
                                        fieldMax="100"
                                        :fieldHelp="__('audit::app.scoreThresholdAlertHelp')">
                                    </x-forms.number>
                                </div>

                                <!-- Notification Settings -->
                                <div class="col-md-12 mb-4 mt-4">
                                    <h5 class="f-16 font-weight-bold border-bottom pb-2 mb-3">
                                        <i class="fa fa-bell mr-2"></i>@lang('audit::app.notificationSettings')
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
                                    <x-forms.checkbox fieldId="generate_pdf_report"
                                        :fieldLabel="__('audit::app.generatePdfReport')"
                                        fieldName="generate_pdf_report"
                                        :checked="$setting->generate_pdf_report" />
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top">
                            <x-forms.button-primary id="save-settings" icon="check">
                                @lang('app.save')
                            </x-forms.button-primary>
                        </div>
                    </div>
                </x-form>
            </div>
        </div>
    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
<script>
    $('#save-settings').click(function() {
        $.easyAjax({
            url: "{{ route('audit-settings.update') }}",
            container: '#save-settings-form',
            type: "POST",
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-settings",
            data: $('#save-settings-form').serialize(),
        });
    });
</script>
@endpush

