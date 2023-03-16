<?php

namespace TaskManagement\Foundation;

use Carbon\Carbon;
use TaskManagement\Constants\Enums;
use TaskManagement\Entities\UserTask;
use TaskManagement\Entities\UserTaskReminder;

class Collection
{
    /**
     * User task reminder create
     * @param UserTask $model
     */
    final public static function HandleCreateReminder(UserTask $model): void
    {
        if (!empty($model->due_date) && $model->has_reminder && !empty($model->reminder_configuration)) {
            $reminder_date = self::getReminderDate($model, Carbon::createFromFormat('Y-m-d H:i:s', $model->due_date));
            UserTaskReminder::create(['user_task_uuid' => $model->uuid, 'reminder_date' => $reminder_date]);
        }
    }

    /**
     * User task reminder create or update
     * @param UserTask $model
     */
    final public static function HandleUpdateReminder(UserTask $model): void
    {
        $reminders = $model->pendingReminders;
        if (!empty($model->due_date) && $model->has_reminder && !empty($model->reminder_configuration)) {
            $reminder_date = self::getReminderDate($model, Carbon::createFromFormat('Y-m-d H:i:s', $model->due_date));
            if ($reminders->count() > 0) {
                // Update Reminder
                $reminders->each(function (UserTaskReminder $reminder) use ($reminder_date) {
                    $reminder->update(['reminder_date' => $reminder_date]);
                });
            } else {
                // Create Reminder
                UserTaskReminder::create(['user_task_uuid' => $model->uuid, 'reminder_date' => $reminder_date]);
            }
        } else if ($reminders->count() > 0) {
            // Delete Reminder
            $reminders->each(function (UserTaskReminder $reminder) {
                $reminder->delete();
            });
        }
    }

    /**
     * @param UserTask $model
     * @param Carbon $dueDate
     * @return string
     */
    final public static function getReminderDate(UserTask $model, Carbon $dueDate): string
    {
        $configuration = $model->reminder_configuration;
        switch ($configuration['frequency']) {
            case Enums::REMINDER_FREQUENCY_DAILY:
                return $dueDate->addDay(1)->format('Y-m-d H:i:s');

            case Enums::REMINDER_FREQUENCY_WEEKLY:
                return $dueDate->startOfWeek()->addWeek(1)->format('Y-m-d H:i:s');

            default:
                return $dueDate->startOfMonth()->addMonth(1)->format('Y-m-d H:i:s');
        }
    }
}
