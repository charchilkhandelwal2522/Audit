<div class="row">
    <div class="col-sm-12">
        <x-form id="start-audit-form">
            <div class="add-client bg-white rounded">
                <!-- Header with Auditor Info -->
                <div class="d-flex justify-content-between align-items-start p-20 border-bottom-grey">
                    <div>
                        <h4 class="mb-1 f-21 font-weight-normal">@lang('audit::app.newAuditInitiation')</h4>
                        <p class="text-muted mb-0 f-14">@lang('audit::app.newAuditInitiationInfo')</p>
                    </div>
                    <div class="text-right">
                        <p class="mb-1 f-14"><strong>@lang('audit::app.auditor'):</strong> {{ user()->name }}</p>
                        <p class="mb-0 text-muted f-13">@lang('app.date'): {{ now()->translatedFormat(company()->date_format) }}</p>
                    </div>
                </div>

                <div class="p-20">
                    <!-- Section 1: Select Department & Auditee -->
                    <div class="mb-4">
                        <h5 class="f-15 font-weight-bold text-dark mb-3">
                            <span class="badge badge-primary rounded-circle mr-2" style="width: 24px; height: 24px; line-height: 16px;">1</span>
                            @lang('audit::app.selectDepartmentAndAuditee')
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <x-forms.label class="my-3" fieldId="department_id" :fieldLabel="__('app.department')">
                                    </x-forms.label>
                                    <select class="form-control select-picker" name="department_id" id="department_id" data-live-search="true">
                                        <option value="">@lang('audit::app.chooseDepartment')</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->team_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <x-forms.label class="my-3" fieldId="auditee_id" :fieldLabel="__('audit::app.personResponsible')">
                                    </x-forms.label>
                                    <select class="form-control select-picker" name="auditee_id" id="auditee_id" data-live-search="true" disabled>
                                        <option value="">@lang('audit::app.choosePerson')</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Select Audit Template -->
                    <div class="mb-4">
                        <h5 class="f-15 font-weight-bold text-dark mb-3">
                            <span class="badge badge-primary rounded-circle mr-2" style="width: 24px; height: 24px; line-height: 16px;">2</span>
                            @lang('audit::app.selectAuditTemplate')
                        </h5>
                        <p class="text-muted f-13 mb-3" id="template-placeholder-text">@lang('audit::app.availableTemplatesInfo')</p>

                        <div class="form-group">
                            <input type="hidden" name="audit_template_id" id="audit_template_id" value="">

                            <div class="row" id="templates-container">
                                <!-- Template cards will be loaded here -->
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Review and Begin -->
                    <div class="mb-3">
                        <h5 class="f-15 font-weight-bold text-dark mb-3">
                            <span class="badge badge-primary rounded-circle mr-2" style="width: 24px; height: 24px; line-height: 16px;">3</span>
                            @lang('audit::app.reviewAndBegin')
                        </h5>
                        <div class="bg-light border rounded p-3">
                            <p class="mb-0 f-14 text-dark-grey">
                                <i class="fa fa-info-circle text-primary mr-2"></i>
                                @lang('audit::app.reviewAndBeginInfo')
                            </p>
                        </div>
                    </div>
                </div>

                <div class="w-100 border-top-grey d-flex justify-content-end px-4 py-3">
                    <x-forms.button-cancel :link="route('audits.index')" class="border-0 mr-3">@lang('app.cancel')
                    </x-forms.button-cancel>
                    <x-forms.button-primary id="start-audit" icon="play">
                        @lang('audit::app.beginAudit')
                    </x-forms.button-primary>
                </div>
            </div>
        </x-form>
    </div>
</div>

<style>
.template-card {
    border: 2px solid #e3e6ef;
    border-radius: 8px;
    padding: 15px;
    cursor: pointer;
    transition: all 0.2s ease;
    height: 100%;
    background: #fff;
}
.template-card:hover {
    border-color: #99a5b5;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.template-card.selected {
    border-color: var(--header_color);
    background-color: rgba(var(--header_color_rgb), 0.03);
}
.template-card .template-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
.template-card .template-title {
    font-weight: 600;
    font-size: 14px;
    color: #333;
    margin-bottom: 5px;
}
.template-card .template-desc {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 10px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    max-height: 3.6em; /* Approximately 3 lines with line-height 1.4 */
}
.template-card .template-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.template-card .checkpoint-badge {
    font-size: 11px;
    color: #6c757d;
}
.template-card .status-badge {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 4px;
}
</style>

