<?php

namespace TaskManagement\Traits;

use Helper\Constants\ApiOptions;
use Lookup\Entities\TaskStatus;
use TaskManagement\Constants\Enums;
use TaskManagement\Constants\Actions;

trait ActionTrait
{
    /**
     * @return array
     */
    public static function GetActions(): array
    {
        return collect([
            Actions::ACTION_CREATE_TASK_RULES,
            Actions::ACTION_MOVE_CANDIDATE_TO_RULES,
            // Actions::ACTION_SEND_EMAIL_RULES
        ])->map(function ($element) {
            $element['validations'] = self::GetActionOptions($element['key']);
            return $element;
        })->toArray();
    }

    /**
     * @param int|null $action
     * @return array
     */
    private static function GetActionOptions(?int $action): array
    {
        $actions = [
            Actions::ACTION_MOVE_CANDIDATE_TO => [
                "stage_uuid" => [
                    "type" => "dropdown",
                    "end_point" => route('pipeline.v2.view'),
                    "method" => ApiOptions::GET_METHOD,
                    "data_key" => "stages",
                    "params" => [
                        [
                            "key" => "uuid",
                            "default_value" => null,
                            "is_required" => true
                        ]
                    ],
                    "primary_key" => ApiOptions::PRIMARY_KEY
                ]
            ],
            Actions::ACTION_CREATE_TASK => [
                "title" => [
                    "type" => "string"
                ],
                "description" => [
                    "type" => "string"
                ],
                "status_uuid" => TaskStatus::GetFilterOptions(),
                "type" => [
                    "type" => "dropdown",
                    "end_point" => route('task_management.user_task.types'),
                    "method" => ApiOptions::GET_METHOD,
                    "data_key" => null,
                    "required_params" => [],
                    "primary_key" => "key"
                ],
                "enable_notification" => [
                    "type" => "boolean"
                ],
                "responsibility_type" => [
                    "type" => "dropdown",
                    "options" => Enums::USER_RESPONSIBILITY
                ],
                "responsibility_uuid" => [
                    [
                        "key" => Enums::RECRUITER,
                        "relation" => "responsibility_type",
                        "type" => "dropdown",
                        "end_point" => route('external.service.user.v1.list'),
                        "method" => ApiOptions::GET_METHOD,
                        "data_key" => null,
                        "params" => [
                            [
                                "key" => "status",
                                "default_value" => 1,
                                "is_required" => true
                            ]
                        ],
                        "primary_key" => ApiOptions::PRIMARY_KEY
                    ],
                    [
                        "key" => Enums::EMPLOYEE,
                        "relation" => "responsibility_type",
                        "type" => "dropdown",
                        "end_point" => route('employee.frontend.api.v1.list'),
                        "method" => ApiOptions::GET_METHOD,
                        "data_key" => null,
                        "params" => [
                            [
                                "key" => "all_employee",
                                "default_value" => 1,
                                "is_required" => true
                            ],
                            [
                                "key" => "status",
                                "default_value" => 1,
                                "is_required" => true
                            ],
                        ],
                        "primary_key" => ApiOptions::PRIMARY_KEY
                    ]
                ],
                "duration" => [
                    "type" => "integer"
                ],
                "has_reminder" => [
                    "type" => "boolean"
                ],
                "reminder_configuration" => [
                    "type" => "array"
                ],
                "tags" => [
                    "type" => "array"
                ],
                "additional_data" => [
                    "type" => "array"
                ]
            ],
            Actions::ACTION_SEND_EMAIL => [
                /*"template_uuid" => [
                    "type" => "dropdown",
                    "end_point" => route('recruiter.mail.list'),
                    "method" => ApiOptions::GET_METHOD,
                    "data_key" => null,
                    "required_params" => [],
                    "primary_key" => ApiOptions::PRIMARY_KEY
                ],*/
                "subject" => [
                    "type" => "string"
                ],
                "body" => [
                    "type" => "string"
                ],
                "receiver_type" => [
                    "type" => "dropdown",
                    "options" => Actions::SEND_EMAIL_TYPES
                ],
                "to_email" => [
                    "type" => "string"
                ],
                "to_name" => [
                    "type" => "string"
                ]
            ]
        ];

        if (empty($action)) {
            return $actions;
        }

        return $actions[$action];
    }
}
