<?php

namespace TaskManagement\Http\Controllers\Pipeline;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Response\Template\Facade\Template;
use Response\Template\Constants\ResponseTypes;
use TaskManagement\Entities\PipelineTemplateTask;
use TaskManagement\Http\Controllers\BaseController;
use TaskManagement\Http\Requests\Pipeline\TemplateTaskRequest;
use TaskManagement\Http\Resources\PipelineTemplateTaskResource;
use TaskManagement\Http\Requests\Pipeline\TemplateTaskDeleteRequest;

class PipelineTemplateTaskController extends BaseController
{
    /**
     * create pipeline template task
     * @method POST
     * @param TemplateTaskRequest $request
     * @return JsonResponse
     * @api api/v1/service/pipeline-task
     */
    public function create(TemplateTaskRequest $request): JsonResponse
    {
        $pipeLineTemplateTask = PipelineTemplateTask::create($request->validated());
        return Template::success()
            ->requestId($this->getRequestId($request))
            ->action(ResponseTypes::created)
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_ACCEPTED)
            ->message(trans('setup::response.added_successfully'))
            ->result(new PipelineTemplateTaskResource($pipeLineTemplateTask))
            ->response();
    }

    /**
     * update pipeline template task
     * @method PUT
     * @param TemplateTaskRequest $request
     * @return JsonResponse
     * @api api/v1/service/pipeline-task
     */
    public function update(TemplateTaskRequest $request): JsonResponse
    {
        $pipeLineTemplateTask = PipelineTemplateTask::findByPk($request->uuid);
        $pipeLineTemplateTask->fill($request->validated())->save();
        return Template::success()
            ->requestId($this->getRequestId($request))
            ->action(ResponseTypes::updated)
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_ACCEPTED)
            ->message(trans('setup::response.updated_successfully'))
            ->result(new PipelineTemplateTaskResource($pipeLineTemplateTask))
            ->response();
    }

    /**
     * Delete pipeline template task
     * @method DELETE
     * @param TemplateTaskDeleteRequest $request
     * @return JsonResponse
     * @api api/v1/service/pipeline-task
     */
    public function delete(TemplateTaskDeleteRequest $request): JsonResponse
    {
        foreach ($request->uuids as $uuid) {
            PipelineTemplateTask::findByPk($uuid)->delete();
        }

        return Template::success()
            ->requestId($this->getRequestId($request))
            ->action(ResponseTypes::deleted)
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_ACCEPTED)
            ->message(trans('setup::response.deleted_successfully'))
            ->result([])
            ->response();
    }
}
