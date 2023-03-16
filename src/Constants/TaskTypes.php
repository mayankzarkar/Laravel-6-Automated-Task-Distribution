<?php

namespace TaskManagement\Constants;

final class TaskTypes
{
    const TO_DO = 901;
    const OFFER = 902;
    const FORM = 903;
    const PROFILE  = 904;
    const RESUME  = 905;
    const INTRODUCTION  = 906;
    const ATTACHMENT  = 907;
    const EVALUATION  = 908;
    const QUESTIONNAIRE  = 909;
    const VIDEO_ASSESSMENT  = 910;
    const ASSESSMENT_TEST  = 911;
    const MEETINGS  = 912;
    const SHARE  = 913;
    const LOG  = 914;
    const EMAIL  = 915;
    const PUSH_TO_HRMS  = 916;
    const VISA_STATUS  = 917;
    const APPLIED_JOBS  = 918;

    const TYPES = [
        self::TO_DO => 'TO-DO',
        self::OFFER => 'Offer',
        self::FORM => 'Form',
        self::PROFILE => 'Profile',
        self::RESUME => 'Resume',
        self::INTRODUCTION => 'Introduction',
        self::ATTACHMENT => 'Attachment',
        self::EVALUATION => 'Evaluation',
        self::QUESTIONNAIRE => 'Questionnaire',
        self::VIDEO_ASSESSMENT => 'Video assessment',
        self::ASSESSMENT_TEST => 'Assessment test',
        self::MEETINGS => 'Meetings',
        self::SHARE => 'Share',
        self::LOG => 'Log',
        self::EMAIL => 'Email',
        self::PUSH_TO_HRMS => 'Push to htms',
        self::VISA_STATUS => 'Visa status',
        self::APPLIED_JOBS => 'Applied jobs'
    ];

    const TYPES_PAIRS = [
        [
            "key" => self::TO_DO,
            "value" => self::TYPES[self::TO_DO],
            "options" => [],
            "is_disabled" => false
        ],
        [
            "key" => self::OFFER,
            "value" => self::TYPES[self::OFFER],
            "options" => [],
            "is_disabled" => false
        ],
        [
            "key" => self::FORM,
            "value" => self::TYPES[self::FORM],
            "options" => [],
            "is_disabled" => false
        ],
        [
            "key" => self::PROFILE,
            "value" => self::TYPES[self::PROFILE],
            "options" => [],
            "is_disabled" => true
        ],
        [
            "key" => self::RESUME,
            "value" => self::TYPES[self::RESUME],
            "options" => [],
            "is_disabled" => true
        ],
        [
            "key" => self::INTRODUCTION,
            "value" => self::TYPES[self::INTRODUCTION],
            "options" => [],
            "is_disabled" => true
        ],
        [
            "key" => self::ATTACHMENT,
            "value" => self::TYPES[self::ATTACHMENT],
            "options" => [],
            "is_disabled" => true
        ],
        [
            "key" => self::EVALUATION,
            "value" => self::TYPES[self::EVALUATION],
            "options" => [],
            "is_disabled" => true
        ],
        [
            "key" => self::QUESTIONNAIRE,
            "value" => self::TYPES[self::QUESTIONNAIRE],
            "options" => [],
            "is_disabled" => true
        ],
        [
            "key" => self::VIDEO_ASSESSMENT,
            "value" => self::TYPES[self::VIDEO_ASSESSMENT],
            "options" => [],
            "is_disabled" => true
        ],
        [
            "key" => self::ASSESSMENT_TEST,
            "value" => self::TYPES[self::ASSESSMENT_TEST],
            "options" => [],
            "is_disabled" => true
        ],
        [
            "key" => self::MEETINGS,
            "value" => self::TYPES[self::MEETINGS],
            "options" => [],
            "is_disabled" => true
        ],
        [
            "key" => self::SHARE,
            "value" => self::TYPES[self::SHARE],
            "options" => [],
            "is_disabled" => true
        ],
        [
            "key" => self::LOG,
            "value" => self::TYPES[self::LOG],
            "options" => [],
            "is_disabled" => true
        ],
        [
            "key" => self::EMAIL,
            "value" => self::TYPES[self::EMAIL],
            "options" => [],
            "is_disabled" => true
        ],
        [
            "key" => self::PUSH_TO_HRMS,
            "value" => self::TYPES[self::PUSH_TO_HRMS],
            "options" => [],
            "is_disabled" => true
        ],
        [
            "key" => self::VISA_STATUS,
            "value" => self::TYPES[self::VISA_STATUS],
            "options" => [],
            "is_disabled" => true
        ],
        [
            "key" => self::APPLIED_JOBS,
            "value" => self::TYPES[self::APPLIED_JOBS],
            "options" => [],
            "is_disabled" => true
        ]
    ];
}
