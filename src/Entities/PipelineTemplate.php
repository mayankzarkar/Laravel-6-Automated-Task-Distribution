<?php

namespace TaskManagement\Entities;

use Illuminate\Support\Collection;
use Jenssegers\Mongodb\Relations\HasMany;
use Pipeline\Service\Entities\V2\Pipeline;
use Jenssegers\Mongodb\Relations\BelongsTo;
use Illuminate\Pagination\LengthAwarePaginator;
use TaskManagement\Observers\PipelineTemplateObserver;
use TaskManagement\Http\Requests\Pipeline\TemplateListRequest;

/**
 * @property string $uuid
 * @property string $account_uuid
 * @property string $pipeline_uuid
 * @property bool $is_published
 * @property string $title
 *
 * @method static self getFirstRow(string $uuid = null ,string $account_uuid = null ,string $company_uuid = null ,array $extraWhere = [])
 * @method static self[] getListRow(array $uuid ,string $account_uuid = null ,string $company_uuid = null ,array $extraWhere = [])
 *
 **/
class PipelineTemplate extends BaseModel
{
    protected static function boot()
    {
        parent::boot();
        self::observe(PipelineTemplateObserver::class);
    }

    /**
     * @uses The table name associated with the model.
     * @var string
     */
    protected $table = 'pipeline_template';

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
        'pipeline_uuid',
        'title',
        'is_published'
    ];

    /**
     * Pipeline template related to the pipeline
     * @return BelongsTo|Null
     */
    final public function pipeline(): ?BelongsTo
    {
        return $this->belongsTo(Pipeline::class,'pipeline_uuid','uuid');
    }

    /**
     * Pipeline template related to the pipeline template tasks
     * @return HasMany
     */
    final public function tasks(): HasMany
    {
        return $this->hasMany(PipelineTemplateTask::class,'pipeline_template_uuid','uuid');
    }

    /**
     * Get all user tasks
     * @param string $account_uuid
     * @param TemplateListRequest $request
     * @param boolean $is_paginate
     * @return Collection|LengthAwarePaginator
     */
    final public static function getPipelineTemplates(string $account_uuid, TemplateListRequest $request, bool $is_paginate = true)
    {
        $query = self::where('account_uuid', $account_uuid)->where('pipeline_uuid', $request->pipeline_uuid);

        if ($query_string = $request->get('query')) {
            $query->where('title', 'LIKE', "%{$query_string}%");
        }

        if ($request->order == BaseModel::DESC)  {
            $query->orderByDesc($request->order_by);
        } else {
            $query->orderBy($request->order_by);
        }

        $query->with("tasks");
        if ($is_paginate) {
            return $query->paginate($request->limit)->onEachSide($request->page);
        }

        return $query->get();
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

    /**
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->is_published;
    }
}
