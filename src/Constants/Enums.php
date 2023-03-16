<?php

namespace TaskManagement\Constants;

final class Enums
{
    const SYSTEM  = 0;
    const RECRUITER  = 1;
    const EMPLOYEE  = 2;
    const CANDIDATE  = 3;
    const REQUESTER = 4;

    // Product features
    const MANUAL_TASK = 1;

    // Date filter
    const FILTER_CREATED_AT = 1;
    const FILTER_UPDATED_AT = 2;
    const FILTER_START_DATE = 3;
    const FILTER_DUE_DATE = 4;

    // Reminder type
    const REMINDER_TYPE_EMAIL = 1;
    const REMINDER_TYPE_NOTIFICATION = 2;

    //Reminder Frequencies
    const REMINDER_FREQUENCY_DAILY = 1;
    const REMINDER_FREQUENCY_MONTHLY = 3;
    const REMINDER_FREQUENCY_WEEKLY = 2;

    const REMINDER_TYPES = [
        self::REMINDER_TYPE_EMAIL => 'Email',
        self::REMINDER_TYPE_NOTIFICATION => 'Notification'
    ];

    const REMINDER_FREQUENCIES = [
        self::REMINDER_FREQUENCY_DAILY => 'Daily',
        self::REMINDER_FREQUENCY_MONTHLY => 'Monthly',
        self::REMINDER_FREQUENCY_WEEKLY => 'Weekly'
    ];

    const CREATORS = [
        self::SYSTEM => 'System',
        self::RECRUITER => 'Recruiter',
        self::EMPLOYEE => 'Employee',
        self::CANDIDATE => 'Candidate'
    ];

    const RESPONSIBILITY = [
        self::RECRUITER => 'Recruiter',
        self::EMPLOYEE => 'Employee',
//        self::CANDIDATE => 'Candidate'
    ];

    const USER_RESPONSIBILITY = [
        self::RECRUITER => 'Recruiter',
        self::EMPLOYEE => 'Employee',
        self::REQUESTER => 'Requester'
    ];

    const FEATURES = [
        self::MANUAL_TASK => 'Manual Task'
    ];

    const DATE_FILTERS = [
        self::FILTER_CREATED_AT => 'created_at',
        self::FILTER_UPDATED_AT => 'updated_at',
        self::FILTER_START_DATE => 'start_date',
        self::FILTER_DUE_DATE => 'due_date'
    ];
}
