<?php

namespace TaskManagement\Http\Requests\Pipeline;

use Helper\Http\Requests\BaseRequest;
use Pipeline\Service\Rules\V2\PipelineExists;
use TaskManagement\Rules\PipelineTemplateTaskExists;

/**
 * @property string $pipeline_uuid
 * @property string|null $parent_uuid
 * @property bool $is_grouped
 **/
class TemplateSourceRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'pipeline_uuid' => ['required', 'string', 'uuid', new PipelineExists],
            'parent_uuid' => ['bail', $this->get('is_grouped') ? 'required' : 'nullable', 'string', 'uuid', new PipelineTemplateTaskExists],
            'is_grouped' => ['required', 'boolean']
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_grouped' => $this->get('is_grouped') ?? false
        ]);
    }
}
