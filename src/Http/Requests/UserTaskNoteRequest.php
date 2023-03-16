<?php

namespace TaskManagement\Http\Requests;

use Helper\Http\Requests\BaseRequest;
use TaskManagement\Rules\UserTaskExists;

/**
 * @property string $uuid
 * @property string $note
 **/
class UserTaskNoteRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'uuid' => ['required', 'string', 'uuid', new UserTaskExists],
            'note' => ['required', 'string']
        ];
    }
}
