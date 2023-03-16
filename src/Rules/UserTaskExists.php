<?php

namespace TaskManagement\Rules;

use Account\Foundation\Collection;
use TaskManagement\Entities\UserTask;
use Illuminate\Contracts\Validation\Rule;

class UserTaskExists implements Rule
{
    /**
     * Determine if the validation rule passes.
     * @param  string $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return !empty(UserTask::findByUuidAccount($value, Collection::get_account()));
    }

    /**
     * Get the validation error message.
     * @return string
     */
    public function message(): string
    {
        return 'User task not found.';
    }
}
