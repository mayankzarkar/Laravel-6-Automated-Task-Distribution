<?php

namespace TaskManagement\Constants;

use formbuilder\Constants\Status;
use formbuilder\Constants\FormEnum;

final class Groups
{
    // Operator Groups
    const GROUP_STAGES = 201;
    const GROUP_OFFERS = 202;
    const GROUP_QUESTIONNAIRE = 203;
    const GROUP_VIDEO_ASSESSMENT = 204;
    const GROUP_INTERVIEW = 205;
    const GROUP_PROFILE = 206;
    const GROUP_EVALUATION_FORM = 207;
    const GROUP_CANDIDATE_RATING = 208;
    const GROUP_PUSH_TO_HRMS = 209;
    const GROUP_TASK_STATUS = 210;
    const GROUP_FORM = 211;

    const GROUPS = [
        self::GROUP_STAGES => 'Stages',
        self::GROUP_OFFERS => 'Offers',
        self::GROUP_QUESTIONNAIRE => 'Questionnaire',
        self::GROUP_VIDEO_ASSESSMENT => 'Video assessment',
        self::GROUP_INTERVIEW => 'Interview',
        self::GROUP_PROFILE => 'Profile',
        self::GROUP_EVALUATION_FORM => 'Evaluation form',
        self::GROUP_CANDIDATE_RATING => 'Candidate rating',
        self::GROUP_PUSH_TO_HRMS => 'Push to HRMS',
        self::GROUP_TASK_STATUS => 'Status',
        self::GROUP_FORM => 'Form'
    ];

    // Filter Groups
    const FILTER_GROUP_CANDIDATE = 501;
    const FILTER_GROUP_STAGE = 502;
    const FILTER_GROUP_OFFER = 503;
    const FILTER_GROUP_QUESTIONNAIRE = 504;
    const FILTER_GROUP_VIDEO_ASSESSMENT = 505;
    const FILTER_GROUP_INTERVIEW = 506;
    const FILTER_GROUP_PROFILE = 507;
    const FILTER_GROUP_FORM = 508;

    const FILTER_GROUPS = [
        self::FILTER_GROUP_CANDIDATE => "Candidate",
        self::FILTER_GROUP_STAGE => "Stage",
        self::FILTER_GROUP_OFFER => "Offer",
        self::FILTER_GROUP_QUESTIONNAIRE => "Questionnaire",
        self::FILTER_GROUP_VIDEO_ASSESSMENT => "Video assessment",
        self::FILTER_GROUP_INTERVIEW => "Interview",
        self::FILTER_GROUP_PROFILE => "Profile",
        self::FILTER_GROUP_FORM => "Form"
    ];

    // Source Groups Attributes
    const ATTRIBUTE_WITH_IN = 701;
    const ATTRIBUTE_FOR = 702;

    const ATTRIBUTES = [
        self::ATTRIBUTE_WITH_IN => "Within",
        self::ATTRIBUTE_FOR => "For"
    ];

    // Source Groups Attribute Options
    const ATTRIBUTE_OPTION_HOURS = 801;
    const ATTRIBUTE_OPTION_DAYS = 802;
    const ATTRIBUTE_OPTION_WEEKS = 803;
    const ATTRIBUTE_OPTION_MONTHS = 804;

    const ATTRIBUTE_OPTIONS = [
        self::ATTRIBUTE_OPTION_HOURS => "Hours",
        self::ATTRIBUTE_OPTION_DAYS => "Days",
        self::ATTRIBUTE_OPTION_WEEKS => "Weeks",
        self::ATTRIBUTE_OPTION_MONTHS => "Months"
    ];

    const ATTRIBUTE_OPTION_PAIRS = [
        [
            "key" => self::ATTRIBUTE_OPTION_HOURS,
            "value" => self::ATTRIBUTE_OPTIONS[self::ATTRIBUTE_OPTION_HOURS]
        ],
        [
            "key" => self::ATTRIBUTE_OPTION_DAYS,
            "value" => self::ATTRIBUTE_OPTIONS[self::ATTRIBUTE_OPTION_DAYS]
        ],
        [
            "key" => self::ATTRIBUTE_OPTION_WEEKS,
            "value" => self::ATTRIBUTE_OPTIONS[self::ATTRIBUTE_OPTION_WEEKS]
        ],
        [
            "key" => self::ATTRIBUTE_OPTION_MONTHS,
            "value" => self::ATTRIBUTE_OPTIONS[self::ATTRIBUTE_OPTION_MONTHS]
        ]
    ];

