<?php

namespace TaskManagement\Constants;

final class Operators
{
    // Operators
    const IS_IN  = 101;
    const IS_NOT_IN  = 102;
    const HAS  = 103;
    const HAS_NOT  = 104;
    const HAS_MOVED_CANDIDATE = 105;
    const HAS_NOT_MOVED_CANDIDATE = 106;
    const AND = 107;
    const OR = 108;
    const FOR = 109;
    const IS = 110;
    const IS_NOT = 111;
    const GREATER_THAN = 113;
    const LESS_THAN = 114;
    const EQUAL_TO = 115;
    const NOT_EQUAL = 116;
    const IN_BETWEEN = 117;

    const OPERATORS = [
        self::IS_IN => "Is in",
        self::IS_NOT_IN => "Is not in",
        self::HAS => "Has",
        self::HAS_NOT => "Has not",
        self::HAS_MOVED_CANDIDATE => "Has moved candidate",
        self::HAS_NOT_MOVED_CANDIDATE => "Has not moved candidate",
        self::AND => "And",
        self::OR => "Or",
        self::FOR => "For",
        self::IS => "Is",
        self::IS_NOT => "Is not",
        self::GREATER_THAN => "Greater than",
        self::LESS_THAN => "Less than",
        self::EQUAL_TO => "Equal to",
        self::NOT_EQUAL => "Not equal",
        self::IN_BETWEEN => "In between"
    ];

    const FILTER_OPERATORS = [
        self::FOR => self::OPERATORS[self::FOR]
    ];

    const CANDIDATE_FILTER_OPERATORS = [
        self::AND => self::OPERATORS[self::AND],
        self::OR => self::OPERATORS[self::OR]
    ];

    const PROPERTY_OPERATORS = [
        self::IS => self::OPERATORS[self::IS],
        self::IS_NOT => self::OPERATORS[self::IS_NOT]
    ];

    const PROFILE_OPERATORS = [
        self::GREATER_THAN => self::OPERATORS[self::GREATER_THAN],
        self::LESS_THAN => self::OPERATORS[self::LESS_THAN],
        self::EQUAL_TO => self::OPERATORS[self::EQUAL_TO],
        self::NOT_EQUAL => self::OPERATORS[self::NOT_EQUAL],
        self::IN_BETWEEN => self::OPERATORS[self::IN_BETWEEN],
    ];
}
