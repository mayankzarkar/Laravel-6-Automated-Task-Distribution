<?php

namespace TaskManagement\Http\Resources;

use Illuminate\Http\Request;
use TaskManagement\Constants\Groups;
use TaskManagement\Constants\Actions;
use TaskManagement\Constants\Sources;
use TaskManagement\Constants\Operators;
use Helper\Http\Resources\BaseJsonResource as Resource;

/**
 * @property string $uuid
 * @property string $pipeline_template_uuid
 * @property int $source
 * @property int $source_group
 * @property int $source_operator
 * @property array $source_operator_value
 * @property int $source_attribute
 * @property array $source_attribute_value
 * @property string $source_value
 * @property array $filters
 * @property int $action
 * @property array $action_data
 * @property string|null $parent_uuid
 * @property bool $is_grouped
 * @property string $created_by
 * @property string $created_at
 * @property string $updated_at
 **/
class PipelineTemplateTaskResource extends Resource
{
    /**
     * @Description Transform the resource into an array.
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $source = [
            "key" => $this->source,
            "value" => Sources::SOURCES[$this->source]
        ];

        $parent = $this->parent;
        if ($this->source == Sources::TASK_AND_ACTIONS && $parent && $parent->isActionIsCreateTask()) {
            $source["value"] = $parent->action_data['title'] ?? $source["value"];
        }

        if ($this->is_grouped && $this->source === Sources::TASK_AND_ACTIONS && !empty($parent)) {
            $targetTask = $parent;
            while (!empty($targetTask) && $targetTask->source === Sources::TASK_AND_ACTIONS) {
                $targetTask = $targetTask->parent;
            }

            if (!empty($targetTask) && $targetTask->isActionIsCreateTask()) {
                $source["value"] = $targetTask->action_data['title'] ?? $source["value"];
            }
        }

        return [
            'uuid' => $this->uuid,
            'pipeline_template_uuid' => $this->pipeline_template_uuid,
            'source' => $source,
            'source_group' => [
                "key" => $this->source_group,
                "value" => Groups::GROUPS[$this->source_group]
            ],
            'source_operator' => [
                "key" => $this->source_operator,
                "value" => Operators::OPERATORS[$this->source_operator]
            ],
            $this->mergeWhen(is_int($this->source_operator_value), function () {
                return [
                    'source_operator_value' => $this->getSourceOperatorValue()
                ];
            }),
            'source_value' => $this->getSourceValue(),
            $this->mergeWhen(!empty($this->source_attribute), function () {
                return [
                    "source_attribute" => [
                        "key" => $this->source_attribute,
                        "value" => Groups::ATTRIBUTES[$this->source_attribute]
                    ],
                    'source_attribute_value' => $this->getSourceAttributeValue()
                ];
            }),
            'filters' => $this->getFilters(),
            'action' => [
                "key" => $this->action,
                "value" => Actions::ACTIONS[$this->action]
            ],
            'action_data' => $this->getActionData(),
            'parent_uuid' => $this->parent_uuid,
            'is_grouped' => is_bool($this->is_grouped) ? $this->is_grouped : false,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
