<?php

namespace TaskManagement\Http\Requests;

use Helper\Http\Requests\BaseRequest;
use TaskManagement\Rules\UserTaskExists;
use Lookup\Rules\AccountLevel\TaskStatusExists;

/**
 * @property array $list
 **/
class UserTaskStatusRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'list' => ['required', 'array'],
            "list.*.uuid"  => ['bail', 'required', 'string', 'uuid', new UserTaskExists],
            "list.*.status_uuid"  => ['bail', 'required', 'string', 'uuid', new TaskStatusExists]
        ];
    }
}
