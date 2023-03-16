<?php

namespace TaskManagement\Rules;

use Account\Foundation\Collection;
use Illuminate\Contracts\Validation\Rule;
use TaskManagement\Entities\PipelineTemplate;

class PipelineTemplateExists implements Rule
{
    /**
     * Determine if the validation rule passes.
     * @param  string $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return !empty(PipelineTemplate::findByUuidAccount($value, Collection::get_account()));
    }

    /**
     * Get the validation error message.
     * @return string
     */
    public function message(): string
    {
        return 'Pipeline template not found.';
    }
}
