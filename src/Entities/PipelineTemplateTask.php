<?php

namespace TaskManagement\Entities;

use User\Entities\User;
use Lookup\Entities\TaskStatus;
use formbuilder\Entities\Template;
use TaskManagement\Constants\Groups;
use TaskManagement\Constants\Sources;
use TaskManagement\Constants\Actions;
use TaskManagement\Constants\TaskTypes;
use Pipeline\Service\Entities\V2\Stage;
use TaskManagement\Constants\Operators;
use Jenssegers\Mongodb\Relations\HasOne;
use candidate\service\Entities\Candidate;
use User\Http\Resources\UserListResource;
use Jenssegers\Mongodb\Relations\BelongsTo;
use TaskManagement\Observers\PipelineTemplateTaskObserver;
use employee\Http\Resources\Employee\EmployeeListResource;

/**
 * @property string $uuid
 * @property string $pipeline_template_uuid
 * @property int $source
 * @property int $source_group
 * @property int $source_operator
 * @property int $source_operator_value
 * @property int $source_attribute
 * @property array $source_attribute_value
 * @property string $source_value
 * @property array $filters
 * @property int $action
 * @property array $action_data
 * @property string $parent_uuid
 * @property bool $is_grouped
 *
 * @method static self getFirstRow(string $uuid = null ,string $account_uuid = null ,string $company_uuid = null ,array $extraWhere = [])
 * @method static self[] getListRow(array $uuid ,string $account_uuid = null ,string $company_uuid = null ,array $extraWhere = [])
 *
 **/
class PipelineTemplateTask extends BaseModel
{
    protected static function boot()
    {
        parent::boot();
        self::observe(PipelineTemplateTaskObserver::class);
    }

    /**
     * @uses The table name associated with the model.
     * @var string
     */
    protected $table = 'pipeline_template_task';

    /**
     * @uses The primary key for the model.
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * @uses The primary key type.
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @uses The columns name for user task table
     * @var array
     */
    protected $fillable = [
        'pipeline_template_uuid',
        'source',
        'source_group',
        'source_operator',
        'source_operator_value',
        'source_attribute',
        'source_attribute_value',
        'source_value',
        'filters',
        'action',
        'action_data',
        'parent_uuid',
        'is_grouped'
    ];

    /**
     * Pipeline template task related to the pipeline template
     * @return BelongsTo|Null
     */
    final public function pipelineTemplate(): ?BelongsTo
    {
        return $this->belongsTo(PipelineTemplate::class, 'pipeline_template_uuid', 'uuid');
    }

