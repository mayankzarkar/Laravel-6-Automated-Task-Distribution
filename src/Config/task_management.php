<?php

use Helper\Foundation\Collection;

return [
    'active_routes' => env('REGISTER_ROUTE_TASK_MANAGEMENT_SERVICE', true),
    'active_event' => env('REGISTER_EVENT_TASK_MANAGEMENT_SERVICE', true),
    'active_middleware' => env('REGISTER_MIDDLEWARE_TASK_MANAGEMENT_SERVICE', true),
    'task_management_url' => Collection::getSelfServiceUrl() . "home/tasks"
];
