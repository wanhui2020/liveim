<?php

namespace App\Http\Controllers\System\Platform;

use App\Models\SystemBasic;
use App\Repositories\SystemBasicRepository;
use App\Utils\Helper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ConfigController extends Controller
{

    public function __construct(SystemBasicRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     *  平台参数修改页面
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
    */
    public function edit()
    {
        try {   //显示只有一条数据，写在配置文件里面
            $result = $this->repository->firstOrCreate(array('id' => 1));
            return view('system.platform.config.edit')->with('config', json_encode($result['data']));
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    /**
     *  保存修改的平台参数
     * @param Request $request
     * @return array
     */
    public function update(Request $request)
    {
        try {
            $data = $request->all();
            $result = $this->repository->update($data);
            $config = SystemBasic::firstOrCreate(['id' => 1]);
            $config = json_decode($config, JSON_UNESCAPED_UNICODE);
            $user = Auth::user()->name;
            $this->logs('平台参数修改', ['用户' => $user, '原参数' => $config, '改为' => $data, '更改时间' => Helper::getNowTime()]);
            return $result;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }
}
