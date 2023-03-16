<?php

namespace TaskManagement\Observers;

use Illuminate\Support\Str;
use TaskManagement\Entities\OngoingPipelineTemplate;

class OngoingPipelineTemplateObserver
{
    /**
     * @Description saving function trigger for do action on updated.
     * @param OngoingPipelineTemplate $model
     * @return  void
     */
    public function updated(OngoingPipelineTemplate $model): void
    {

    }

    /**
     * @Description saving function trigger for do action on updating.
     * @param OngoingPipelineTemplate $model
     * @return void
     */
    public function updating(OngoingPipelineTemplate $model): void
    {

    }

    /**
     * @Description saving function trigger for do action on created.
     * @param OngoingPipelineTemplate $model
     * @return void
     */
    public function created(OngoingPipelineTemplate $model): void
    {

    }

    /**
     * @Description saving function trigger for do action on creating.
     * @param OngoingPipelineTemplate $model
     * @return void
     */
    public function creating(OngoingPipelineTemplate $model): void
    {
        $model->uuid = Str::uuid()->toString();
    }

    /**
     * @Description saving function trigger for do action on deleted.
     * @param OngoingPipelineTemplate $model
     * @return void
     */
    public function deleted(OngoingPipelineTemplate $model)
    {

    }

    /**
     * @Description saving function trigger for do action on deleting.
     * @param OngoingPipelineTemplate $model
     * @return void
     */
    public function deleting(OngoingPipelineTemplate $model): void
    {
        $model->tasks()->delete();
    }

    /**
     * @Description saving function trigger for do action on restored.
     * @param OngoingPipelineTemplate $model
     * @return void
     */
    public function restored(OngoingPipelineTemplate $model): void
    {

    }

    /**
     * @Description saving function trigger for do action on restoring.
     * @param OngoingPipelineTemplate $model
     * @return void
     */
    public function restoring(OngoingPipelineTemplate $model): void
    {

    }
}
