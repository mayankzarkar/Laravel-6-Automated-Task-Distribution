<?php

namespace TaskManagement\Http\Requests\Pipeline;

use Helper\Entities\BaseModel;
use Illuminate\Validation\Rule;
use Helper\Http\Requests\BaseRequest;
use Pipeline\Service\Rules\V2\PipelineExists;

/**
 * @property string $pipeline_uuid
 * @property int $limit
 * @property int $page
 * @property string $order_by
 * @property string $order
 * @property string|null $query
 **/
class TemplateListRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'pipeline_uuid' => ['required', 'string', new PipelineExists],
            'limit' => ['required', 'integer', 'max:150'],
            'page' => ['required', 'integer'],
            'order_by' => ['required', 'string'],
            'order' => ['required', 'string', Rule::in([BaseModel::DESC, BaseModel::ASC])],
            'query' => ['nullable', 'string'],
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
            'query' => $this->get('query') ?? null
        ]);
    }
}
