<?php

namespace TaskManagement\Events;

use User\Entities\User;
use formbuilder\Entities\Offer;
use TaskManagement\Constants\Enums;
use Illuminate\Queue\SerializesModels;
use App\ats\Entities\JobCandidate;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use TaskManagement\Foundation\TaskManagementFilterInterface;

/**
 * @param string $account_uuid
 * @param string $pipeline_uuid
 * @param JobCandidate $job_candidate
 * @param TaskManagementFilterInterface $offer
 * @param User $responsibility
 * @param int $responsibility_type
 */
class HandleOfferGroupEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var String $account_uuid */
    public $account_uuid;

    /** @var null|String $pipeline_uuid */
    public $pipeline_uuid;

    /** @var null|JobCandidate $job_candidate */
    public $job_candidate;

    /** @var TaskManagementFilterInterface $offer */
    public $offer;

    /** @var User $responsiblity */
    public $responsibility;

    /** @var int $responsibility_type */
    public $responsibility_type;

    /**
     * @param TaskManagementFilterInterface $offer
     * @return void
     */
    public function __construct(TaskManagementFilterInterface $offer)
    {
        $this->offer = $offer;
        $this->account_uuid = $offer->account_uuid;
        if ($offer instanceof Offer) {
            $this->job_candidate = JobCandidate::getJobCandidateByUserUUIDAndJobUUID($offer->candidate_uuid, $offer->job_uuid);
        } else {
            $this->job_candidate = JobCandidate::findByPk($offer->getShareableCandidate());
        }

        $this->pipeline_uuid = $this->job_candidate ? $this->job_candidate->stage->pipeline->origin_pipeline_uuid : null;
        $this->handleResponsibility($offer);
    }

    private function handleResponsibility(TaskManagementFilterInterface $offer): void
    {
        $this->responsibility = ($offer instanceof Offer) ? $offer->userRelation : $offer->creator;
        $this->responsibility_type = Enums::CANDIDATE;
        if ($this->responsibility instanceof User && (strpos($this->responsibility->getConnectionName(), "core") === false)) {
            $this->responsibility_type = !is_null($this->responsibility->employee_uuid) ? Enums::EMPLOYEE : Enums::RECRUITER;
        }
    }
}
