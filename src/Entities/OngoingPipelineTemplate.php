<?php

namespace TaskManagement\Entities;

use Jenssegers\Mongodb\Relations\HasMany;
use Pipeline\Service\Entities\V2\Pipeline;
use Jenssegers\Mongodb\Relations\BelongsTo;
use TaskManagement\Observers\OngoingPipelineTemplateObserver;

/**
 * @property string $uuid
 * @property string $account_uuid
 * @property string $pipeline_uuid
 * @property string $source_pipeline_uuid
 * @property string $title
 *
 * @method static self getFirstRow(string $uuid = null ,string $account_uuid = null ,string $company_uuid = null ,array $extraWhere = [])
 * @method static self[] getListRow(array $uuid ,string $account_uuid = null ,string $company_uuid = null ,array $extraWhere = [])
 *
 **/
class OngoingPipelineTemplate extends BaseModel
{
    protected static function boot()
    {
        parent::boot();
        self::observe(OngoingPipelineTemplateObserver::class);
    }

    /**
     * @uses The table name associated with the model.
     * @var string
     */
    protected $table = 'ongoing_pipeline_template';

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
        'account_uuid',
        'source_pipeline_uuid',
        'pipeline_uuid',
        'title'
    ];

    /**
     * Pipeline template related to the source pipeline
     * @return BelongsTo|Null
     */
    final public function sourcePipeline(): ?BelongsTo
    {
        return $this->belongsTo(PipelineTemplate::class, 'source_pipeline_uuid', 'uuid');
    }

    /**
     * Pipeline template related to the pipeline
     * @return BelongsTo|Null
     */
    final public function pipeline(): ?BelongsTo
    {
        return $this->belongsTo(Pipeline::class, 'pipeline_uuid', 'uuid');
    }

    /**
     * Pipeline template related to the pipeline template tasks
     * @return HasMany
     */
    final public function tasks(): HasMany
    {
        return $this->hasMany(OngoingPipelineTemplateTask::class, 'pipeline_template_uuid', 'uuid');
    }

    /**
     * @param $query
     * @param string $account
     * @return mixed
     */
    public function scopeAccount($query, string $account)
    {
        return $query->where('account_uuid', $account);
    }

    /**
     * @param $query
     * @param string $pipeline
     * @return mixed
     */
    public function scopePipeline($query, string $pipeline)
    {
        return $query->where('pipeline_uuid', $pipeline);
    }
}
