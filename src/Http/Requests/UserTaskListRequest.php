<?php

namespace TaskManagement\Http\Requests;

use Helper\Entities\BaseModel;
use Illuminate\Validation\Rule;
use TaskManagement\Constants\Enums;
use Illuminate\Support\Facades\Date;
use Helper\Http\Requests\BaseRequest;

/**
 * @property int $limit
 * @property int $page
 * @property string $order_by
 * @property string $order
 * @property string|null $query
 * @property int $feature
 * @property string|null $date
 * @property string|null $responsibility_uuid
 * @property string|null $relation_uuid
 * @property array $date_filter
 * @property Date|null $date_range_start
 * @property Date|null $date_range_end
 **/
class UserTaskListRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'limit' => ['required', 'integer', 'max:150'],
            'page' => ['required', 'integer'],
            'order_by' => ['required', 'string'],
            'order' => ['required', 'string', Rule::in([BaseModel::DESC, BaseModel::ASC])],
            'query' => ['nullable', 'string'],
            'feature' => ['required', 'integer', Rule::in(array_keys(Enums::FEATURES))],
            'responsibility_type' => ['nullable', 'integer', Rule::in(array_keys(Enums::RESPONSIBILITY))],
            'responsibility_uuid' => ['bail', 'nullable', 'string', 'uuid'],
            'relation_uuid' => ['nullable', 'string', 'uuid'],
            'date_filter' => ['nullable', 'array'],
            'date_filter.*' => ['integer', Rule::in(array_keys(Enums::DATE_FILTERS))],
            'date_range_start' => ['nullable', Rule::requiredIf(!empty($this->get('date_filter'))), 'date'],
            'date_range_end' => ['nullable', Rule::requiredIf(!empty($this->get('date_range_start'))), 'date', 'after_or_equal:date_range_start']
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'limit' => $this->get('limit') ?? 30,
            'page' => $this->get('page') ?? 1,
            'order_by' => $this->get('orderBy') ?? 'created_at',
            'order' => $this->get('order') ?? BaseModel::DESC,
            'feature' => $this->get('feature') ?? Enums::MANUAL_TASK,
            'query' => $this->get('query') ?? null,
            'date_filter' => $this->get('date_filter') ?? [],
            'date_range_start' => $this->get('date_range_start') ?? null,
            'date_range_end' => $this->get('date_range_end') ?? null,
            'responsibility_type' => $this->get('responsibility_type') ?? null,
            'responsibility_uuid' => $this->get('responsibility_uuid') ?? null,
            'relation_uuid' => $this->get('relation_uuid') ?? null,
        ]);
    }
}
