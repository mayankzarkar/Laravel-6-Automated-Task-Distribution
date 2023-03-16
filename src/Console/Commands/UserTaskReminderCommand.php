<?php
namespace TaskManagement\Console\Commands;

use Illuminate\Console\Command;
use TaskManagement\Jobs\SendUserTaskReminder;

class UserTaskReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'App:user-task-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for send reminder to user.';

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
        dispatch(new SendUserTaskReminder);
        $this->info("Done");
    }
}
