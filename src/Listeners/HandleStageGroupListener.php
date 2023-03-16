<?php
namespace TaskManagement\Listeners;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use TaskManagement\Constants\Groups;
use TaskManagement\Constants\Operators;
use TaskManagement\Events\HandleStageGroupEvent;
use TaskManagement\Entities\PipelineTemplateTask;
use TaskManagement\Entities\OngoingPipelineTemplateTask;
use TaskManagement\Traits\Foundation\HandlePipelineTemplateTask;

class HandleStageGroupListener
{
    use HandlePipelineTemplateTask;

    /**
     * @uses the handle the event.
     * @return void
     */
    public function handle(HandleStageGroupEvent $event): void
    {
        $this->handleCandidateSource($event);
    }

    /**
     * @param HandleStageGroupEvent $event
     */
    private function handleCandidateSource(HandleStageGroupEvent $event): void
    {
        $relation = $event->relation;
        $onGoingPipelineTemplateTask = OngoingPipelineTemplateTask::query()
            ->whereHas("pipelineTemplate", function($query) use ($event) {
                $query->account($event->account_uuid)->pipeline($event->pipeline_uuid);
            })
            ->pending()
            ->relation($relation->user_uuid)
            ->responsibilityGroup($event->stage_uuid)
            ->get();

        if (count($onGoingPipelineTemplateTask) > 0) {
            foreach ($onGoingPipelineTemplateTask as $task) {
                $task->responsibility_uuid = $event->responsibility->uuid;
                $task->responsibility_type = $event->responsibility_type;

                // Check the filter and open this record
                $task->handleFilters();
                $task->markAsInProgress();
                $task->save();
            }
        } else {
            /*
             $pipelineTemplateTasksNotExists = PipelineTemplateTask::query()
                ->whereHas("pipelineTemplate", function($query) use ($event) {
                    $query->account($event->account_uuid)->pipeline($event->pipeline_uuid);
                })
                ->mainTask()
                ->sourceGroup(Groups::GROUP_STAGES)
                ->sourceOperator(Operators::IS_NOT_IN)
                ->sourceValueNotIn($event->stage_uuid)
                ->get();

            if (count($pipelineTemplateTasksNotExists) > 0) {
                self::handlePipelineTask($pipelineTemplateTasksNotExists, $event->responsibility->uuid, $event->responsibility_type, $relation);
            }
            */

            $pipelineTemplateTasksExists = PipelineTemplateTask::query()
                ->whereHas("pipelineTemplate", function($query) use ($event) {
                    $query->account($event->account_uuid)->pipeline($event->pipeline_uuid);
                })
                ->mainTask()
                ->sourceGroup(Groups::GROUP_STAGES)
                ->get();

            $pipelineTemplateTasksExists = $this->verifyPipelineTemplateTask($pipelineTemplateTasksExists, Operators::IS_IN, $event);
            if (count($pipelineTemplateTasksExists) > 0) {
                self::handlePipelineTask($pipelineTemplateTasksExists, $event->responsibility->uuid, $event->responsibility_type, $relation);
            }
        }
    }

    /**
     * @param $tasks
     * @param int $operator
     * @param HandleStageGroupEvent $event
     * @return mixed
     */
    private function verifyPipelineTemplateTask($tasks, int $operator, HandleStageGroupEvent $event)
    {
        return $tasks->filter(function ($task) use ($event, $operator) {
            if ($task->source_operator == $operator && $task->source_value == $event->stage_uuid) {
                $cacheKey = "{$task->uuid}";
                if (Cache::has($cacheKey)) {
                    Cache::forget($cacheKey);
                }

                Cache::put($cacheKey, ["task" => $task], Carbon::now()->endOfDay());
                return true;
            }

            $triggerTask = $task->child;
            $isValid = false;
            $validTask = null;
            while (!$isValid && !empty($triggerTask)) {
                if ($triggerTask->source_operator == $operator && $triggerTask->source_value == $event->stage_uuid) {
                    $isValid = true;
                    $validTask = $triggerTask;
                }

                $triggerTask = $triggerTask->child;
            }

            // Put details into cache
            if ($isValid && !empty($validTask)) {
                $cacheKey = "{$validTask->uuid}";
                if (Cache::has($cacheKey)) {
                    Cache::forget($cacheKey);
                }

                Cache::put($cacheKey, ["task" => $validTask], Carbon::now()->endOfDay());
            }

            return $isValid;
        });
    }
}
