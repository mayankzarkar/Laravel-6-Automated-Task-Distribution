<?php

namespace TaskManagement\Rules;

use Illuminate\Contracts\Validation\Rule;
use TaskManagement\Entities\PipelineTemplateTask;

class PipelineTemplateTaskDuplicate implements Rule
{
    /** @var array $exclude */
    private $exclude;

    /**
     * @param string|null $uuid
     */
    public function __construct(string $uuid = null)
    {
        if ($uuid) {
            $this->exclude[] = $uuid;
        }
    }

    /**
     * Determine if the validation rule passes.
     * @param  string $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $query = PipelineTemplateTask::where('parent_uuid', $value);
        if (!empty($this->exclude)) {
            $query->whereNotIn("uuid", $this->exclude);
        }

        return empty($query->first());
    }

    /**
     * Get the validation error message.
     * @return string
     */
    public function message(): string
    {
        return 'Duplicate pipeline template task not allowed.';
    }
}
