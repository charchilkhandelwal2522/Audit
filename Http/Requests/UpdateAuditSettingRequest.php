<?php

namespace Modules\Audit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAuditSettingRequest extends FormRequest
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
            'partial_completion_weight' => 'required|numeric|min:0|max:1',
            // 'score_threshold_alert' => 'required|integer|min:0|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'partial_completion_weight.required' => __('audit::validation.partialCompletionWeightRequired'),
            'partial_completion_weight.numeric' => __('audit::validation.partialCompletionWeightNumeric'),
            'partial_completion_weight.min' => __('audit::validation.partialCompletionWeightMin'),
            'partial_completion_weight.max' => __('audit::validation.partialCompletionWeightMax'),
        ];
    }
}

