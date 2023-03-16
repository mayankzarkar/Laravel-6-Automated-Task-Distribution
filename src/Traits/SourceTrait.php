<?php

namespace TaskManagement\Traits;

use formbuilder\Entities\Offer;
use Lookup\Entities\TaskStatus;
use TaskManagement\Constants\Groups;
use formbuilder\Entities\FormRequest;
use TaskManagement\Constants\Sources;
use Pipeline\Service\Entities\V2\Stage;
use TaskManagement\Entities\PipelineTemplateTask;

trait SourceTrait
{
    /**
     * @param string $pipeline_uuid
     * @param bool $is_grouped
     * @param string|null $parent_uuid
     * @return array
     */
    public static function GetSources(string $pipeline_uuid, bool $is_grouped, ?string $parent_uuid = null): array
    {
        $sources = [Sources::CANDIDATE_SOURCES];
        if (!empty($parent_uuid)) {
            $parentTask = PipelineTemplateTask::findByPk($parent_uuid);
            if ($parentTask->isActionIsCreateTask()) {
                $sources[] = array_merge(Sources::TASK_SOURCES, [
                    "source_value" => $parentTask->action_data['title']
                ]);
            }

            if ($is_grouped) {
                $source = array();
                $targetTask = $parentTask;
                while (!empty($targetTask) && $targetTask->source === Sources::TASK_AND_ACTIONS) {
                    $targetTask = $targetTask->parent;
                }

                if (!empty($targetTask) && $targetTask->isActionIsCreateTask()) {
                    $source = array_merge(Sources::TASK_SOURCES, [
                        "source_value" => $targetTask->action_data['title']
                    ]);
                }

                if (!empty($source)) {
                    $sources = [Sources::CANDIDATE_SOURCES, $source];
                }

                $sources = array_values(array_filter($sources, function ($source) use ($parentTask) {
                    if ($source['source_key'] == $parentTask->source) {
                        return true;
                    }

                    return false;
                }));

                $sources = array_map(function ($source) use ($parentTask) {
                    $groups = array_filter($source['source_operator_groups'], function ($group) use ($parentTask) {
                        if ($group['key'] == $parentTask->source_group) {
                            return true;
                        }

                        return false;
                    });
                    $source['source_operator_groups'] = array_values($groups);
                    return $source;
                }, $sources);
            }
        }

        return self::PrepareSources($sources, $pipeline_uuid);
    }

    private static function PrepareSources(array $sources, string $pipeline_uuid): array
    {
        return array_map(function ($source) use ($pipeline_uuid) {
            $source['source_operator_groups'] = array_map(function ($group) use ($pipeline_uuid) {
                switch ($group['key']) {
                    case Groups::GROUP_QUESTIONNAIRE:
                    case Groups::GROUP_VIDEO_ASSESSMENT:
                    case Groups::GROUP_INTERVIEW:
                    case Groups::GROUP_EVALUATION_FORM:
                    case Groups::GROUP_CANDIDATE_RATING:
                    case Groups::GROUP_PUSH_TO_HRMS:
                        $group['options'] = [];
                        break;
                    case Groups::GROUP_PROFILE:
                        $group['options'] = [
                            "type" => "string"
                        ];
                        break;

                    case Groups::GROUP_TASK_STATUS:
                        $group['options'] = TaskStatus::GetFilterOptions();
                        break;

                    case Groups::GROUP_OFFERS:
                        $group['options'] = Offer::GetFilterOptions();
                        break;

                    case Groups::GROUP_FORM:
                        $group['options'] = FormRequest::GetFilterOptions();
                        break;

                    default:
                        $group['options'] = Stage::GetFilterOptions();
                        $index = array_search("uuid", array_column($group['options']['params'], "key"));
                        if (!empty($group['options']['params'][$index])) {
                            $group['options']['params'][$index]['default_value'] = $pipeline_uuid;
                        }
                        break;
                }

                return $group;
            }, $source['source_operator_groups']);

            return $source;
        }, $sources);
    }
}