    const ATTRIBUTE_OPTION_FOR = [
        "key" => self::ATTRIBUTE_FOR,
        "value" => self::ATTRIBUTES[self::ATTRIBUTE_FOR],
        "validations" => [
            [
                "key" => "duration",
                "value" => "Duration",
                "type" => "number"
            ],
            [
                "key" => "duration_type",
                "value" => "Duration type",
                "type" => "dropdown",
                "options" => self::ATTRIBUTE_OPTION_PAIRS
            ]
        ]
    ];

    const ATTRIBUTE_OPTION_WITH_IN = [
        "key" => self::ATTRIBUTE_WITH_IN,
        "value" => self::ATTRIBUTES[self::ATTRIBUTE_WITH_IN],
        "validations" => [
            [
                "key" => "duration",
                "value" => "Duration",
                "type" => "number"
            ],
            [
                "key" => "duration_type",
                "value" => "Duration type",
                "type" => "dropdown",
                "options" => self::ATTRIBUTE_OPTION_PAIRS
            ]
        ]
    ];

    const OPERATOR_STAGES_GROUP = [
        "key" => self::GROUP_STAGES,
        "value" => self::GROUPS[self::GROUP_STAGES],
        "operators" => [
            Operators::HAS_MOVED_CANDIDATE => Operators::OPERATORS[Operators::HAS_MOVED_CANDIDATE],
            Operators::HAS_NOT_MOVED_CANDIDATE => Operators::OPERATORS[Operators::HAS_NOT_MOVED_CANDIDATE]
        ],
        "operator_values" => [],
        "attributes" => []
        // "attributes" => [self::ATTRIBUTE_OPTION_WITH_IN]
    ];

    // Only for candidate
    const OPERATOR_CANDIDATE_STAGES_GROUP = [
        "key" => self::GROUP_STAGES,
        "value" => self::GROUPS[self::GROUP_STAGES],
        "operators" => [
            Operators::IS_IN => Operators::OPERATORS[Operators::IS_IN],
            //Operators::IS_NOT_IN => Operators::OPERATORS[Operators::IS_NOT_IN]
        ],
        "operator_values" => [],
        "attributes" => []
        // "attributes" => [self::ATTRIBUTE_OPTION_FOR]
    ];

    const OPERATOR_OFFERS_GROUP = [
        "key" => self::GROUP_OFFERS,
        "value" => self::GROUPS[self::GROUP_OFFERS],
        "operators" => [
            Operators::HAS => Operators::OPERATORS[Operators::HAS],
            Operators::HAS_NOT => Operators::OPERATORS[Operators::HAS_NOT]
        ],
        "operator_values" => Status::List,
        "attributes" => [self::ATTRIBUTE_OPTION_WITH_IN]
    ];

    const OPERATOR_FORMS_GROUP = [
        "key" => self::GROUP_FORM,
        "value" => self::GROUPS[self::GROUP_FORM],
        "operators" => [
            Operators::HAS => Operators::OPERATORS[Operators::HAS],
            Operators::HAS_NOT => Operators::OPERATORS[Operators::HAS_NOT]
        ],
        "operator_values" => FormEnum::FormListStatus,
        "attributes" => [self::ATTRIBUTE_OPTION_WITH_IN]
    ];

    const OPERATOR_QUESTIONNAIRE_GROUP = [
        "key" => self::GROUP_QUESTIONNAIRE,
        "value" => self::GROUPS[self::GROUP_QUESTIONNAIRE],
        "operators" => [
            Operators::HAS => Operators::OPERATORS[Operators::HAS],
            // Operators::HAS_NOT_COMPLETED => Operators::OPERATORS[Operators::HAS_NOT_COMPLETED]
        ],
        "operator_values" => [],
        "attributes" => []
        // "attributes" => [self::ATTRIBUTE_OPTION_WITH_IN]
    ];

