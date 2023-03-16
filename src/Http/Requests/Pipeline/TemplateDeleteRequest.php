<?php

namespace TaskManagement\Http\Requests\Pipeline;

/**
 * @property array $uuids
 **/
class TemplateDeleteRequest extends TemplateViewRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        $rules = parent::rules();
        return [
            'uuids' => ['required', 'array'],
            'uuids.*' => $rules['uuid']
        ];
    }
}
