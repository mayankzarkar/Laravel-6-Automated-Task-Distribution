<?php

namespace TaskManagement\Http\Resources;

use Illuminate\Http\Request;
use Helper\Http\Resources\BaseJsonResource as Resource;

/**
 * @property string $uuid
 * @property string $pipeline_uuid
 * @property PipelineTemplateTaskResource $tasks
 * @property string $title
 * @property bool $is_published
 * @property string $created_by
 * @property string $created_at
 * @property string $updated_at
 **/
class PipelineTemplateResource extends Resource
{
    /**
     * @Description Transform the resource into an array.
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'pipeline_uuid' => $this->pipeline_uuid,
            'tasks' => PipelineTemplateTaskResource::collection($this->tasks),
            'title' => $this->title,
            'is_published' => $this->is_published,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
