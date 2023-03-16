<?php

namespace TaskManagement\Http\Controllers\Pipeline;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Response\Template\Facade\Template;
use TaskManagement\Entities\PipelineTemplate;
use Response\Template\Constants\ResponseTypes;
use TaskManagement\Http\Controllers\BaseController;
use Account\Foundation\Collection as AccountCollection;
use TaskManagement\Http\Requests\Pipeline\TemplateRequest;
use TaskManagement\Http\Resources\PipelineTemplateResource;
use TaskManagement\Http\Requests\Pipeline\TemplateListRequest;
use TaskManagement\Http\Requests\Pipeline\TemplateViewRequest;
use TaskManagement\Http\Requests\Pipeline\TemplateDeleteRequest;

class PipelineTemplateController extends BaseController
{
    /**
     * Get all pipeline template
     * @method GET
     * @param TemplateListRequest $request
     * @return JsonResponse
     * @api api/v1/service/pipeline-task/templates
     */
    public function list(TemplateListRequest $request): JsonResponse
    {
        $pipeLineTemplates = PipelineTemplate::getPipelineTemplates(AccountCollection::get_account(), $request);
        return Template::paginate()
            ->requestId($this->getRequestId(request()))
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_OK)
            ->message(trans('setup::response.data_retrieved'))
            ->action(ResponseTypes::list)
            ->page($request->page)
            ->limit($request->limit)
            ->lastPage($pipeLineTemplates->lastPage())
            ->total($pipeLineTemplates->total())
            ->result(PipelineTemplateResource::collection($pipeLineTemplates))
            ->response();
    }

    /**
     * create pipeline template
     * @method POST
     * @param TemplateRequest $request
     * @return JsonResponse
     * @api api/v1/service/pipeline-task/templates
     */
    public function create(TemplateRequest $request): JsonResponse
    {
        $pipeLineTemplate = PipelineTemplate::create($request->validated());
        return Template::success()
            ->requestId($this->getRequestId($request))
            ->action(ResponseTypes::created)
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_ACCEPTED)
            ->message(trans('setup::response.added_successfully'))
            ->result(new PipelineTemplateResource($pipeLineTemplate))
            ->response();
    }

    /**
     * update pipeline template
     * @method PUT
     * @param TemplateRequest $request
     * @return JsonResponse
     * @api api/v1/service/pipeline-task/templates
     */
    public function update(TemplateRequest $request): JsonResponse
    {
        $pipeLineTemplate = PipelineTemplate::findByPk($request->uuid);
        $pipeLineTemplate->fill($request->validated())->save();
        return Template::success()
            ->requestId($this->getRequestId($request))
            ->action(ResponseTypes::updated)
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_ACCEPTED)
            ->message(trans('setup::response.updated_successfully'))
            ->result(new PipelineTemplateResource($pipeLineTemplate))
            ->response();
    }

    /**
     * view pipeline template
     * @method GET
     * @param TemplateViewRequest $request
     * @return JsonResponse
     * @api api/v1/service/pipeline-task/templates/view
     */
    public function view(TemplateViewRequest $request): JsonResponse
    {
        $pipeLineTemplate = PipelineTemplate::findByPk($request->uuid);
        return Template::success()
            ->requestId($this->getRequestId($request))
            ->action(ResponseTypes::view)
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_OK)
            ->message(trans('setup::response.data_retrieved'))
            ->result(new PipelineTemplateResource($pipeLineTemplate))
            ->response();
    }

    /**
     * Delete pipeline template
     * @method DELETE
     * @param TemplateDeleteRequest $request
     * @return JsonResponse
     * @api api/v1/service/pipeline-task/templates
     */
    public function delete(TemplateDeleteRequest $request): JsonResponse
    {
        foreach ($request->uuids as $uuid) {
            PipelineTemplate::findByPk($uuid)->delete();
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
