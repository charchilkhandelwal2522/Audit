<?php

namespace Modules\Audit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'audit_template_id' => 'required|exists:audit_templates,id',
            'department_id' => 'required|exists:teams,id',
            'auditee_id' => 'required|exists:users,id',
            'location' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'audit_template_id.required' => __('audit::validation.templateRequired'),
            'department_id.required' => __('audit::validation.departmentRequired'),
            'auditee_id.required' => __('audit::validation.auditeeRequired'),
        ];
    }
}

