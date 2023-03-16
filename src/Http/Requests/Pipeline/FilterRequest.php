<?php

namespace TaskManagement\Http\Requests\Pipeline;

use Illuminate\Validation\Rule;
use Helper\Http\Requests\BaseRequest;
use TaskManagement\Constants\Sources;

/**
 * @property int $source
 * @property int $source_operator_group
 **/
class FilterRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'source' => ['required', 'integer', Rule::in([Sources::CANDIDATE_AND_ACTIONS, Sources::TASK_AND_ACTIONS])],
            'source_operator_group' => ['required', 'integer', Rule::in(array_column(self::GetSourceOperatorGroupsBySource($this->get('source')), 'key'))]
        ];
    }

    /**
     * @param int $source
     * @return array
     */
    public static function GetSourceOperatorGroupsBySource(int $source): array
    {
        switch($source) {
            case Sources::RECRUITER_AND_ACTIONS:
                $response = Sources::RECRUITER_SOURCES['source_operator_groups'];
                break;

            case Sources::EMPLOYEE_AND_ACTIONS:
                $response = Sources::EMPLOYEE_SOURCES['source_operator_groups'];
                break;

            case Sources::TASK_AND_ACTIONS:
                $response = Sources::TASK_SOURCES['source_operator_groups'];
                break;

            default:
                $response = Sources::CANDIDATE_SOURCES['source_operator_groups'];
                break;
        }

        return $response;
    }
}
