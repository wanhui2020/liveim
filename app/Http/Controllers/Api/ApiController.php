<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ResultTrait;

class ApiController extends Controller
{
    use ResultTrait;
    public $repository;
}
