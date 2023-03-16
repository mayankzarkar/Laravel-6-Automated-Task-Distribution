<?php

namespace TaskManagement\Http\Requests\Pipeline;

use Helper\Http\Requests\BaseRequest;
use TaskManagement\Rules\PipelineTemplateTaskExists;

/**
 * @property array $uuids
 **/
class TemplateTaskDeleteRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'uuids' => ['required', 'array'],
            'uuids.*' => ['required', 'string', 'uuid', new PipelineTemplateTaskExists]
        ];
    }
}
