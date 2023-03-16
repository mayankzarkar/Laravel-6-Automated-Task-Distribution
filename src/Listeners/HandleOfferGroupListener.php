<?php
namespace TaskManagement\Listeners;

use Carbon\Carbon;
use formbuilder\Entities\Offer;
use TaskManagement\Constants\Groups;
use Illuminate\Support\Facades\Cache;
use TaskManagement\Constants\Operators;
use TaskManagement\Events\HandleOfferGroupEvent;
use TaskManagement\Entities\PipelineTemplateTask;
use TaskManagement\Entities\OngoingPipelineTemplateTask;
use TaskManagement\Traits\Foundation\HandlePipelineTemplateTask;

class HandleOfferGroupListener
{
    use HandlePipelineTemplateTask;

    /**
     * @uses the handle the event.
     * @param HandleOfferGroupEvent $event
     */
    public function handle(HandleOfferGroupEvent $event)
    {
        if ($event->pipeline_uuid && $event->job_candidate) {
            if ($event->offer->isCompleted()) {
                $this->handleOffer($event, Operators::HAS_COMPLETED);
            }

            $this->handleOffer($event, Operators::HAS_NOT_COMPLETED);
        }
    }

    /**
     * @param HandleOfferGroupEvent $event
     * @param int $operator
     * @return void
     */
    private function handleOffer(HandleOfferGroupEvent $event, int $operator): void
    {
        $offer = $event->offer;
        $onGoingPipelineTemplateTask = OngoingPipelineTemplateTask::query()
            ->whereHas("pipelineTemplate", function($query) use ($event) {
                $query->account($event->account_uuid)->pipeline($event->pipeline_uuid);
            })
            ->pending()
            ->relation($event->job_candidate->user_uuid)
            ->responsibilityGroup($offer->template_uuid)
            ->sourceOperator($operator)
            ->get();

        if (count($onGoingPipelineTemplateTask) > 0) {
            foreach ($onGoingPipelineTemplateTask as $task) {
                $task->responsibility_uuid = $event->responsibility->uuid;
                $task->responsibility_type = $event->responsibility_type;
                $task->reference_uuid = $offer->uuid;

                // Check the filter and open this record
                $task->handleFilters()->markAsInProgress()->save();
            }
        } else {
            $pipelineTemplateTasks = PipelineTemplateTask::query()
                ->whereHas("pipelineTemplate", function($query) use ($event) {
                    $query->account($event->account_uuid)->pipeline($event->pipeline_uuid);
                })
                ->mainTask()
                ->sourceGroup(($offer instanceof Offer) ? Groups::GROUP_OFFERS : Groups::GROUP_FORM)
                ->sourceOperator($operator)
                ->sourceValue($offer->template_uuid)
                ->get();

            if (count($pipelineTemplateTasks) > 0) {
                $onGoingPipelineTemplateTask = OngoingPipelineTemplateTask::query()
                    ->whereHas("pipelineTemplate", function($query) use ($event) {
                        $query->account($event->account_uuid)->pipeline($event->pipeline_uuid);
                    })
                    ->mainTask()
                    ->executed()
                    ->relation($event->job_candidate->user_uuid)
                    ->responsibilityGroup($offer->template_uuid)
                    ->sourceOperator($operator)
                    ->reference($offer->uuid)
                    ->latest()
                    ->get();

                if (count($onGoingPipelineTemplateTask) == 0) {
                    $pipelineTemplateTasks = $this->verifyPipelineTemplateTask($pipelineTemplateTasks, $operator, $event);
                    if (count($pipelineTemplateTasks) > 0) {
                        self::handlePipelineTask($pipelineTemplateTasks, $event->responsibility->uuid, $event->responsibility_type, $event->job_candidate, ["reference_uuid" => $offer->uuid]);
                    }
                }
            }
        }
    }

    /**
     * @param $tasks
     * @param int $operator
     * @param HandleOfferGroupEvent $event
     * @return mixed
     */
    private function verifyPipelineTemplateTask($tasks, int $operator, HandleOfferGroupEvent $event)
    {
        return $tasks->filter(function ($task) use ($event, $operator) {
            $cacheKey = "{$task->uuid}";
            if (Cache::has($cacheKey)) {
                Cache::forget($cacheKey);
            }

            Cache::put($cacheKey, ["task" => $task], Carbon::now()->endOfDay());
            return true;
        });
    }
}
