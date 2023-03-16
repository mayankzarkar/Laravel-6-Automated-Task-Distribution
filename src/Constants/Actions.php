<?php

namespace TaskManagement\Constants;

final class Actions
{
    // Actions
    const ACTION_CREATE_TASK = 401;
    const ACTION_MOVE_CANDIDATE_TO = 402;
    const ACTION_SEND_EMAIL = 403;

    const ACTIONS = [
        self::ACTION_CREATE_TASK => 'Create task',
        self::ACTION_MOVE_CANDIDATE_TO => 'Move candidate to',
        self::ACTION_SEND_EMAIL => 'Send email'
    ];

    // Send email types
    const SEND_EMAIL_TYPE_CANDIDATE = 601;
//    const SEND_EMAIL_TYPE_RECRUITER = 602;
//    const SEND_EMAIL_TYPE_EMPLOYEE = 603;
    const SEND_EMAIL_TYPE_OTHER = 604;

    const SEND_EMAIL_TYPES = [
        self::SEND_EMAIL_TYPE_CANDIDATE => 'Candidate',
//        self::SEND_EMAIL_TYPE_RECRUITER => 'Recruiter',
//        self::SEND_EMAIL_TYPE_EMPLOYEE => 'Employee',
        self::SEND_EMAIL_TYPE_OTHER => 'Other'
    ];

    const ACTION_CREATE_TASK_RULES = [
        "key" => self::ACTION_CREATE_TASK,
        "value" => Actions::ACTIONS[Actions::ACTION_CREATE_TASK]
    ];

    const ACTION_MOVE_CANDIDATE_TO_RULES = [
        "key" => Actions::ACTION_MOVE_CANDIDATE_TO,
        "value" => Actions::ACTIONS[Actions::ACTION_MOVE_CANDIDATE_TO]
    ];

    const ACTION_SEND_EMAIL_RULES = [
        "key" => Actions::ACTION_SEND_EMAIL,
        "value" => Actions::ACTIONS[Actions::ACTION_SEND_EMAIL]
    ];
}
