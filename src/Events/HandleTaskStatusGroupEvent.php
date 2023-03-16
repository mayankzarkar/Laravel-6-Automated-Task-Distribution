<?php

namespace TaskManagement\Events;

use User\Entities\User;
use TaskManagement\Constants\Enums;
use TaskManagement\Entities\UserTask;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use App\candidate\Entities\User as CandidateUser;

/**
 * @param UserTask $user_task
 * @param string $account_uuid
 * @param User|CandidateUser $responsibility
 * @param int $responsibility_type
 */
class HandleTaskStatusGroupEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var UserTask $user_task */
    public $user_task;

    /** @var string $account_uuid */
    public $account_uuid;

    /** @var User|CandidateUser $responsiblity */
    public $responsibility;

    /** @var int $responsibility_type */
    public $responsibility_type;

    /**
     * @param UserTask $userTask
     * @param mixed $responsibility
     * @return void
     */
    public function __construct(UserTask $userTask, $responsibility)
    {
        $this->user_task = $userTask;
        $this->handleResponsibility($responsibility);
    }

    private function handleResponsibility($responsibility): void
    {
        $this->responsibility = $responsibility;
        if ($responsibility instanceof User) {
            $this->responsibility_type = !is_null($responsibility->employee_uuid) ? Enums::EMPLOYEE : Enums::RECRUITER;
        } else {
            $this->responsibility_type = Enums::CANDIDATE;
        }

        $this->account_uuid = $responsibility->account_uuid;
    }
}
