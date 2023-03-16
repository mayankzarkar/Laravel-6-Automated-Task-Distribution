<?php

namespace TaskManagement\Foundation;

use service\mail\Foundation\Mail;
use service\mail\Facade\MailService;
use TaskManagement\Constants\Enums;
use TaskManagement\Entities\UserTask;
use Service\Notification\Facade\Notification;

class NotificationCollection
{
    final public static function generateTaskData(UserTask $task): array
    {
        list($language_code, $assignee_name, $creator_name) = self::generateData($task);
        return [
            'assignee_uuid' => $task->responsibility_uuid,
            'account_uuid' => $task->account_uuid,
            'assignee_email' => $task->responsibility->email,
            'due_date' => $task->due_date,
            'title' => $task->title,
            'description' => $task->description,
            'slug' => $task->slug,
            'status' => $task->taskStatus->name[$language_code]??$task->taskStatus->name['en'],
            'language_code' => $language_code,
            'assignee_name' => $assignee_name,
            'creator_name' => $creator_name,
            'link' => self::generateLink($task),
            'enable_notification' => $task->enable_notification
        ];
    }

    /**
     * @param UserTask $task
     */
    final public static function TaskCreated(UserTask $task): void
    {
        self::process(self::generateTaskData($task));
    }

    /**
     * @param UserTask $task
     * @param array $note
     */
    final public static function TaskNoteCreated(UserTask $task, array $note): void
    {
        $data = self::generateTaskData($task);
        $data = array_merge($data, [
            'note' => $note['note'],
            'user_name' => $note['created_by']['name'][$data['language_code']]
        ]);
        self::process($data, "task_note_create", "task_note_create");
    }

    /**
     * @param UserTask $task
     */
    final public static function TaskStatusUpdated(UserTask $task): void
    {
        $data = self::generateTaskData($task);
        self::process($data, "status_change", "task_status_updated");
    }

    /**
     * @param UserTask $task
     * @return array
     */
    final public static function generateData(UserTask $task): array
    {
        $creator_name = "System";
        $language_code = "en";
        if ($task->creator_type !== Enums::SYSTEM) {
            $language_code = request()->header('accept-language', 'en');
            $creator_name = $task->creator->getFullName()[$language_code] ?? $task->creator->getFullName()['en'] ?? $task->responsibility->getFullName();
        }

        $assignee_name = $task->responsibility->getFullName()[$language_code] ?? $task->responsibility->getFullName()['en'] ?? $task->responsibility->getFullName();

        return [$language_code, $assignee_name, $creator_name];
    }

    /**
     * @param array $data
     * @param string $notification_type
     * @param string $template
     */
    final private static function process(array $data, string $notification_type = "create", string $template = 'task_assigned'): void
    {
        if (!empty($data["enable_notification"])) {
            self::sendNotification($data, $notification_type);
        }
        self::sendEmail($data, $template, $template);
    }

    /**
     * @param array $data
     * @param string $type
     */
    final public static function sendNotification(array $data, string $type): void
    {
        $notification_object = Notification::notification($data['assignee_uuid'], null, $data['account_uuid'], true);
        $notification_object->create(
            trans("task_management::notification.user_task.{$type}.title"),
            trans("task_management::notification.user_task.{$type}.description", $data),
            'eva_flow',
            $data['link']
        );
    }

    /**
     * @param array $data
     * @param string $subject_template
     * @param string $body_template
     */
    final public static function sendEmail(array $data, string $subject_template, string $body_template): void
    {
        MailService::build(
            Mail::getFromEmail(),
            Mail::getFromName(),
            $data['assignee_email'],
            $data['assignee_name'],
            trans("mail-service::subjects_email_template.task_management.{$subject_template}"),
            view("mail-service::templates.{$data['language_code']}.{$body_template}", $data)
        )->send();
    }

    /**
     * Generate Base link for candidate
     * @param UserTask $task
     * @return string
     */
    final private static function generateLink(UserTask $task): string
    {
        return config('task_management.task_management_url') . "?slug=" . $task->slug;
    }
}
