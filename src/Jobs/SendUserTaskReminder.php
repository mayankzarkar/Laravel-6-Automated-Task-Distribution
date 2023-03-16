<?php
namespace TaskManagement\Jobs;

use Helper\Constants\Queue;
use Illuminate\Support\Facades\Log;
use TaskManagement\Events\ReminderEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use TaskManagement\Entities\UserTaskReminder;

class SendUserTaskReminder implements ShouldQueue
{
    /**
     * @Development_Group: Backend Developer
     * @Description  The name of the queue the job should be sent to.
     * @var string
     */
    public $queue = Queue::TASK_MANAGEMENT_QUEUE;

    /**
     * @Development_Group: Backend Developer
     * @Description  The time (seconds) before the job should be processed.
     * @var int
     */
    public $delay = Queue::TASK_MANAGEMENT_QUEUE_DELAY;

    /**
     * Execute the job.
     * @return void
     */
    public function handle(): void
    {
        $reminders = UserTaskReminder::GetPendingReminder();
        $reminders->each(function (UserTaskReminder $reminder) {
            event(new ReminderEvent($reminder));
        });
    }

    /**
     * @param ReminderEvent $event
     * @param \Exception $exception
     */
    public function failed(ReminderEvent $event, \Exception $exception): void
    {
        Log::channel('task_management')->error('send-reminder-to-user-command', [
            'reminder_uuid' => $event->reminder->uuid,
            'message' => $exception->getMessage(),
            'trace' => $exception->getTrace()
        ]);
    }
}
