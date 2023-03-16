<?php

namespace TaskManagement\Entities;

use Carbon\Carbon;
use User\Entities\User;
use formbuilder\Entities\Offer;
use TaskManagement\Constants\Enums;
use TaskManagement\Constants\Groups;
use TaskManagement\Constants\Actions;
use formbuilder\Entities\FormRequest;
use TaskManagement\Constants\Operators;
use Jenssegers\Mongodb\Relations\HasOne;
use Jenssegers\Mongodb\Relations\BelongsTo;
use candidate\service\Entities\User as Candidate;
use TaskManagement\Traits\Foundation\HandleFilterCalculations;
use TaskManagement\Observers\OngoingPipelineTemplateTaskObserver;

/**
 * @property string $uuid
 * @property int $responsibility_type
 * @property string $responsibility_uuid
 * @property int $responsibility_group_type
 * @property string $responsibility_group_uuid
 * @property string $pipeline_template_uuid
 * @property string $relation_uuid
 * @property int $source
 * @property int $source_group
 * @property int $source_operator
 * @property int $source_operator_value
 * @property int $source_attribute
 * @property array $source_attribute_value
 * @property string $source_value
 * @property array $filters
 * @property string $filter_expression
 * @property bool $is_filter_matched
 * @property int $action
 * @property array $action_data
 * @property string $parent_uuid
 * @property int $status
 * @property string|null $reference_uuid
 * @property bool $is_grouped
 *
 * @method static self getFirstRow(string $uuid = null ,string $account_uuid = null ,string $company_uuid = null ,array $extraWhere = [])
 * @method static self[] getListRow(array $uuid ,string $account_uuid = null ,string $company_uuid = null ,array $extraWhere = [])
 *
 **/
class OngoingPipelineTemplateTask extends BaseModel
{
    use HandleFilterCalculations;

    const STATUS_IN_PROGRESS = 1, STATUS_PENDING = 2, STATUS_DRAFT = 3;
    const STATUS_COMPLETED = 4, STATUS_FAILED = 5, STATUS_ERROR = 6;

    protected static function boot()
    {
        parent::boot();
        self::observe(OngoingPipelineTemplateTaskObserver::class);
    }

