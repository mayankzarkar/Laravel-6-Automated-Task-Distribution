<?php
namespace TaskManagement\Listeners;

use TaskManagement\Constants\Enums;
use TaskManagement\Entities\UserTask;
use TaskManagement\Events\ReminderEvent;
use TaskManagement\Foundation\NotificationCollection;

class ReminderListener
{
    /**
     * @uses the handle the event.
     * @return void
     */
    public function handle(ReminderEvent $event): void
    {
        $reminder = $event->reminder;
        $task = $reminder->userTask;
        $types = $task->reminder_configuration['type'];
        $data = $this->notificationData($task);
        if (in_array(Enums::REMINDER_TYPE_EMAIL, $types)) {
            NotificationCollection::sendEmail($data, "task_reminder", 'task_reminder');
        }

        if (in_array(Enums::REMINDER_TYPE_NOTIFICATION, $types)) {
            NotificationCollection::sendNotification($data, "reminder");
        }

        $reminder->processed()->save();
    }

    /**
     * @param UserTask $task
     * @return array
     */
    private final function notificationData(UserTask $task): array
    {
        return array_merge(NotificationCollection::generateTaskData($task), [
            'language_code' => 'en'
        ]);
    }
}
