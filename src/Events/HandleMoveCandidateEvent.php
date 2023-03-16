<?php

namespace TaskManagement\Events;

use User\Entities\User;
use App\ats\Entities\Stages;
use Illuminate\Queue\SerializesModels;
use App\ats\Entities\JobCandidate;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use App\candidate\Entities\User as CandidateUser;

/**
 * @param Stages $stage
 * @param JobCandidate $candidate
 * @param User|CandidateUser $responsibility
 */
class HandleMoveCandidateEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var Stages $stage */
    public $stage;

    /** @var JobCandidate $candidate */
    public $candidate;

    /** @var User|CandidateUser $responsiblity */
    public $responsibility;

    /**
     * @param JobCandidate $candidate
     * @param Stages $stage
     * @param mixed $responsibility
     * @return void
     */
    public function __construct(JobCandidate $candidate, Stages $stage, $responsibility)
    {
        $this->candidate = $candidate;
        $this->stage = $stage;
        $this->responsibility = $responsibility;
    }
}
