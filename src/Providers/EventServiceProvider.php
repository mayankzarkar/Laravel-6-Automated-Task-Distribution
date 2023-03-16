<?php

namespace TaskManagement\Providers;

use TaskManagement\Events\ReminderEvent;
use TaskManagement\Listeners\ReminderListener;
use TaskManagement\Events\HandleOfferGroupEvent;
use TaskManagement\Events\HandleStageGroupEvent;
use TaskManagement\Events\HandleMoveCandidateEvent;
use TaskManagement\Events\HandleTaskStatusGroupEvent;
use TaskManagement\Listeners\HandleOfferGroupListener;
use TaskManagement\Listeners\HandleStageGroupListener;
use TaskManagement\Listeners\HandleMoveCandidateListener;
use TaskManagement\Listeners\HandleTaskStatusGroupListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @uses   The event listener mappings for the application.
     * @var $listen array
     */
    protected $listen = [
        ReminderEvent::class => [
            ReminderListener::class
        ],
        HandleStageGroupEvent::class => [
            HandleStageGroupListener::class
        ],
        HandleTaskStatusGroupEvent::class => [
            HandleTaskStatusGroupListener::class
        ],
        HandleMoveCandidateEvent::class => [
            HandleMoveCandidateListener::class
        ],
        HandleOfferGroupEvent::class => [
            HandleOfferGroupListener::class
        ]
    ];

    /**
     * @uses   Register any events for your application.
     * @return  void
     */
    public function boot()
    {
        parent::boot();
    }
}
