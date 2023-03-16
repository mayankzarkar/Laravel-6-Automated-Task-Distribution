<?php

namespace TaskManagement\Http\Requests;

use Helper\Http\Requests\BaseRequest;
use TaskManagement\Rules\UserTaskExists;

/**
 * @property array $uuids
 **/
class UserTaskDeleteRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'uuids' => ['required', 'array'],
            'uuids.*' => ['bail', 'string', 'uuid', new UserTaskExists]
        ];
    }
}
