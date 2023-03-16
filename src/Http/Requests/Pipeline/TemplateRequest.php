<?php

namespace TaskManagement\Http\Requests\Pipeline;

use Pipeline\Service\Rules\V2\PipelineExists;

/**
 * @property string $uuid
 * @property string $pipeline_uuid
 * @property string $title
 **/
class TemplateRequest extends TemplateViewRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            "title" =>  ['required', 'string']
        ];

        if ($this->getMethod() == "PUT") {
            $rules = array_merge($rules, parent::rules());
            $rules["is_published"] = ['required', 'bool'];
        } else {
            $rules["pipeline_uuid"] = ['required', 'string', 'uuid', new PipelineExists];
        }

        return $rules;
    }
}
