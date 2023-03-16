<?php

namespace TaskManagement\Traits;

use formbuilder\Rules\Template;
use Helper\Rules\AssessmentExists;
use Helper\Rules\QuestionnaireExists;
use TaskManagement\Constants\Groups;
use Pipeline\Service\Rules\V2\StageExists;
use Lookup\Rules\AccountLevel\TaskStatusExists;

trait SourceValueRule
{
    /**
     * @param array $source_groups
     * @param int|null $source_group
     * @return array
     */
    public function validateSourceValue(array $source_groups, ?int $source_group): array
    {
        if (!in_array($source_group, $source_groups)) {
            return [];
        }

        switch ($source_group) {
            case Groups::GROUP_FORM:
            case Groups::GROUP_OFFERS:
                $rules = ["uuid", new Template];
                break;

            case Groups::GROUP_QUESTIONNAIRE:
                $rules = ["uuid", new QuestionnaireExists];
                break;

            case Groups::GROUP_VIDEO_ASSESSMENT:
                $rules = ["uuid", new AssessmentExists];
                break;

            case Groups::GROUP_STAGES:
                $rules = ["uuid", new StageExists];
                break;

            case Groups::GROUP_TASK_STATUS:
                $rules = ["uuid", new TaskStatusExists];
                break;

            default:
                $rules = [];
                break;
        }

        return $rules;
    }
}
