<?php

namespace TaskManagement\Http\Controllers;

use Carbon\Carbon;
use User\Facade\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use TaskManagement\Constants\Enums;
use TaskManagement\Entities\UserTask;
use User\Entities\User as UserEntity;
use Response\Template\Facade\Template;
use TaskManagement\Traits\TaskTypeTrait;
use Response\Template\Constants\ResponseTypes;
use TaskManagement\Http\Requests\UserTaskRequest;
use TaskManagement\Http\Resources\UserTaskResource;
use TaskManagement\Http\Requests\UserTaskViewRequest;
use TaskManagement\Http\Requests\UserTaskListRequest;
use TaskManagement\Foundation\NotificationCollection;
use TaskManagement\Http\Requests\UserTaskNoteRequest;
use TaskManagement\Http\Requests\UserTaskStatusRequest;
use Account\Foundation\Collection as AccountCollection;
use TaskManagement\Http\Requests\UserTaskDeleteRequest;
use TaskManagement\Http\Requests\UserTaskNoteDeleteRequest;

class UserTaskController extends BaseController
{
    use TaskTypeTrait;

    /**
     * Create user task
     * @method POST
     * @param UserTaskRequest $request
     * @return JsonResponse
     * @api api/v1/service/task/users
     */
    public function create(UserTaskRequest $request): JsonResponse
    {
        $userTask = UserTask::create($request->validated());
        return Template::success()
            ->requestId($this->getRequestId($request))
            ->action(ResponseTypes::created)
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_ACCEPTED)
            ->message(trans('setup::response.added_successfully'))
            ->result(new UserTaskResource($userTask))
            ->response();
    }

    /**
     * Update user task
     * @method PUT
     * @param UserTaskRequest $request
     * @return JsonResponse
     * @api api/v1/service/task/users
     */
    public function update(UserTaskRequest $request): JsonResponse
    {
        $userTask = UserTask::findByPk($request->uuid);
        $userTask->fill($request->validated())->save();
        return Template::success()
            ->requestId($this->getRequestId($request))
            ->action(ResponseTypes::updated)
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_ACCEPTED)
            ->message(trans('setup::response.updated_successfully'))
            ->result(new UserTaskResource($userTask))
            ->response();
    }

    /**
     * Get user task
     * @method GET
     * @param UserTaskViewRequest $request
     * @return JsonResponse
     * @api api/v1/service/task/users/view
     */
    public function view(UserTaskViewRequest $request): JsonResponse
    {
        $userTask = UserTask::findByPk($request->uuid);
        return Template::success()
            ->requestId($this->getRequestId($request))
            ->action(ResponseTypes::view)
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_OK)
            ->message(trans('setup::response.data_retrieved'))
            ->result(new UserTaskResource($userTask))
            ->response();
    }

    /**
     * Get user task by slug
     * @method GET
     * @return JsonResponse
     * @api api/v1/service/task/users/{slug}
     */
    public function viewBySlug(string $slug): JsonResponse
    {
        $userTask = UserTask::findBySlug($slug);
        if (empty($userTask)) {
            return Template::error()
                ->requestId($this->getRequestId(request()))
                ->status(ResponseTypes::failed)
                ->statusCode(Response::HTTP_NOT_FOUND)
                ->message('User task not found')
                ->errorsCount(1)
                ->response();
        }

        return Template::success()
            ->requestId($this->getRequestId(request()))
            ->action(ResponseTypes::view)
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_OK)
            ->message(trans('setup::response.data_retrieved'))
            ->result(new UserTaskResource($userTask))
            ->response();
    }

    /**
     * Get all user tasks
     * @method GET
     * @param UserTaskListRequest $request
     * @return JsonResponse
     * @api api/v1/service/task/users/list
     */
    public function list(UserTaskListRequest $request): JsonResponse
    {
        $userTasks = UserTask::getUserTasks(AccountCollection::get_account(), User::getAuthUser()->uuid, $request);
        return Template::paginate()
            ->requestId($this->getRequestId(request()))
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_OK)
            ->message(trans('setup::response.data_retrieved'))
            ->action(ResponseTypes::list)
            ->page($request->page)
            ->limit($request->limit)
            ->lastPage($userTasks->lastPage())
            ->total($userTasks->total())
            ->result(UserTaskResource::collection($userTasks))
            ->response();
    }

    /**
     * Delete user tasks
     * @method DELETE
     * @param UserTaskDeleteRequest $request
     * @return JsonResponse
     * @api api/v1/service/task/users
     */
    public function delete(UserTaskDeleteRequest $request): JsonResponse
    {
        foreach ($request->uuids as $uuid) {
            UserTask::findByPk($uuid)->delete();
        }

        return Template::paginate()
            ->requestId($this->getRequestId($request))
            ->action(ResponseTypes::deleted)
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_ACCEPTED)
            ->message(trans('setup::response.deleted_successfully'))
            ->result([])
            ->response();
    }

    /**
     * Update statuses
     * @method PUT
     * @param UserTaskStatusRequest $request
     * @return JsonResponse
     * @api api/v1/service/task/status
     */
    public function status(UserTaskStatusRequest $request): JsonResponse
    {
        foreach ($request->list as $data) {
            $userTask = UserTask::findByPk($data['uuid']);
            $userTask->fill(['status_uuid' => $data['status_uuid']]);
            $userTask->save();
        }

        return Template::paginate()
            ->requestId($this->getRequestId($request))
            ->action(ResponseTypes::updated)
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_ACCEPTED)
            ->message(trans('setup::response.updated_successfully'))
            ->result([])
            ->response();
    }

    /**
     * Create Note for user task
     * @method POST
     * @param UserTaskNoteRequest $request
     * @return JsonResponse
     * @api api/v1/service/task/note
     */
    public function createNote(UserTaskNoteRequest $request): JsonResponse
    {
        $userTask = UserTask::findByPk($request->uuid);
        $notes = $userTask->notes ?? [];

        $createdByType = Enums::CANDIDATE;
        if (User::getAuthUser() instanceof UserEntity) {
            $createdByType = User::isEmployee() ? Enums::EMPLOYEE : Enums::RECRUITER;
        }

        $note = [
            'uuid' => Str::uuid()->toString(),
            'note' => $request->note,
            'created_by' => [
                'uuid' => User::AuthUuid(),
                'type' => $createdByType,
                'name' => User::getAuthUser()->getFullName(),
            ],
            'created_at' => Carbon::now()->format('Y-m-d h:i:s')
        ];
        array_push($notes, $note);

        $userTask->update(['notes' => $notes]);
        NotificationCollection::TaskNoteCreated($userTask, $note);
        return Template::paginate()
            ->requestId($this->getRequestId($request))
            ->action(ResponseTypes::updated)
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_ACCEPTED)
            ->message(trans('setup::response.updated_successfully'))
            ->result(new UserTaskResource($userTask))
            ->response();
    }

    /**
     * Delete Note for user task
     * @method DELETE
     * @param UserTaskNoteDeleteRequest $request
     * @return JsonResponse
     * @api api/v1/service/task/note
     */
    public function deleteNote(UserTaskNoteDeleteRequest $request): JsonResponse
    {
        $userTask = UserTask::findByPk($request->uuid);
        $notes = $userTask->notes;
        foreach ($request->note_uuids as $uuid) {
            $key = array_search($uuid, array_column($notes, 'uuid'));
            unset($notes[$key]);
        }

        $userTask->update(['notes' => $notes]);
        return Template::paginate()
            ->requestId($this->getRequestId($request))
            ->action(ResponseTypes::deleted)
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_ACCEPTED)
            ->message(trans('setup::response.deleted_successfully'))
            ->result(new UserTaskResource($userTask))
            ->response();
    }

    /**
     * User Task type
     * @method get
     * @return JsonResponse
     * @api api/v1/service/task/users/types
     */
    public function types(Request $request): JsonResponse
    {
        $candidateRelation = filter_var($request->get('have_candidate_relation', true), FILTER_VALIDATE_BOOLEAN);
        return Template::success()
            ->requestId($this->getRequestId(request()))
            ->action(ResponseTypes::view)
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_OK)
            ->message(trans('setup::response.data_retrieved'))
            ->result(self::GetTypes($candidateRelation))
            ->response();
    }

    /**
     * User Task mark as completed
     * @method get
     * @return JsonResponse
     * @api api/v1/service/task/users/mark-as-completed
     */
    public function markAsCompleted(UserTaskViewRequest $request): JsonResponse
    {
        $userTask = UserTask::findByPk($request->uuid);
        $userTask->mark_as_completed = 2;
        $userTask->save();
        return Template::success()
            ->requestId($this->getRequestId($request))
            ->action(ResponseTypes::view)
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_OK)
            ->message(trans('setup::response.updated_successfully'))
            ->result(new UserTaskResource($userTask->fresh()))
            ->response();
    }
}
