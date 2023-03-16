<?php
namespace TaskManagement\Listeners;

use Carbon\Carbon;
use Helper\Constants\Queue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\ats\Foundation\JobCandidateCollection;
use TaskManagement\Events\HandleMoveCandidateEvent;

class HandleMoveCandidateListener implements ShouldQueue
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

    /**
     * @uses the handle the event.
     * @return void
     */
    public function handle(HandleMoveCandidateEvent $event): void
    {
        $cacheKey = "{$event->candidate->uuid}|{$event->stage->uuid}";
        if (Cache::has($cacheKey)) {
            Cache::forget($cacheKey);
        }

        Cache::put($cacheKey, ["responsibility" => $event->responsibility], Carbon::now()->endOfDay());
        JobCandidateCollection::moveCandidates(
            [$event->candidate->uuid],
            $event->stage->job_uuid,
            $event->stage->uuid,
            ""
        );
    }

    /**
     * @param \Exception $exception
     */
    public function failed(\Exception $exception): void
    {
        Log::channel('task_management')->error('handle-pipeline-task-action-listener', [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTrace()
        ]);
    }
}
