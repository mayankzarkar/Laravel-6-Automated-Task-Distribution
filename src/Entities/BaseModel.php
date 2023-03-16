<?php

namespace TaskManagement\Entities;

use Helper\Entities\BaseModel as Model;

class BaseModel extends Model {

    /**
     * @note The connection name for the model.
     * @var string
     */
    protected $connection = 'service.task_management.read';
}
