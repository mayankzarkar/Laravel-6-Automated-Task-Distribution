<?php

namespace TaskManagement\Http\Requests\Pipeline;

use Illuminate\Validation\Rule;
use TaskManagement\Constants\Groups;
use TaskManagement\Constants\Actions;
use TaskManagement\Traits\FilterTrait;
use TaskManagement\Traits\FilterDataRule;
use TaskManagement\Traits\ActionDataRule;
use TaskManagement\Traits\SourceValueRule;
use TaskManagement\Entities\PipelineTemplate;
use TaskManagement\Rules\PipelineTemplateExists;
use TaskManagement\Traits\AdditionalValidationRule;
use TaskManagement\Rules\PipelineTemplateTaskExists;
use TaskManagement\Rules\PipelineTemplateTaskDuplicate;

/**
 * @property string $uuid
 * @property string $pipeline_template_uuid
 * @property int $source
 * @property int $source_group
 * @property int $source_operator
 * @property int $source_attribute
 * @property array $source_attribute_value
 * @property string $source_value
 * @property array $filters
 * @property int $action
 * @property array $action_data
 * @property string|null $parent_uuid
 **/
class TemplateTaskRequest extends FilterRequest
{
    use ActionDataRule, FilterDataRule, FilterTrait, SourceValueRule, AdditionalValidationRule;
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        $parent_rules = parent::rules();
        $rules = [
            "pipeline_template_uuid" => ['required', 'string', 'uuid', new PipelineTemplateExists],
            "source" => $parent_rules['source'],
            "source_group" => $parent_rules['source_operator_group'],
            "source_operator" => ['required', 'integer'],
            "source_operator_value" => ['nullable', 'integer'],
            "source_attribute" => ['nullable', 'integer'],
            "source_attribute_value" => [Rule::requiredIf(!empty($this->source_attribute)), 'array'],
            "source_value" => ['required', 'string'],
            "filters" => ['nullable', 'array'],
            "action" => ['required', 'integer', Rule::in(array_keys(Actions::ACTIONS))],
            "action_data" => ['required', 'array'],
            "parent_uuid" => [
                'bail',
                $this->validateParentUuid() ? 'required' : 'nullable',
                'string',
                'uuid',
                new PipelineTemplateTaskExists
            ],
            "is_grouped" => ["required", "boolean"]
        ];

        // validating action data
        if ($this->get('action')) {
            $rules = array_merge($rules, $this->validateActionData($this->get('action')));
        }

        // validating source operators
        if ($this->get('source') && $this->get('source_group')) {
            $rules['source_operator'][] = Rule::in(array_keys($this->getSourceContentByGroup("operators")));
            $sourceOperatorValues = array_keys($this->getSourceContentByGroup("operator_values"));
            if (!empty($sourceOperatorValues)) {
                $rules['source_operator_value'][] = "required";
            }
            $rules['source_operator_value'][] = Rule::in($sourceOperatorValues);
            $rules['source_attribute'][] = Rule::in(array_keys($this->getSourceContentByGroup("attributes")));

            // validation source attribute value
            $rules['source_attribute_value.duration'] = [Rule::requiredIf(!empty($this->source_attribute)), "integer"];
            $rules['source_attribute_value.duration_type'] = [Rule::requiredIf(!empty($this->source_attribute)), "integer", Rule::in(array_keys(Groups::ATTRIBUTE_OPTIONS))];

            // validating filter data
            $rules = array_merge($rules, $this->validateFilterData(self::GetFilters($this->get('source'), $this->get('source_group')), $this->get('filters', [])));

            $rules['source_value'] = array_merge($rules['source_value'], $this->validateSourceValue(array_column(parent::GetSourceOperatorGroupsBySource($this->get('source')), "key"), $this->get('source_group')));
        }

        if ($this->getMethod() == "PUT") {
            $rules['uuid'] = ['required', 'string', 'uuid', new PipelineTemplateTaskExists];
            $rules['parent_uuid'][] = new PipelineTemplateTaskDuplicate($this->get('uuid'));
        } else {
            $rules['parent_uuid'][] = new PipelineTemplateTaskDuplicate;
        }

        return $rules;
    }

    /**
     * @param string $option
     * @return array
     */
    private function getSourceContentByGroup(string $option): array
    {
        $source_operator_groups = parent::GetSourceOperatorGroupsBySource($this->get('source'));
        $source_group = $this->get('source_group');
        $index = array_search($source_group, array_column($source_operator_groups, "key"));
        $response = $source_operator_groups[$index][$option];
        if ($option != "attributes") {
            return $response;
        }

        return array_flip(array_column($response, "key"));
    }

    /**
     * @return bool
     */
    private function validateParentUuid(): bool
    {
        $pipelineTemplate = PipelineTemplate::findByPK($this->get('pipeline_template_uuid'));
        if (empty($pipelineTemplate)) {
            return false;
        }

        if ($pipelineTemplate->tasks->count() > 0) {
            $validScenarios = [
                $this->getMethod() == "PUT",
                $this->has("uuid"),
                $pipelineTemplate->tasks->first()->uuid == $this->get("uuid")
            ];

            if (in_array(true, $validScenarios)) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'source_attribute' => $this->get('source_attribute') ?? null,
            'source_attribute_value' => $this->get('source_attribute_value') ?? [],
            'is_grouped' => $this->get('is_grouped') ?? false
        ]);
    }
}
