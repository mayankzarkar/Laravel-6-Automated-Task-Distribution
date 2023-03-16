<?php
namespace TaskManagement\Console\Commands;

use Illuminate\Console\Command;
use TaskManagement\Constants\Actions;
use TaskManagement\Constants\TaskTypes;
use TaskManagement\Entities\PipelineTemplateTask;
use TaskManagement\Entities\UserTask;

class FixTaskTypeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'App:fix-task-management-type-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for fix task type.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        UserTask::whereNull("type")->update(["type" => TaskTypes::TO_DO]);
        $tasks = PipelineTemplateTask::where("action", Actions::ACTION_CREATE_TASK)->get();
        foreach ($tasks as $task) {
            $actionData = $task->action_data;
            if (empty($actionData)) {
                continue;
            }

            unset($actionData['type_uuid']);
            $actionData['type'] = TaskTypes::TO_DO;
            $task->action_data = $actionData;
            $task->save();
        }

        $this->info("Done");
    }
}
