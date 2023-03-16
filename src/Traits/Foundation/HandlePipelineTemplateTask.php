<?php

namespace TaskManagement\Traits\Foundation;

use App\ats\Entities\JobCandidate;
use Illuminate\Support\Facades\Cache;
use TaskManagement\Entities\PipelineTemplate;
use TaskManagement\Entities\OngoingPipelineTemplate;
use TaskManagement\Entities\OnGoingPipelineTemplateTask;

trait HandlePipelineTemplateTask
{
    /**
     * @param $pipeline_template_tasks
     * @param string $responsibility_uuid
     * @param int $responsibility_type
     * @param JobCandidate $relation
     * @param array $extra
     */
    public function handlePipelineTask($pipeline_template_tasks, string $responsibility_uuid, int $responsibility_type, JobCandidate $relation, array $extra = []): void
    {
        foreach ($pipeline_template_tasks as $pipelineTemplateTask) {
            if (!$pipelineTemplateTask->pipelineTemplate->isPublished() || empty($pipelineTemplateTask->pipelineTemplate->tasks)) {
                continue;
            }

            $this->handleClonePipeline($pipelineTemplateTask->pipelineTemplate, $responsibility_uuid, $responsibility_type, $relation, $extra);
        }
    }

    /**
     * @param PipelineTemplate $pipeline
     * @param string $responsibility_uuid
     * @param int $responsibility_type
     * @param JobCandidate $relation
     * @return void
     */
    private function handleClonePipeline(PipelineTemplate $pipeline, string $responsibility_uuid, int $responsibility_type, JobCandidate $relation, array $extra): void
    {
        $ongoingPipelineTemplate = new OngoingPipelineTemplate();
        $ongoingPipelineTemplate->account_uuid = $pipeline->account_uuid;
        $ongoingPipelineTemplate->source_pipeline_uuid = $pipeline->uuid;
        $ongoingPipelineTemplate->pipeline_uuid = $pipeline->pipeline_uuid;
        $ongoingPipelineTemplate->title = $pipeline->title;
        $ongoingPipelineTemplate->save();

        $parentUuid = $oldSource = null;
        foreach ($pipeline->tasks as $task) {
            $ongoingPipelineTemplateTask = new OnGoingPipelineTemplateTask();
            $ongoingPipelineTemplateTask->pipeline_template_uuid = $ongoingPipelineTemplate->uuid;
            $ongoingPipelineTemplateTask->responsibility_type = $responsibility_type;
            $ongoingPipelineTemplateTask->responsibility_group_type = $task->source_group;
            $ongoingPipelineTemplateTask->responsibility_group_uuid = $task->source_value;
            $ongoingPipelineTemplateTask->source = $task->source;
            $ongoingPipelineTemplateTask->source_group = $task->source_group;
            $ongoingPipelineTemplateTask->source_operator = $task->source_operator;
            $ongoingPipelineTemplateTask->source_attribute = $task->source_attribute;
            $ongoingPipelineTemplateTask->source_attribute_value = $task->source_attribute_value;
            $ongoingPipelineTemplateTask->source_value = $task->source_value;
            $ongoingPipelineTemplateTask->filters = $task->filters;
            $ongoingPipelineTemplateTask->action = $task->action;
            $ongoingPipelineTemplateTask->is_grouped = $task->is_grouped;

            // Override the action data and putting the job_candidate_uuid
            $actionData = $task->action_data;
            $actionData['job_candidate_uuid'] = $relation->uuid;
            $ongoingPipelineTemplateTask->action_data = $actionData;
            $ongoingPipelineTemplateTask->relation_uuid = $relation->user_uuid;

            // Get details from cache
            $cacheKey = "{$task->uuid}";
            if (Cache::has($cacheKey)) {
                $ongoingPipelineTemplateTask->status = OngoingPipelineTemplateTask::STATUS_IN_PROGRESS;
                Cache::forget($cacheKey);
            }

            if (empty($task->parent_uuid)) {
                $ongoingPipelineTemplateTask->parent_uuid = null;
                $ongoingPipelineTemplateTask->responsibility_uuid = $responsibility_uuid;

                if (!empty($extra["reference_uuid"])) {
                    $ongoingPipelineTemplateTask->reference_uuid = $extra["reference_uuid"];
                }
            } else if (!empty($parentUuid)) {
                $ongoingPipelineTemplateTask->parent_uuid = $parentUuid;
            }

            if ($oldSource === $task->source) {
                $ongoingPipelineTemplateTask->responsibility_uuid = $responsibility_uuid;
            }

            $ongoingPipelineTemplateTask->save();
            $parentUuid = $ongoingPipelineTemplateTask->uuid;
            $oldSource = $ongoingPipelineTemplateTask->source;
        }
    }
}
