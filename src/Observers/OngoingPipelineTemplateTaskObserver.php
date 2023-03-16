<?php

namespace TaskManagement\Observers;

use Illuminate\Support\Str;
use formbuilder\Entities\Offer;
use TaskManagement\Constants\Groups;
use Illuminate\Support\Facades\Queue;
use formbuilder\Entities\FormRequest;
use TaskManagement\Constants\Operators;
use TaskManagement\Jobs\HandlePipelineTaskAction;
use TaskManagement\Jobs\HandlePipelineTaskAutoAction;
use TaskManagement\Entities\OnGoingPipelineTemplateTask;

class OngoingPipelineTemplateTaskObserver
{
    /**
     * @Description saving function trigger for do action on updated.
     * @param OnGoingPipelineTemplateTask $model
     * @return  void
     */
    public function updated(OnGoingPipelineTemplateTask $model): void
    {
        // Mark as pending if task is grouped
        if ($model->isPending() && !empty($model->child) && $model->child->is_grouped) {
            $model->child->markAsPending()->save();
        }

        // Check for is_grouped
        if ($model->isCompleted() && ($model->is_grouped || (!empty($model->child) && $model->child->is_grouped))) {
            if (!empty($model->parent) && !$model->parent->isCompleted()) {
                $model->parent->markAsCompleted()->save();
            }

            if (!empty($model->child) && $model->child->is_grouped && !$model->child->isCompleted()) {
                $model->child->markAsCompleted()->save();
            }
        }

        if ($model->isCompleted() && !empty($model->child)) {
            if (!$model->child->isCompleted()) {
                $model->child->markAsPending()->save();
            }
        }

        if (($model->isFailed() || $model->isError()) && (!empty($model->child))) {
            $model->child->markAsFailed()->save();
        }
    }

    /**
     * @Description saving function trigger for do action on updating.
     * @param OnGoingPipelineTemplateTask $model
     * @return void
     */
    public function updating(OnGoingPipelineTemplateTask $model): void
    {
        if ($model->isInProgress()) {
            if (!$model->isValid() || !$model->is_filter_matched) {
                $this->handleNotValidRecord($model);
            } else {
                dispatch(new HandlePipelineTaskAction($model));
            }
        }

        if ($model->isPending() && $model->isExecutable() && $model->isValid()) {
            if (!$model->is_filter_matched) {
                $model->handleFilters();
            }

            $model->markAsInProgress()->save();
        }
    }

    /**
     * @Description saving function trigger for do action on created.
     * @param OnGoingPipelineTemplateTask $model
     * @return void
     */
    public function created(OnGoingPipelineTemplateTask $model): void
    {
        // Handle if pipeline is deleted for this task
        if (empty($model->pipelineTemplate)) {
            $model->delete();
        }

        if ($model->isInProgress()) {
            if (!$model->is_filter_matched) {
                $model->handleFilters()->save();
            } else if (!$model->isValid()) {
                $this->handleNotValidRecord($model);
            } else {
                dispatch(new HandlePipelineTaskAction($model));
            }
        }
    }

    /**
     * @Description saving function trigger for do action on creating.
     * @param OnGoingPipelineTemplateTask $model
     * @return void
     */
    public function creating(OnGoingPipelineTemplateTask $model): void
    {
        $model->uuid = Str::uuid()->toString();
        $model->is_filter_matched = count($model->filters) === 0;
        if (!$model->status) {
            $model->status = OnGoingPipelineTemplateTask::STATUS_DRAFT;
        }
    }

    /**
     * @param OnGoingPipelineTemplateTask $model
     */
    private function handleNotValidRecord(OnGoingPipelineTemplateTask $model)
    {
        if (!$model->isValid() && !empty($model->source_attribute) && in_array($model->source_operator, [Operators::HAS_NOT_COMPLETED, Operators::IS_NOT_IN])) {
            // Handle: if completed under the time duration and have negative query
            if (in_array($model->source_group, [Groups::GROUP_OFFERS, Groups::GROUP_FORM])) {
                switch ($model->source_group) {
                    case Groups::GROUP_OFFERS:
                        $offer = Offer::findByPk($model->reference_uuid);
                        break;

                    default:
                        $offer = FormRequest::findByPk($model->reference_uuid);
                        break;
                }

                if ($offer->isCompleted()) {
                    if ($model->isMainTask()) {
                        $model->pipelineTemplate->delete();
                    } else {
                        $model->markAsFailed()->save();
                    }

                    return;
                }

                $this->dispatchJobIfNotExist($model);
            }

            $model->markAsPending()->save();
        } elseif ($model->isMainTask()) {
            $model->pipelineTemplate->delete();
        } else {
            $model->markAsFailed()->save();
        }
    }

    /**
     * @param OnGoingPipelineTemplateTask $model
     */
    private function dispatchJobIfNotExist(OnGoingPipelineTemplateTask $model)
    {
        list($maxDate, $now) = $model->validateSourceAttributeDates();
        $job = (new HandlePipelineTaskAutoAction($model->uuid))->delay($maxDate);
        $queues = Queue::getRedis()->connection(config('queue.connections.redis.connection', 'queue'))->zrange('queues:' .$job->queue. ':delayed', 0, -1);
        if (!empty($queues)) {
            $exist = false;
            foreach ($queues as $queue) {
                $queue = json_decode($queue, true);
                if (!empty($queue['displayName']) && $queue['displayName'] == HandlePipelineTaskAutoAction::class) {
                    $command = $queue['data']['command'] ?? "";
                    if (!empty($command) && str_contains($command, $model->uuid)) {
                        $exist = true;
                    }
                }
            }

            if ($exist) {
                return;
            }
        }

        dispatch($job);
    }
}
