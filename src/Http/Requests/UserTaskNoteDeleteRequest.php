<?php

namespace TaskManagement\Http\Requests;

use Helper\Http\Requests\BaseRequest;
use TaskManagement\Rules\UserTaskExists;
use TaskManagement\Rules\UserTaskNoteExists;

/**
 * @property string $uuid
 * @property array $note_uuids
 **/
class UserTaskNoteDeleteRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'uuid' => ['required', 'string', 'uuid', new UserTaskExists],
            'note_uuids' => ['required', 'array'],
            'note_uuids.*' => ['bail', 'string', 'uuid', new UserTaskNoteExists($this->get('uuid'))]
        ];
    }
}
