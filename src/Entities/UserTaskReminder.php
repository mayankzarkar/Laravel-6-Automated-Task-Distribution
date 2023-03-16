<?php

namespace TaskManagement\Entities;

use Carbon\Carbon;
use Jenssegers\Mongodb\Relations\BelongsTo;
use TaskManagement\Observers\UserTaskReminderObserver;

/**
 * @property string $uuid
 * @property string $user_task_uuid
 * @property boolean $is_processed
 * @property string $reminder_date
 *
 * @method static self getFirstRow(string $uuid = null ,string $account_uuid = null ,string $company_uuid = null ,array $extraWhere = [])
 * @method static self[] getListRow(array $uuid ,string $account_uuid = null ,string $company_uuid = null ,array $extraWhere = [])
 *
 **/
class UserTaskReminder extends BaseModel
{
    protected static function boot()
    {
        parent::boot();
        self::observe(UserTaskReminderObserver::class);
    }

    /**
     * @uses The table name associated with the model.
     * @var string
     */
    protected $table = 'user_task_reminder';

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
        'user_task_uuid',
        'reminder_date'
    ];

    /**
     *  Reminder related to the user task
     * @return BelongsTo|Null
     */
    final public function userTask(): ?BelongsTo
    {
        return $this->belongsTo(UserTask::class,'user_task_uuid', 'uuid');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopePending($query)
    {
        return $query->where('is_processed', false);
    }

    /**
     * @return mixed
     */
    public static function GetPendingReminder()
    {
        return self::pending()
            ->where('reminder_date', '<=' , Carbon::now()->format('Y-m-d H:i:s'))
            ->get();
    }

    /**
     * @return self
     */
    public function processed(): self
    {
        $this->is_processed = true;

        return $this;
    }
}
