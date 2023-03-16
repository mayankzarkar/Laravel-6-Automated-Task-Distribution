<?php

namespace TaskManagement\Traits;

use TaskManagement\Constants\Groups;
use TaskManagement\Constants\Sources;
use TaskManagement\Constants\Operators;
use candidate\service\Entities\Candidate;

trait FilterTrait
{
    /**
     * @param int $source
     * @param int $source_group
     * @return array
     */
    public static function GetFilters(int $source, int $source_group): array
    {
        $response = [
            'main_operators' => [],
            'filter_groups' => []
        ];

        // Add filter for source group
        switch ($source_group) {
            case Groups::GROUP_PROFILE:
                $response['filter_groups'][] = [
                    "key" => Groups::FILTER_GROUP_PROFILE,
                    "value" => Groups::FILTER_GROUPS[Groups::FILTER_GROUP_PROFILE],
                    "properties" => [
                        "candidate.ai_matching" => [
                            "value" => "A.I. Matching",
                            "options" => [
                                "type" => "string"
                            ],
                            "class" => null
                        ]
                    ],
                    "operators" => Operators::PROFILE_OPERATORS
                ];
                break;
        }

        // Add filter for source
        switch ($source) {
            case Sources::CANDIDATE_AND_ACTIONS:
                $response['main_operators'] = Operators::CANDIDATE_FILTER_OPERATORS;
                $response['filter_groups'][] = [
                    "key" => Groups::FILTER_GROUP_CANDIDATE,
                    "value" => Groups::FILTER_GROUPS[Groups::FILTER_GROUP_CANDIDATE],
                    "properties" => Candidate::GetTMProperties(),
                    "operators" => Operators::PROPERTY_OPERATORS
                ];
                break;

            case Sources::EMPLOYEE_AND_ACTIONS:
            case Sources::RECRUITER_AND_ACTIONS:
                $response['main_operators'] = Operators::FILTER_OPERATORS;
                break;
        }

        return $response;
    }
}
