<?php

namespace TaskManagement\Rules;

use Account\Foundation\Collection;
use TaskManagement\Entities\UserTask;
use Illuminate\Contracts\Validation\Rule;

class UserTaskNoteExists implements Rule
{
    private $userTaskUuid;
    public function __construct($userTaskUuid)
    {
        $this->userTaskUuid = $userTaskUuid;
    }

    /**
     * Determine if the validation rule passes.
     * @param  string $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $userTask = UserTask::findByUuidAccount($this->userTaskUuid, Collection::get_account());
        if (!empty($userTask)) {
            return array_search($value, array_column($userTask->notes, 'uuid')) !== FALSE;
        }

        return false;
    }

    /**
     * Get the validation error message.
     * @return string
     */
    public function message(): string
    {
        return 'User task note not found.';
    }
}
