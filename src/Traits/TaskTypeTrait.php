<?php

namespace TaskManagement\Traits;

use formbuilder\Entities\Offer;
use formbuilder\Entities\FormRequest;
use service\mail\Entities\EmailTemplate;
use TaskManagement\Constants\TaskTypes;

trait TaskTypeTrait
{
    /**
     * @return array
     */
    public static function GetTypes(bool $candidate_relation): array
    {
        $types = TaskTypes::TYPES_PAIRS;
        if (!$candidate_relation) {
            return [$types[0]];
        }

        return self::PrepareTypes($types);
    }

    /**
     * @param array $types
     * @return array
     */
    private static function PrepareTypes(array $types): array
    {
        return array_map(function ($type) {
            switch ($type['key']) {
                case TaskTypes::OFFER:
                    $type['options'] = Offer::GetFilterOptions();
                    $type['extra_options'][] = EmailTemplate::GetFilterOptions();
                    break;

                case TaskTypes::FORM:
                    $type['options'] = FormRequest::GetFilterOptions();
                    break;

//                case TaskTypes::PROFILE:
//                case TaskTypes::RESUME:
//                case TaskTypes::INTRODUCTION:
//                case TaskTypes::ATTACHMENT:
//                case TaskTypes::EVALUATION:
//                case TaskTypes::QUESTIONNAIRE:
//                case TaskTypes::VIDEO_ASSESSMENT:
//                case TaskTypes::ASSESSMENT_TEST:
//                case TaskTypes::MEETINGS:
//                case TaskTypes::SHARE:
//                case TaskTypes::LOG:
//                case TaskTypes::EMAIL:
//                case TaskTypes::PUSH_TO_HRMS:
//                case TaskTypes::VISA_STATUS:

                default:
                    $type['options'] = [];
                    break;
            }
            return $type;
        }, $types);
    }
}
