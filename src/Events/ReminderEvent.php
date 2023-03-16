<?php

namespace TaskManagement\Events;

use Illuminate\Queue\SerializesModels;
use TaskManagement\Entities\UserTaskReminder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ReminderEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var UserTaskReminder $reminder */
    public $reminder;

    /**
     * @note Create a new event instance.
     * @param UserTaskReminder $reminder
     * @return void
     */
    public function __construct(UserTaskReminder $reminder)
    {
        $this->reminder = $reminder;
    }
}
