<?php
namespace TaskManagement\Listeners;

use TaskManagement\Constants\Groups;
use TaskManagement\Constants\Sources;
use TaskManagement\Entities\OngoingPipelineTemplateTask;
use TaskManagement\Events\HandleTaskStatusGroupEvent;

class HandleTaskStatusGroupListener
{
    /**
     * @uses the handle the event.
     * @return void
     */
    public function handle(HandleTaskStatusGroupEvent $event): void
    {
        $ongoingTask = OngoingPipelineTemplateTask::pending()
            ->reference($event->user_task->uuid)
            ->source(Sources::TASK_AND_ACTIONS)
            ->sourceGroup(Groups::GROUP_TASK_STATUS)
            ->sourceValue($event->user_task->status_uuid)
            ->first();

        if (!empty($ongoingTask)) {
            $ongoingTask->responsibility_uuid = $ongoingTask->responsibility_uuid ?: $event->responsibility->uuid;
            $ongoingTask->responsibility_type = $ongoingTask->responsibility_type ?: $event->responsibility_type;

            // Check the filter and open this record
            $ongoingTask->handleFilters();
            $ongoingTask->markAsInProgress();
            $ongoingTask->save();
        }
    }
}
