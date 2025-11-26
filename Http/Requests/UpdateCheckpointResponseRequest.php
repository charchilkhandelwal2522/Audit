<?php

namespace Modules\Audit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCheckpointResponseRequest extends FormRequest
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
            'status' => 'required|in:not_completed,partially_completed,completed',
            'notes' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240', // 10MB max per file
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.required' => __('audit::app.validation.statusRequired'),
            'status.in' => __('audit::app.validation.statusInvalid'),
            'files.*.max' => __('audit::app.validation.fileTooLarge'),
        ];
    }
}

