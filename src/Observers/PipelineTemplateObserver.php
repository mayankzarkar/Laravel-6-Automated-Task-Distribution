<?php

namespace TaskManagement\Observers;

use Account\Foundation\Collection;
use Illuminate\Support\Str;
use TaskManagement\Entities\PipelineTemplate;

class PipelineTemplateObserver
{
    /**
     * @Description saving function trigger for do action on updated.
     * @param PipelineTemplate $model
     * @return  void
     */
    public function updated(PipelineTemplate $model): void
    {

    }

    /**
     * @Description saving function trigger for do action on updating.
     * @param PipelineTemplate $model
     * @return void
     */
    public function updating(PipelineTemplate $model): void
    {

    }

    /**
     * @Description saving function trigger for do action on created.
     * @param PipelineTemplate $model
     * @return void
     */
    public function created(PipelineTemplate $model): void
    {

    }

    /**
     * @Description saving function trigger for do action on creating.
     * @param PipelineTemplate $model
     * @return void
     */
    public function creating(PipelineTemplate $model): void
    {
        $model->uuid = Str::uuid()->toString();
        $model->account_uuid = Collection::get_account();
        $model->is_published = false;
    }

    /**
     * @Description saving function trigger for do action on deleted.
     * @param PipelineTemplate $model
     * @return void
     */
    public function deleted(PipelineTemplate $model)
    {

    }

    /**
     * @Description saving function trigger for do action on deleting.
     * @param PipelineTemplate $model
     * @return void
     */
    public function deleting(PipelineTemplate $model): void
    {
        $model->tasks()->delete();
    }

    /**
     * @Description saving function trigger for do action on restored.
     * @param PipelineTemplate $model
     * @return void
     */
    public function restored(PipelineTemplate $model): void
    {

    }

    /**
     * @Description saving function trigger for do action on restoring.
     * @param PipelineTemplate $model
     * @return void
     */
    public function restoring(PipelineTemplate $model): void
    {

    }
}