<script>
$(document).ready(function() {
    // Department change - load templates and employees
    $(document).on('change', '#department_id', function () {
        const departmentId = $(this).val();

        if (departmentId) {
            // Load templates for department
            loadTemplates(departmentId);

            // Load employees for department
            loadEmployees(departmentId);
        } else {
            // Reset dropdowns and templates
            $('#auditee_id').html('<option value="">@lang("audit::app.choosePerson")</option>').prop('disabled', true).selectpicker('refresh');
            $('#templates-container').html('');
            $('#audit_template_id').val('');
            $('#template-placeholder-text').show();
        }
    });

    function truncateText(text, maxLength) {
        if (!text || text.length <= maxLength) {
            return text || '@lang("audit::app.noDescription")';
        }
        return text.substring(0, maxLength).trim() + '...';
    }

    function loadTemplates(departmentId) {
        const url = "{{ route('audit-templates.by-department', ':id') }}".replace(':id', departmentId);

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                let html = '';

                if (response.templates && response.templates.length > 0) {
                    $('#template-placeholder-text').hide();

                    response.templates.forEach(function(template, index) {
                        const colors = ['#4299e1', '#48bb78', '#ed8936', '#9f7aea', '#f56565'];
                        const icons = ['📋', '✅', '📊', '🔍', '📝'];
                        const bgColor = colors[index % colors.length];
                        const icon = icons[index % icons.length];
                        const checkpointCount = template.checkpoints_count || 0;
                        // Truncate description to approximately 150 characters (roughly 15-20 words or 3 lines)
                        const truncatedDesc = truncateText(template.description, 150);

                        html += `
                            <div class="col-md-6 mb-3">
                                <div class="template-card" data-template-id="${template.id}">
                                    <div class="d-flex align-items-start">
                                        <div class="template-icon mr-3" style="background-color: ${bgColor}20; color: ${bgColor};">
                                            ${icon}
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="template-title">${template.title}</div>
                                            <div class="template-desc" title="${template.description || ''}">${truncatedDesc}</div>
                                            <div class="template-meta">
                                                <span class="checkpoint-badge">
                                                    <i class="fa fa-list-check mr-1"></i> ${checkpointCount} @lang('audit::app.checkpoints')
                                                </span>
                                                <span class="status-badge bg-success text-white">@lang('app.active')</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    $('#template-placeholder-text').show();
                    html = '<div class="col-12"><p class="text-muted text-center py-3">@lang("audit::app.noTemplatesForDepartment")</p></div>';
                }

                $('#templates-container').html(html);
            }
        });
    }

    function loadEmployees(departmentId) {
        const url = "{{ route('audits.employees-by-department', ':id') }}".replace(':id', departmentId);

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                let options = '<option value="">@lang("audit::app.choosePerson")</option>';

                if (response.employees && response.employees.length > 0) {
                    response.employees.forEach(function(employee) {
                        const imageUrl = employee.image ? employee.image : '{{ asset("img/gravatar.png") }}';
                        options += `<option value="${employee.id}" data-content="<span class='d-inline-flex align-items-center'><img src='${imageUrl}' class='taskEmployeeImg rounded-circle mr-2' /> ${employee.name}</span>">${employee.name}</option>`;
                    });
                }

                $('#auditee_id').html(options).prop('disabled', false).selectpicker('refresh');
            }
        });
    }

    // Template card selection
    $(document).on('click', '.template-card', function() {
        $('.template-card').removeClass('selected');
        $(this).addClass('selected');
        $('#audit_template_id').val($(this).data('template-id'));
    });

    // Start audit
    $('#start-audit').click(function() {
        const url = "{{ route('audits.store') }}";

        $.easyAjax({
            url: url,
            container: '#start-audit-form',
            type: "POST",
            disableButton: true,
            blockUI: true,
            buttonSelector: "#start-audit",
            data: $('#start-audit-form').serialize(),
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
