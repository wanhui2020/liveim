<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/4/16
 * Time: 10:22
 */

namespace App\Http\Controllers\Api\Auth;


use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\CustomerRequest;
use App\Repositories\CustomerUserRepository;

class AuthController extends ApiController
{

    public function __construct(CustomerUserRepository $repository)
    {
        $this->repository = $repository;
    }


    public function login(CustomerRequest $request)
    {
        try {
            $data = $request->all();

            return $this->repository->login($data);
        } catch (\Exception $e) {

            return $this->exception($e);
        }
    }


    public function register(CustomerRequest $request)
    {
        try {
            $data = $request->all();
            return $this->repository->register($data);
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }


    public function forget(CustomerRequest $request)
    {
        try {
            $data = $request->all();
            return $this->repository->forget($data);
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }
}
