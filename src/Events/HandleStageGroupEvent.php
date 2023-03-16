<?php

namespace TaskManagement\Events;

use User\Entities\User;
use TaskManagement\Constants\Enums;
use Illuminate\Queue\SerializesModels;
use App\ats\Entities\JobCandidate;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use App\candidate\Entities\User as CandidateUser;

/**
 * @param string $stage_uuid
 * @param string $account_uuid
 * @param string $pipeline_uuid
 * @param JobCandidate $relation
 * @param User|CandidateUser $responsibility
 * @param int $responsibility_type
 */
class HandleStageGroupEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var String $stage_stage */
    public $stage_uuid;

    /** @var String $account_uuid */
    public $account_uuid;

    /** @var String $pipeline_uuid */
    public $pipeline_uuid;

    /** @var JobCandidate $relation */
    public $relation;

    /** @var User|CandidateUser $responsiblity */
    public $responsibility;

    /** @var int $responsibility_type */
    public $responsibility_type;

    /**
     * @param JobCandidate $relation
     * @param mixed $responsibility
     * @return void
     */
    public function __construct(JobCandidate $relation, $responsibility)
    {
        $this->relation = $relation;
        $this->handleResponsibility($responsibility);
        $this->pipeline_uuid = $relation->stage->pipeline->origin_pipeline_uuid;
        $this->stage_uuid = $relation->stage->origin_stage_uuid;
        if (empty($this->account_uuid)) {
            $this->account_uuid = $relation->job->account_uuid;
        }
    }

    private function handleResponsibility($responsibility): void
    {
        $this->responsibility = $responsibility;
        $this->responsibility_type = Enums::CANDIDATE;
        if ($responsibility instanceof User && (strpos($responsibility->getConnectionName(), "core") === false)) {
            $this->responsibility_type = !is_null($responsibility->employee_uuid) ? Enums::EMPLOYEE : Enums::RECRUITER;
        }

        $this->account_uuid = $responsibility->account_uuid;
    }
}
