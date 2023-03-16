<?php

namespace TaskManagement\Observers;

use Illuminate\Support\Str;
use TaskManagement\Foundation\Collection;
use TaskManagement\Entities\UserTaskReminder;

class UserTaskReminderObserver
{
    /**
     * @Description saving function trigger for do action on updated.
     * @param UserTaskReminder $model
     * @return  void
     */
    public function updated(UserTaskReminder $model): void
    {
        if ($model->isDirty('is_processed') && $model->userTask->reminder_configuration['is_recursive']) {
            $userTask = $model->userTask;
            $userTask->due_date = $model->reminder_date;
            Collection::HandleCreateReminder($userTask);
        }
    }

    /**
     * @Description saving function trigger for do action on updating.
     * @param UserTaskReminder $model
     * @return  void
     */
    public function updating(UserTaskReminder $model): void
    {

    }

    /**
     * @Description saving function trigger for do action on created.
     * @param UserTaskReminder $model
     * @return void
     */
    public function created(UserTaskReminder $model): void
    {

    }

    /**
     * @Description saving function trigger for do action on creating.
     * @param UserTaskReminder $model
     * @return void
     */
    public function creating(UserTaskReminder $model): void
    {
        $model->uuid = Str::uuid()->toString();
        $model->is_processed = false;
    }

    /**
     * @Description saving function trigger for do action on deleted.
     * @param UserTaskReminder $model
     * @return void
     */
    public function deleted(UserTaskReminder $model)
    {

    }

    /**
     * @Description saving function trigger for do action on deleting.
     * @param UserTaskReminder $model
     * @return void
     */
    public function deleting(UserTaskReminder $model): void
    {

    }

    /**
     * @Description saving function trigger for do action on restored.
     * @param UserTaskReminder $model
     * @return void
     */
    public function restored(UserTaskReminder $model): void
    {

    }

    /**
     * @Description saving function trigger for do action on restoring.
     * @param UserTaskReminder $model
     * @return void
     */
    public function restoring(UserTaskReminder $model): void
    {

    }
}
