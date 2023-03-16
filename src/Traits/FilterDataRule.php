<?php

namespace TaskManagement\Traits;

use Illuminate\Validation\Rule;
use TaskManagement\Constants\Operators;
use candidate\service\Entities\Candidate;

trait FilterDataRule
{
    private static $filter_data_key = "filters";
    private static $filter_data_validations = [
        "main_operator" => ["required", "integer"],
        "filter_group" => ["required", "integer"],
        "filter_key" => ["required", "string"],
        "filter_operator" => ["required", "integer"],
        "filter_value" => ["required", "string"],
        "is_grouped" => ["required", "boolean"]
    ];

    /**
     * @param array $filter_data
     * @param array $filters
     * @return array
     */
    public function validateFilterData(array $filter_data, array $filters): array
    {
        $rules = [];
        if (empty($filters)) {
            foreach (static::$filter_data_validations as $field => $validation) {
                $rules[static::$filter_data_key . ".*." . $field] = $validation;
            }

            return $rules;
        }

        foreach ($filters as $key => $value) {
            foreach (static::$filter_data_validations as $field => $validation) {
                switch ($field) {
                    case "filter_group":
                        $validation[] = Rule::in(self::HandleFilterGroup($filter_data));
                        break;

                    case "filter_key":
                        $validation[] = Rule::in(self::HandleFilterKey($filter_data, $value['filter_group']));
                        break;

                    case "filter_operator":
                        $validation[] = Rule::in(self::HandleFilterOperator($filter_data, $value['filter_group']));
                        break;

                    case "filter_value":
                        $validation = self::HandleFilterValue($validation, $filter_data, $value['filter_group'], $value['filter_key'], $value['filter_operator']);
                        break;

                    case "main_operator":
                        $validation[] = Rule::in(self::HandleFilterMain($filter_data));
                        break;
                }

                $rules[static::$filter_data_key . ".{$key}." . $field] = $validation;
            }
        }

        return $rules;
    }

    /**
     * @param array $filter_data
     * @return array
     */
    protected static function HandleFilterMain(array $filter_data): array
    {
        return array_keys($filter_data['main_operators']);
    }

    /**
     * @param array $filter_data
     * @return array
     */
    protected static function HandleFilterGroup(array $filter_data): array
    {
        return array_column($filter_data['filter_groups'], "key");
    }

    /**
     * @param array $filter_data
     * @param int|null $filter_group
     * @return array
     */
    protected static function HandleFilterKey(array $filter_data, ?int $filter_group): array
    {
        $valid_filter_groups = self::HandleFilterGroup($filter_data);
        $filter_groups = $filter_data['filter_groups'];
        if (is_null($filter_group) || !in_array($filter_group, $valid_filter_groups)) {
            $properties = array_column($filter_groups, "properties");
            if (empty($properties)) {
                return [];
            }

            return array_keys(array_merge(...$properties));
        }

        $properties = $filter_groups[array_search($filter_group, array_column($filter_groups, "key"))]['properties'];
        return array_keys($properties);
    }

    /**
     * @param array $filter_data
     * @param int|null $filter_group
     * @return array
     */
    protected static function HandleFilterOperator(array $filter_data, ?int $filter_group): array
    {
        $valid_filter_groups = self::HandleFilterGroup($filter_data);
        $filter_groups = $filter_data['filter_groups'];
        if (is_null($filter_group) || !in_array($filter_group, $valid_filter_groups)) {
            $operators = array_column($filter_groups, "operators");
            if (empty($operators)) {
                return [];
            }

            return array_keys(array_merge(...$operators));
        }

        $operators = $filter_groups[array_search($filter_group, array_column($filter_groups, "key"))]['operators'];
        return array_keys($operators);
    }

    /**
     * @param array $validations
     * @param array $filter_data
     * @param int|null $filter_group
     * @param string|null $filter_key
     * @return array
     */
    protected static function HandleFilterValue(array $validations, array $filter_data, ?int $filter_group, ?string $filter_key, ?int $filter_operator): array
    {
        $keys = self::HandleFilterKey($filter_data, $filter_group);
        $operators = self::HandleFilterOperator($filter_data, $filter_group);
        if (!in_array($filter_key, $keys) || !in_array($filter_operator, $operators)) {
            return $validations;
        }

        $validation_data = Candidate::GetTMValidations() + ["candidate.ai_matching" => ($filter_operator == Operators::IN_BETWEEN) ? "regex:/^((100|[1-9][0-9]?)[-]{1}(100|[1-9][0-9]?))$/" : ["required", "integer", "max:100"]];
        return $validation_data[$filter_key];
    }
}
