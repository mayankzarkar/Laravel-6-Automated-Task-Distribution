<?php

namespace TaskManagement\Http\Requests\Pipeline;

use Helper\Http\Requests\BaseRequest;
use TaskManagement\Rules\PipelineTemplateExists;

/**
 * @property string $uuid
 **/
class TemplateViewRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'uuid' => ['required', 'string', 'uuid', new PipelineTemplateExists]
        ];
    }
}
