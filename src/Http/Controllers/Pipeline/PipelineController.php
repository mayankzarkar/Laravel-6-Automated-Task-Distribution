<?php

namespace TaskManagement\Http\Controllers\Pipeline;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use TaskManagement\Traits\ActionTrait;
use TaskManagement\Traits\FilterTrait;
use TaskManagement\Traits\SourceTrait;
use Response\Template\Facade\Template;
use Response\Template\Constants\ResponseTypes;
use TaskManagement\Http\Controllers\BaseController;
use TaskManagement\Http\Requests\Pipeline\FilterRequest;
use TaskManagement\Http\Requests\Pipeline\TemplateSourceRequest;

class PipelineController extends BaseController
{
    use SourceTrait, FilterTrait, ActionTrait;

    /**
     * List of sources
     * @method GET
     * @param TemplateSourceRequest $request
     * @return JsonResponse
     * @api api/v1/service/pipeline-task/sources
     */
    public function sources(TemplateSourceRequest $request): JsonResponse
    {
        return Template::success()
            ->requestId($this->getRequestId(request()))
            ->action(ResponseTypes::view)
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_OK)
            ->message(trans('setup::response.data_retrieved'))
            ->result(self::GetSources($request->pipeline_uuid, $request->is_grouped, $request->parent_uuid))
            ->response();
    }

    /**
     * List of filters
     * @method GET
     * @param FilterRequest $request
     * @return JsonResponse
     * @api api/v1/service/pipeline-task/filters
     */
    public function filter(FilterRequest $request): JsonResponse
    {
        return Template::success()
            ->requestId($this->getRequestId($request))
            ->action(ResponseTypes::view)
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_OK)
            ->message(trans('setup::response.data_retrieved'))
            ->result(self::GetFilters($request->source, $request->source_operator_group))
            ->response();
    }

    /**
     * List of actions
     * @method GET
     * @return JsonResponse
     * @api api/v1/service/pipeline-task/actions
     */
    public function actions(): JsonResponse
    {
        return Template::success()
            ->requestId($this->getRequestId(request()))
            ->action(ResponseTypes::view)
            ->status(ResponseTypes::success)
            ->statusCode(Response::HTTP_OK)
            ->message(trans('setup::response.data_retrieved'))
            ->result(self::GetActions())
            ->response();
    }
}