    /**
     * @uses The table name associated with the model.
     * @var string
     */
    protected $table = 'ongoing_pipeline_template_task';

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
        'responsibility_type',
        'responsibility_uuid',
        'responsibility_group_type',
        'responsibility_group_uuid',
        'pipeline_template_uuid',
        'relation_uuid',
        'source',
        'source_group',
        'source_operator',
        'source_operator_value',
        'source_attribute',
        'source_attribute_value',
        'source_value',
        'filters',
        'filter_expression',
        'is_filter_matched',
        'action',
        'action_data',
        'parent_uuid',
        'status',
        // In case need a reference from another entity
        'reference_uuid',
        'is_grouped'
    ];

    /**
     * Pipeline template task related to the pipeline template
     * @return BelongsTo|Null
     */
    final public function pipelineTemplate(): ?BelongsTo
    {
        return $this->belongsTo(OngoingPipelineTemplate::class, 'pipeline_template_uuid', 'uuid');
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
     * Ongoing Pipeline template task related to the responsibility
     * @return BelongsTo|Null
     */
    final public function responsibility(): ?BelongsTo
    {
        switch ($this->responsibility_type) {
            case Enums::RECRUITER:
            case Enums::EMPLOYEE:
                $relativeClass = User::class;
                break;

            default:
                $relativeClass = Candidate::class;
                break;
        }

        return $this->belongsTo($relativeClass, 'responsibility_uuid', 'uuid');
    }

    /**
     * Ongoing Pipeline template task related to the relation
     * @return BelongsTo|Null
     */
    final public function relation(): ?BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'relation_uuid', 'uuid');
    }

    /**
     * @return bool
     */
    public function isExecutable(): bool
    {
        if ($this->parent && $this->parent->isCompleted()) {
            $validScenarios = [
                ($this->source === $this->parent->source),
                ($this->source_group === $this->parent->source_group),
                ($this->source_operator === $this->parent->source_operator),
                !empty($this->responsibility_uuid)
            ];

            // Check for multiple create task action
            if ($this->parent->action === Actions::ACTION_CREATE_TASK) {
                $validScenarios[] = $this->parent->action_data['status_uuid'] === $this->source_value;
            } else {
                $validScenarios[] = $this->source_value === $this->parent->source_value;
            }

            if (!in_array(false, $validScenarios)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->status === self::STATUS_ERROR;
    }

    /**
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeExecuted($query)
    {
        return $query->whereIn('status', [self::STATUS_COMPLETED, self::STATUS_IN_PROGRESS]);
    }

    /**
     * @return bool
     */
    public function isMainTask(): bool
    {
        if (empty($this->parent_uuid)) {
            return true;
        }

        $mainTask = false;
        $target = $this;
        while (!$mainTask) {
            if (empty($target->parent_uuid) && !$target->is_grouped) {
                $mainTask = true;
            }

            $target = $target->parent;
        }

        return $mainTask;
    }

    /**
     * ==================================================================
     * Handling the business logics
     * ==================================================================
     *
     * @return self
     */
    public function markAsInProgress(): self
    {
        $this->status = self::STATUS_IN_PROGRESS;

        return $this;
    }

    /**
     * @return self
     */
    public function markAsDraft(): self
    {
        $this->status = self::STATUS_DRAFT;

        return $this;
    }

    /**
     * @return self
     */
    public function markAsPending(): self
    {
        $this->status = self::STATUS_PENDING;

        return $this;
    }

    /**
     * @return self
     */
    public function markAsCompleted(): self
    {
        $this->status = self::STATUS_COMPLETED;

        return $this;
    }

    /**
     * @return self
     */
    public function markAsFailed(): self
    {
        $this->status = self::STATUS_FAILED;

        return $this;
    }

    /**
     * @return self
     */
    public function markAsError(): self
    {
        $this->status = self::STATUS_ERROR;

        return $this;
    }

    /**
     * @return self
     */
    public function handleFilters(): self
    {
        list($this->filter_expression, $this->is_filter_matched) = $this->handleFilterCalculation($this->filters, $this->responsibility, $this->relation);

        return $this;
    }

    /**
     * ==================================================================
     * Handling the scope for queries
     * ==================================================================
     *
     * @param $query
     * @return mixed
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * @param $query
     * @param string $responsibility_uuid
     * @return mixed
     */
    public function scopeResponsibility($query, string $responsibility_uuid)
    {
        return $query->where('responsibility_uuid', $responsibility_uuid);
    }

    /**
     * @param $query
     * @param string $relation_uuid
     * @return mixed
     */
    public function scopeRelation($query, string $relation_uuid)
    {
        return $query->where('relation_uuid', $relation_uuid);
    }

    /**
     * @param $query
     * @param string $responsibility_group_uuid
     * @return mixed
     */
    public function scopeResponsibilityGroup($query, string $responsibility_group_uuid)
    {
        return $query->where('responsibility_group_uuid', $responsibility_group_uuid);
    }

    /**
     * @param $query
     * @param string $reference_uuid
     * @return mixed
     */
    public function scopeReference($query, string $reference_uuid)
    {
        return $query->where('reference_uuid', $reference_uuid);
    }

    /**
     * @param $query
     * @param int $source
     * @return mixed
     */
    public function scopeSource($query, int $source)
    {
        return $query->where('source', $source);
    }

    /**
     * @param $query
     * @param int $source_group
     * @return mixed
     */
    public function scopeSourceGroup($query, int $source_group)
    {
        return $query->where('source_group', $source_group);
    }

    /**
     * @param $query
     * @param string $source_value
     * @return mixed
     */
    public function scopeSourceValue($query, string $source_value)
    {
        return $query->where('source_value', $source_value);
    }

    /**
     * @param $query
     * @param int $source_operator
     * @return mixed
     */
    public function scopeSourceOperator($query, int $source_operator)
    {
        return $query->where('source_operator', $source_operator);
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
     * @return bool
     */
    public function isValid(): bool
    {
        $valid = true;
        if (!empty($this->source_attribute)) {
            $valid = $this->validateSourceAttribute();
        }

        return $valid;
    }

    /**
     * @return array
     */
    public function validateSourceAttributeDates(): array
    {
        switch ($this->source_group) {
            case Groups::GROUP_OFFERS:
                $offer = Offer::findByPk($this->reference_uuid);
                $date = $offer->job->created_at;
                break;

            case Groups::GROUP_FORM:
                $form = FormRequest::findByPk($this->reference_uuid);
                $date = $form->created_at;
                break;
        }

        $dateTime = Carbon::parse($date);
        $now = Carbon::now();
        switch ($this->source_attribute_value['duration_type']) {
            case Groups::ATTRIBUTE_OPTION_HOURS:
                $dateTime->addHours($this->source_attribute_value["duration"]);
                break;

            case Groups::ATTRIBUTE_OPTION_DAYS:
                $dateTime->startOfDay()->addDays($this->source_attribute_value["duration"]);
                break;

            case Groups::ATTRIBUTE_OPTION_WEEKS:
                $dateTime->startOfDay()->addWeeks($this->source_attribute_value["duration"]);
                break;

            default:
                $dateTime->startOfDay()->addMonths($this->source_attribute_value["duration"]);
                break;
        }

        return [$dateTime, $now];
    }

    /**
     * @return bool
     */
    private function validateSourceAttribute(): bool
    {
        list($dateTime, $now) = $this->validateSourceAttributeDates();
        if (in_array($this->source_operator, [Operators::HAS, Operators::IS_IN])) {
            return $dateTime >= $now;
        }

        return $dateTime <= $now;
    }
}
