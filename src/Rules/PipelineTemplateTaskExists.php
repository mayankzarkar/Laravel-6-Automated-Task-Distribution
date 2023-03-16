<?php

namespace TaskManagement\Rules;

use Illuminate\Contracts\Validation\Rule;
use TaskManagement\Entities\PipelineTemplateTask;

class PipelineTemplateTaskExists implements Rule
{
    /**
     * Determine if the validation rule passes.
     * @param  string $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return !empty(PipelineTemplateTask::findByPk($value));
    }

    /**
     * Get the validation error message.
     * @return string
     */
    public function message(): string
    {
        return 'Pipeline template task not found.';
    }
}