    const OPERATOR_VIDEO_ASSESSMENT_GROUP = [
        "key" => self::GROUP_VIDEO_ASSESSMENT,
        "value" => self::GROUPS[self::GROUP_VIDEO_ASSESSMENT],
        "operators" => [
            Operators::HAS => Operators::OPERATORS[Operators::HAS],
            // Operators::HAS_NOT_COMPLETED => Operators::OPERATORS[Operators::HAS_NOT_COMPLETED]
        ],
        "operator_values" => [],
        "attributes" => []
        // "attributes" => [self::ATTRIBUTE_OPTION_WITH_IN]
    ];

    const OPERATOR_INTERVIEW_GROUP = [
        "key" => self::GROUP_INTERVIEW,
        "value" => self::GROUPS[self::GROUP_INTERVIEW],
        "operators" => [
            Operators::HAS => Operators::OPERATORS[Operators::HAS],
            // Operators::HAS_NOT_COMPLETED => Operators::OPERATORS[Operators::HAS_NOT_COMPLETED]
        ],
        "operator_values" => [],
        "attributes" => []
        // "attributes" => [self::ATTRIBUTE_OPTION_WITH_IN]
    ];

    const OPERATOR_EVALUATION_FORM_GROUP = [
        "key" => self::GROUP_EVALUATION_FORM,
        "value" => self::GROUPS[self::GROUP_EVALUATION_FORM],
        "operators" => [
            Operators::HAS => Operators::OPERATORS[Operators::HAS],
            // Operators::HAS_NOT_COMPLETED => Operators::OPERATORS[Operators::HAS_NOT_COMPLETED]
        ],
        "operator_values" => [],
        "attributes" => []
        // "attributes" => [self::ATTRIBUTE_OPTION_WITH_IN]
    ];

    const OPERATOR_CANDIDATE_RATING_GROUP = [
        "key" => self::GROUP_CANDIDATE_RATING,
        "value" => self::GROUPS[self::GROUP_CANDIDATE_RATING],
        "operators" => [
            Operators::HAS => Operators::OPERATORS[Operators::HAS],
            // Operators::HAS_NOT_COMPLETED => Operators::OPERATORS[Operators::HAS_NOT_COMPLETED]
        ],
        "operator_values" => [],
        "attributes" => []
        // "attributes" => [self::ATTRIBUTE_OPTION_WITH_IN]
    ];

    const OPERATOR_PUSH_TO_HRMS_GROUP = [
        "key" => self::GROUP_PUSH_TO_HRMS,
        "value" => self::GROUPS[self::GROUP_PUSH_TO_HRMS],
        "operators" => [
            Operators::HAS => Operators::OPERATORS[Operators::HAS],
            // Operators::HAS_NOT_COMPLETED => Operators::OPERATORS[Operators::HAS_NOT_COMPLETED]
        ],
        "operator_values" => [],
        "attributes" => []
        // "attributes" => [self::ATTRIBUTE_OPTION_WITH_IN]
    ];

    const OPERATOR_PROFILE_GROUP = [
        "key" => self::GROUP_PROFILE,
        "value" => self::GROUPS[self::GROUP_PROFILE],
        "operators" => [
            Operators::HAS => Operators::OPERATORS[Operators::HAS]
        ],
        "operator_values" => [],
        "attributes" => []
        // "attributes" => [self::ATTRIBUTE_OPTION_WITH_IN]
    ];

    const OPERATOR_TASK_STATUS_GROUP = [
        "key" => self::GROUP_TASK_STATUS,
        "value" => self::GROUPS[self::GROUP_TASK_STATUS],
        "operators" => [
            Operators::IS => Operators::OPERATORS[Operators::IS],
            // Operators::IS_NOT => Operators::OPERATORS[Operators::IS_NOT]
        ],
        "operator_values" => [],
        "attributes" => []
        // "attributes" => [self::ATTRIBUTE_OPTION_WITH_IN]
    ];
}
