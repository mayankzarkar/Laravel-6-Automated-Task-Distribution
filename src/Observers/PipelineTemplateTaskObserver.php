<?php

namespace TaskManagement\Observers;

use Illuminate\Support\Str;
use TaskManagement\Traits\ActionTrait;
use TaskManagement\Entities\PipelineTemplateTask;

class PipelineTemplateTaskObserver
{
    use ActionTrait;

    /**
     * @Description saving function trigger for do action on updated.
     * @param PipelineTemplateTask $model
     * @return  void
     */
    public function updated(PipelineTemplateTask $model): void
    {
        $this->updatePipelineDate($model);
    }

    /**
     * @Description saving function trigger for do action on updating.
     * @param PipelineTemplateTask $model
     * @return void
     */
    public function updating(PipelineTemplateTask $model): void
    {
        $this->formatData($model);
    }

    /**
     * @Description saving function trigger for do action on created.
     * @param PipelineTemplateTask $model
     * @return void
     */
    public function created(PipelineTemplateTask $model): void
    {
        $this->updatePipelineDate($model);
    }

    /**
     * @Description saving function trigger for do action on creating.
     * @param PipelineTemplateTask $model
     * @return void
     */
    public function creating(PipelineTemplateTask $model): void
    {
        $model->uuid = Str::uuid()->toString();
        $this->formatData($model);
    }

    /**
     * @Description saving function trigger for do action on deleted.
     * @param PipelineTemplateTask $model
     * @return void
     */
    public function deleted(PipelineTemplateTask $model)
    {

    }

    /**
     * @Description saving function trigger for do action on deleting.
     * @param PipelineTemplateTask $model
     * @return void
     */
    public function deleting(PipelineTemplateTask $model): void
    {
        if (!empty($model->parent) && !empty($model->child)) {
            $model->child->update(['parent_uuid' => $model->parent->uuid]);
        } elseif (empty($model->parent) && !empty($model->child)) {
            $model->child->update(['parent_uuid' => null]);
        }
    }

    /**
     * @Description saving function trigger for do action on restored.
     * @param PipelineTemplateTask $model
     * @return void
     */
    public function restored(PipelineTemplateTask $model): void
    {

    }

    /**
     * @Description saving function trigger for do action on restoring.
     * @param PipelineTemplateTask $model
     * @return void
     */
    public function restoring(PipelineTemplateTask $model): void
    {

    }

    /**
     * @Description format the data before inserting
     * @param PipelineTemplateTask $model
     */
    private function formatData(PipelineTemplateTask $model): void
    {
        $model->source = $model->source + 0;
        $model->source_group = $model->source_group + 0;
        $model->source_operator = $model->source_operator + 0;
        $model->action = $model->action + 0;
        $model->filters = array_map(function ($filter) {
            return [
                "main_operator" => (int)$filter["main_operator"],
                "filter_group" => (int)$filter["filter_group"],
                "filter_key" => $filter["filter_key"],
                "filter_operator" => (int)$filter["filter_operator"],
                "filter_value" => $filter["filter_value"],
                "is_grouped" => (bool)$filter["is_grouped"]
            ];
        }, $model->filters ?: []);

        if (!empty($model->action_data)) {
            $actions = array_keys(self::GetActionOptions($model->action));
            $actionData = [];
            foreach ($model->action_data as $key => $value) {
                if (!in_array($key, $actions)) {
                    continue;
                }

                $actionData[$key] = $value;
            }
            $model->action_data = $actionData;
        }
    }

    /**
     * @param PipelineTemplateTask $model
     */
    private function updatePipelineDate(PipelineTemplateTask $model): void
    {
        $template = $model->pipelineTemplate;
        $template->updated_at = $model->updated_at;
        $template->save();
    }
}
