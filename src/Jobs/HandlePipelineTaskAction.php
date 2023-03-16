<?php
namespace TaskManagement\Jobs;

use Helper\Constants\Queue;
use Illuminate\Support\Carbon;
use service\mail\Foundation\Mail;
use App\ats\Entities\Stages;
use Illuminate\Support\Facades\Log;
use TaskManagement\Constants\Enums;
use service\mail\Facade\MailService;
use TaskManagement\Constants\Actions;
use TaskManagement\Constants\Sources;
use TaskManagement\Entities\UserTask;
use App\ats\Entities\JobCandidate;
use language\service\Entities\Language;
use service\mail\Entities\EmailTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;
use TaskManagement\Events\HandleMoveCandidateEvent;
use TaskManagement\Entities\OnGoingPipelineTemplateTask;

class HandlePipelineTaskAction implements ShouldQueue
{
    /**
     * @Development_Group: Backend Developer
     * @Description  The name of the queue the job should be sent to.
     * @var string
     */
    public $queue = Queue::TASK_MANAGEMENT_QUEUE;

    /**
     * @Development_Group: Backend Developer
     * @Description  The time (seconds) before the job should be processed.
     * @var int
     */
    public $delay = Queue::TASK_MANAGEMENT_QUEUE_DELAY;

    /** @var OnGoingPipelineTemplateTask $ongoingTask */
    private $ongoingTask;

    public function __construct(OnGoingPipelineTemplateTask $ongoingTask)
    {
        $this->ongoingTask = $ongoingTask;
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle(): void
    {
        switch ($this->ongoingTask->action) {
            case Actions::ACTION_CREATE_TASK:
                $this->createTask();
                break;

            case Actions::ACTION_SEND_EMAIL:
                $this->sendEmail();
                break;

            default:
                $this->moveCandidateToStage();
                break;
        }

        $this->ongoingTask->markAsCompleted()->save();
    }

    /**
     * @param \Exception $exception
     */
    public function failed(\Exception $exception): void
    {
        $this->ongoingTask->markAsFailed()->save();
        Log::channel('task_management')->error('handle-pipeline-task-action-command', [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTrace()
        ]);
    }

    /**
     * Move candidate to another stage
     */
    private function moveCandidateToStage(): void
    {
        $stage_uuid = $this->ongoingTask->action_data['stage_uuid'];
        $action_data = $this->ongoingTask->action_data;

        $jobCandidate = JobCandidate::findByPk($action_data['job_candidate_uuid']);
        $stage = Stages::where("origin_stage_uuid", $stage_uuid)
            ->where("job_uuid", $jobCandidate->job_uuid)
            ->first();

        event(new HandleMoveCandidateEvent($jobCandidate, $stage, $this->ongoingTask->responsibility));
    }

    /**
     * Create task
     */
    private function createTask(): void
    {
        $actionData = $this->ongoingTask->action_data;
        $jobCandidate = JobCandidate::findByPk($actionData['job_candidate_uuid']);
        if (!empty($actionData['responsibility_type']) && $actionData['responsibility_type'] == Enums::REQUESTER) {
            $responsibility = $jobCandidate->job->JobRequisition ? $jobCandidate->job->JobRequisition->userRelation : $jobCandidate->job->userRelation;
            $actionData['responsibility_uuid'] = $responsibility->uuid;
            $actionData['responsibility_type'] = Enums::RECRUITER;
            if ($responsibility->isEmployee()) {
                $actionData['responsibility_type'] = Enums::EMPLOYEE;
            }
        }

        $actionData['additional_data']['job_uuid'] = $jobCandidate->job_uuid;
        if (!empty($actionData["duration"]) && is_integer($actionData["duration"])) {
            $currentDatetime = Carbon::now();
            $actionData["start_date"] = $currentDatetime->format('Y-m-d H:i:s');
            $actionData["due_date"] = $currentDatetime->addDays($actionData["duration"])->format('Y-m-d H:i:s');
        }

        $task = UserTask::create($actionData + [
            'account_uuid' => $this->ongoingTask->pipelineTemplate->account_uuid,
            'responsibility_type' => $actionData["responsibility_type"] ?: $this->ongoingTask->responsibility_type,
            'responsibility_uuid' => $actionData["responsibility_uuid"] ?: $this->ongoingTask->responsibility_uuid,
            'relation_uuid' => $this->ongoingTask->relation->uuid,
            'created_by' => $this->ongoingTask->created_by
        ]);

        $childTask = $this->ongoingTask->child;
        if (!empty($childTask) && $childTask->source == Sources::TASK_AND_ACTIONS) {
            do {
                $childTask->reference_uuid = $task->uuid;
                $childTask->save();
                $childTask = $childTask->child;
            } while(!empty($childTask) && $childTask->source == Sources::TASK_AND_ACTIONS && $childTask->is_grouped);

        }
    }

    /**
     * send email
     */
    private function sendEmail(): void
    {
        $action_data = $this->ongoingTask->action_data;
        if (!empty($action_data['template_uuid'])) {
            $language = Language::findByCode("en");
            $emailTemplate = EmailTemplate::findByPk($action_data['template_uuid']);
            $translation = $emailTemplate->translation->filter(function ($element) use ($language) {
                return $element->language_id == $language->uuid;
            });

            $action_data['body'] = $translation->body;
            $action_data['subject'] = $translation->subject;
        }

        switch ($action_data['receiver_type']) {
            /*case Actions::SEND_EMAIL_TYPE_EMPLOYEE:
            case Actions::SEND_EMAIL_TYPE_RECRUITER:
                $recruiter = $this->ongoingTask->responsibility;
                $toEmail = $recruiter->email;
                $toName = $recruiter->getFullName();
                break;*/

            case Actions::SEND_EMAIL_TYPE_OTHER:
                $toEmail = $action_data['to_email'];
                $toName = $action_data['to_name'];
                break;

            default:
                $relation = $this->ongoingTask->relation;
                $toEmail = $relation->email;
                $toName = $relation->getFullName();
                break;
        }

        MailService::build(
            Mail::getFromEmail(),
            Mail::getFromName(),
            $toEmail,
            $toName,
            $action_data['subject'],
            $action_data['body']
        )->send();
    }
}
