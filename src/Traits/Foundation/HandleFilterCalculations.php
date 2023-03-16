<?php

namespace TaskManagement\Traits\Foundation;

use TaskManagement\Constants\Groups;
use TaskManagement\Constants\Operators;

trait HandleFilterCalculations
{
    /**
     * @param array $filters
     * @param $responsibility
     * @param $relation
     * @returns array
     */
    public function handleFilterCalculation(array $filters, $responsibility, $relation): array
    {
        $equation = null;
        $is_grouped = false;
        if (empty($filters)) {
            return [$equation, !$is_grouped];
        }

        try {
            foreach ($filters as $key => $filter) {
                if ($filter['is_grouped'] && !$is_grouped) {
                    $equation .= "(";
                    $is_grouped = true;
                }

                switch ($filter['filter_group']) {
                    case Groups::FILTER_GROUP_CANDIDATE:
                        $isFilterMatched = $this->handleCandidateFilter($filter, $relation->candidate->getTMValues());
                        break;

                    default:
                        $isFilterMatched = $this->handleProfileFilter($filter, ['candidate.ai_matching' => 50]);
                        break;
                }

                $nextFilter = array_key_exists($key+1, $filters) ? $filters[$key+1] : null;
                $equation .= $isFilterMatched;
                if ((empty($nextFilter) || !$nextFilter['is_grouped']) && $filter['is_grouped'] && $is_grouped) {
                    $equation .= ")";
                    $is_grouped = false;
                }

                if ($key != count($filters) -1) {
                    if ($nextFilter['main_operator'] == Operators::AND) {
                        $equation .= "&&";
                    } else if ($nextFilter['main_operator'] == Operators::OR) {
                        $equation .= "||";
                    }
                }
            }

            return [$equation, eval("return ".$equation.";")];
        } catch (\Exception $exception) {
            return [null, false];
        }
    }

    /**
     * @param $filter
     * @param array $values
     * @return int
     */
    private function handleProfileFilter($filter, array $values): int
    {
        $filter = (object)$filter;
        $value = $values[$filter->filter_key];
        switch ($filter->filter_operator) {
            case Operators::GREATER_THAN:
                $isValid = $value > $filter->filter_value;
                break;

            case Operators::LESS_THAN:
                $isValid = $value < $filter->filter_value;
                break;

            case Operators::EQUAL_TO:
                $isValid = $value == $filter->filter_value;
                break;

            case Operators::NOT_EQUAL:
                $isValid = $value != $filter->filter_value;
                break;

            default:
                list($min, $max) = explode('-', $filter->filter_value);
                $isValid = $min < $value && $max > $value;
                break;
        }

        return $isValid ? 1 : 0;
    }

    /**
     * @param $filter
     * @param array $values
     * @return int
     */
    private function handleCandidateFilter($filter, array $values): int
    {
        $filter = (object)$filter;
        $value = $values[$filter->filter_key];
        switch ($filter->filter_operator) {
            case Operators::IS:
                if (is_array($value)) {
                    $isValid = in_array($filter->filter_value, $value);
                } else {
                    $isValid = $value == $filter->filter_value;
                }
                break;

            default:
                if (is_array($value)) {
                    $isValid = !in_array($filter->filter_value, $value);
                } else {
                    $isValid = $value != $filter->filter_value;
                }
                break;
        }

        return $isValid ? 1 : 0;
    }
}
