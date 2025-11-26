<?php

namespace Modules\Audit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAuditTemplateRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'required|exists:teams,id',
            'status' => 'nullable|in:active,inactive',
            'checkpoints' => 'required|array|min:1',
            'checkpoints.*.title' => 'required|string|max:255',
            'checkpoints.*.description' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => __('audit::app.validation.titleRequired'),
            'department_id.required' => __('audit::app.validation.departmentRequired'),
            'checkpoints.required' => __('audit::app.validation.checkpointsRequired'),
            'checkpoints.min' => __('audit::app.validation.checkpointsMin'),
            'checkpoints.*.title.required' => __('audit::app.validation.checkpointTitleRequired'),
        ];
    }
}

