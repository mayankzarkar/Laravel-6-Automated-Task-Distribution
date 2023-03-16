<?php

namespace TaskManagement\Http\Resources;

use Illuminate\Http\Request;
use TaskManagement\Constants\Enums;
use TaskManagement\Entities\UserTask;
use TaskManagement\Constants\TaskTypes;
use candidate\service\Http\Resources\CandidateResource;
use Helper\Http\Resources\BaseJsonResource as Resource;
use User\Http\Resources\UserListResource as UserResource;
use Lookup\Http\Resources\task_status\TaskStatusResource;
use employee\Http\Resources\Employee\EmployeeListResource as EmployeeResource;

/**
 * @property string $uuid
 * @property string $slug
 * @property int $type
 * @property int $responsibility_type
 * @property string $responsibility_uuid
 * @property int $creator_type
 * @property string $creator_uuid
 * @property string $relation_uuid
 * @property int $status
 * @property string $title
 * @property string $description
 * @property bool $enable_notification
 * @property bool $has_configuration
 * @property array $configuration
 * @property string $start_date
 * @property string $due_date
 * @property bool $has_reminder
 * @property array $reminder_configuration
 * @property array $attachments
 * @property array $tags
 * @property array $notes
 * @property int $feature
 * @property string $created_by
 * @property string $created_at
 * @property string $updated_at
 * @property string $redirect_url
 * @property array $additional_data
 * @property bool $is_editable
 * @property array $extra_data
 **/
class UserTaskResource extends Resource
{
    /**
     * @Description Transform the resource into an array.
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        // Generating resource for creator
        switch ($this->creator_type) {
            case Enums::SYSTEM:
                $creator = null;
                break;

            case Enums::RECRUITER:
                $creator = new UserResource($this->creator);
                break;

            case Enums::EMPLOYEE:
                $creator = new EmployeeResource($this->creator->employee);
                break;

            default:
                $creator = new CandidateResource($this->creator->candidate);
                break;
        }

        // Generating resource for responsibility
        switch ($this->responsibility_type) {
            case Enums::RECRUITER:
                $responsibility = new UserResource($this->responsibility);
                break;

            case Enums::EMPLOYEE:
                $responsibility = new EmployeeResource($this->responsibility->employee);
                break;

            default:
                $responsibility = new CandidateResource($this->responsibility->candidate);
                break;
        }

        return [
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'type' => [
                "key" => $this->type,
                "value" => TaskTypes::TYPES[$this->type]
            ],
            'responsibility_type' => $this->responsibility_type,
            'responsibility' => $responsibility,
            'creator_type' => $this->creator_type,
            'creator' => $creator,
            'relation' => new CandidateResource($this->relation->candidate ?? null),
            'status' => new TaskStatusResource($this->taskStatus),
            'title' => $this->title,
            'description' => $this->description,
            'enable_notification' => $this->enable_notification,
            'has_configuration' => $this->has_configuration,
            'configuration' => $this->configuration,
            'start_date' => $this->start_date,
            'due_date' => $this->due_date,
            'has_reminder' => $this->has_reminder,
            'reminder_configuration' => !empty($this->reminder_configuration) ? $this->reminder_configuration : null,
            'attachments' => $this->attachments,
            'tags' => $this->tags,
            'notes' => $this->notes,
            'feature' => $this->feature,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'redirect_url' => $this->redirect_url,
            'additional_data' => UserTask::GetAdditionalData($this->type, $this->additional_data ?? []),
            'is_editable' => is_bool($this->is_editable) ? $this->is_editable : true,
            'extra_data' => $this->extra_data,
            'mark_as_completed' => $this->mark_as_completed ?: 0
        ];
    }
}
