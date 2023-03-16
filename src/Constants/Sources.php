<?php

namespace TaskManagement\Constants;

final class Sources
{
    const RECRUITER_AND_ACTIONS  = 301;
    const EMPLOYEE_AND_ACTIONS  = 302;
    const CANDIDATE_AND_ACTIONS  = 303;
    const TASK_AND_ACTIONS  = 304;

    const SOURCES = [
        self::RECRUITER_AND_ACTIONS => 'Recruiter and actions',
        self::EMPLOYEE_AND_ACTIONS => 'Employee and actions',
        self::CANDIDATE_AND_ACTIONS => 'Candidate and actions',
        self::TASK_AND_ACTIONS => 'Task and actions'
    ];

    const RECRUITER_SOURCES = [
        'source_key' => self::RECRUITER_AND_ACTIONS,
        'source_value' => self::SOURCES[self::RECRUITER_AND_ACTIONS],
        'source_operator_groups' => [
            Groups::OPERATOR_STAGES_GROUP,
            Groups::OPERATOR_EVALUATION_FORM_GROUP,
            Groups::OPERATOR_CANDIDATE_RATING_GROUP,
            Groups::OPERATOR_PUSH_TO_HRMS_GROUP,
        ]
    ];

    const EMPLOYEE_SOURCES = [
        'source_key' => self::EMPLOYEE_AND_ACTIONS,
        'source_value' => self::SOURCES[self::EMPLOYEE_AND_ACTIONS],
        'source_operator_groups' => [
            Groups::OPERATOR_STAGES_GROUP,
            Groups::OPERATOR_EVALUATION_FORM_GROUP,
            Groups::OPERATOR_CANDIDATE_RATING_GROUP,
            Groups::OPERATOR_PUSH_TO_HRMS_GROUP,
        ]
    ];

    const CANDIDATE_SOURCES = [
        'source_key' => self::CANDIDATE_AND_ACTIONS,
        'source_value' => self::SOURCES[self::CANDIDATE_AND_ACTIONS],
        'source_operator_groups' => [
            Groups::OPERATOR_CANDIDATE_STAGES_GROUP,
            Groups::OPERATOR_OFFERS_GROUP,
            Groups::OPERATOR_FORMS_GROUP,
            // Groups::OPERATOR_QUESTIONNAIRE_GROUP,
            // Groups::OPERATOR_VIDEO_ASSESSMENT_GROUP,
            // Groups::OPERATOR_INTERVIEW_GROUP,
            // Groups::OPERATOR_PROFILE_GROUP
        ]
    ];

    const TASK_SOURCES = [
        'source_key' => self::TASK_AND_ACTIONS,
        'source_value' => self::SOURCES[self::TASK_AND_ACTIONS],
        'source_operator_groups' => [
            Groups::OPERATOR_TASK_STATUS_GROUP
        ]
    ];
}
