<?php

namespace TaskManagement\Http\Controllers;

use App\Traits\AuthUser;
use Helper\Http\Controllers\BaseController as Controller;

class BaseController extends Controller
{
    use AuthUser;
}