    /**
     * Pipeline template task related to the parent entity
     * @return BelongsTo|Null
     */
    final public function parent(): ?BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_uuid', 'uuid');
    }

    /**
     * Pipeline template task related to the child entity
     * @return HasOne|Null
     */
    final public function child(): ?HasOne
    {
        return $this->hasOne(self::class, 'parent_uuid', 'uuid');
    }

    /**
     * @param $query
     * @param int $sourceGroup
     * @return mixed
     */
    public function scopeSourceGroup($query, int $sourceGroup)
    {
        return $query->where('source_group', $sourceGroup);
    }

    /**
     * @param $query
     * @param string $sourceValue
     * @return mixed
     */
    public function scopeSourceValue($query, string $sourceValue)
    {
        return $query->where('source_value', $sourceValue);
    }

    /**
     * @param $query
     * @param string $sourceValue
     * @return mixed
     */
    public function scopeSourceValueNotIn($query, string $sourceValue)
    {
        return $query->whereNotIn('source_value', [$sourceValue]);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeMainTask($query)
    {
        return $query->whereNull('parent_uuid');
    }

    /**
     * @param $query
     * @param int $operator
     * @return mixed
     */
    public function scopeSourceOperator($query, int $operator)
    {
        return $query->where('source_operator', $operator);
    }

    /**
     * @return array|string
     */
    final public function getSourceValue()
    {
        switch ($this->source_group) {
            /*case Groups::GROUP_OFFERS:
            case Groups::GROUP_QUESTIONNAIRE:
            case Groups::GROUP_VIDEO_ASSESSMENT:
            case Groups::GROUP_INTERVIEW:
            case Groups::GROUP_EVALUATION_FORM:
            case Groups::GROUP_CANDIDATE_RATING:
            case Groups::GROUP_PUSH_TO_HRMS:*/
            case Groups::GROUP_STAGES:
                $stage = Stage::findByPk($this->source_value);
                $sourceValue = [
                    "uuid" => $stage->uuid,
                    "name" => $stage->title
                ];
                break;

            case Groups::GROUP_TASK_STATUS:
                $status = TaskStatus::findByPk($this->source_value);
                $sourceValue = [
                    "uuid" => $status->uuid,
                    "name" => $status->name['en'] ?? $status->name['ar'] ?? null
                ];
                break;

            case Groups::GROUP_FORM:
            case Groups::GROUP_OFFERS:
                $template = Template::findByPk($this->source_value);
                $sourceValue = [
                    "uuid" => $template->uuid,
                    "name" => $template->title,
                    "type" => [
                        "uuid" => $template->formType->uuid,
                        "name" => $template->formType->name
                    ]
                ];
                break;

            default:
                $sourceValue = $this->source_value;
                break;
        }

        return $sourceValue;
    }

    /**
     * @return array
     */
    final public function getFilters(): array
    {
        if (empty($this->filters)) {
            return $this->filters;
        }

        $properties = Candidate::GetTMProperties() + [
            "candidate.ai_matching" => [
                "value" => "A.I. Matching",
                "options" => [
                    "type" => "string"
                ],
                "class" => null
            ]
        ];

        return array_map(function ($filter) use ($properties) {
            $filter["main_operator"] = [
                "key" => $filter["main_operator"],
                "value" => Operators::OPERATORS[$filter["main_operator"]]
            ];

            $filter["filter_group"] = [
                "key" => $filter["filter_group"],
                "value" => Groups::FILTER_GROUPS[$filter["filter_group"]]
            ];

            $filter["filter_operator"] = [
                "key" => $filter["filter_operator"],
                "value" => Operators::OPERATORS[$filter["filter_operator"]]
            ];

            if (!in_array($filter['filter_key'], array_keys($properties))) {
                return $filter;
            }

            $target = $properties[$filter['filter_key']];
            $filter["filter_key"] = [
                "key" => $filter["filter_key"],
                "value" => $target["value"]
            ];

            if (empty($target['class'])) {
                return $filter;
            }

            $targetClass = $target['class'];
            $property = $targetClass::findByPk($filter['filter_value']);
            if (empty($property)) {
                return $filter;
            }

            $filter['filter_value'] = [
                "uuid" => $property->uuid,
                "name" => $property->name['en'] ?? $property->title['en'] ?? $property->name ?? $property->title
            ];

            return $filter;
        }, $this->filters);
    }

    /**
     * @return array
     */
    final public function getActionData(): array
    {
        $actionData = $this->action_data;
        switch ($this->action) {
            // case Actions::ACTION_SEND_EMAIL:
            case Actions::ACTION_CREATE_TASK:
                $actionData['type'] = [
                    "key" => $actionData['type'],
                    "value" => TaskTypes::TYPES[$actionData['type']]
                ];

                if (!empty($actionData['responsibility_uuid'])) {
                    $responsibility = User::findByUuid($actionData['responsibility_uuid']);
                    unset($actionData['responsibility_uuid']);
                    $actionData['responsibility'] = $responsibility->isEmployee() ? new EmployeeListResource($responsibility->employee) : new UserListResource($responsibility);
                }

                if (!empty($actionData['status_uuid'])) {
                    $status = TaskStatus::getwithTrashed($actionData['status_uuid']);
                    unset($actionData['status_uuid']);
                    $actionData['status'] = [
                        "uuid" => $status->uuid,
                        "name" => $status->name['en'] ?? $status->name['ar'] ?? null
                    ];
                }

                $actionData['additional_data'] = UserTask::GetAdditionalData($actionData['type']['key'], $actionData['additional_data'] ?? []);
                break;

            case Actions::ACTION_MOVE_CANDIDATE_TO:
                $stage = Stage::findByPk($actionData['stage_uuid']);
                unset($actionData['stage_uuid']);
                $actionData['stage'] = [
                    "uuid" => $stage->uuid,
                    "name" => $stage->title
                ];
                break;
        }

        return $actionData;
    }

    /**
     * @return array
     */
    final public function getSourceAttributeValue(): array
    {
        return [
            "duration" => $this->source_attribute_value["duration"],
            "duration_type" => [
                "key" => $this->source_attribute_value["duration_type"],
                "value" => Groups::ATTRIBUTE_OPTIONS[$this->source_attribute_value["duration_type"]]
            ]
        ];
    }

    /**
     * @return bool
     */
    public function isActionIsCreateTask(): bool
    {
        return $this->action == Actions::ACTION_CREATE_TASK;
    }

    /**
     * @return array|null
     */
    final public function getSourceOperatorValue(): ?array
    {
        if (!is_int($this->source_operator_value)) {
            return null;
        }

        $response = [
            "key" => $this->source_operator_value,
            "value" => ""
        ];
        switch($this->source) {
            case Sources::RECRUITER_AND_ACTIONS:
                $groups = Sources::RECRUITER_SOURCES['source_operator_groups'];
                break;

            case Sources::EMPLOYEE_AND_ACTIONS:
                $groups = Sources::EMPLOYEE_SOURCES['source_operator_groups'];
                break;

            case Sources::TASK_AND_ACTIONS:
                $groups = Sources::TASK_SOURCES['source_operator_groups'];
                break;

            default:
                $groups = Sources::CANDIDATE_SOURCES['source_operator_groups'];
                break;
        }

        if (!empty($groups)) {
            $index = array_search($this->source_group, array_column($groups, "key"));
            $sourceGroup = $groups[$index];
            if (!empty($sourceGroup)) {
                if (empty($sourceGroup['operator_values'])) {
                    return null;
                }

                $response["value"] = $sourceGroup['operator_values'][$this->source_operator_value];
            }
        }

        return $response;
    }
}
