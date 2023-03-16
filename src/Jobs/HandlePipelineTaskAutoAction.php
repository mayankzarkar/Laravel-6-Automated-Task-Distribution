<?php
namespace TaskManagement\Jobs;

use Helper\Constants\Queue;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use TaskManagement\Entities\OngoingPipelineTemplateTask;

class HandlePipelineTaskAutoAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var string $uuid */
    private $uuid;

    /**
     * @param string $uuid
     */
    public function __construct(string $uuid)
    {
        $this->uuid = $uuid;
        $this->onQueue(Queue::TASK_MANAGEMENT_QUEUE);
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle(): void
    {
        $ongoingTask = OngoingPipelineTemplateTask::getwithTrashed($this->uuid);
        if ($ongoingTask->isPending() && !$ongoingTask->trashed()) {
            dispatch(new HandlePipelineTaskAction($ongoingTask));
        }
    }

    /**
     * @param \Exception $exception
     */
    public function failed(\Exception $exception): void
    {
        Log::channel('task_management')->error('handle-pipeline-task-auto-action-command', [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTrace()
        ]);
    }
}
