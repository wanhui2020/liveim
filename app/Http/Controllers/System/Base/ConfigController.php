<?php
/**
 * 系统参数
 */

namespace App\Http\Controllers\System\Base;

use App\Facades\BaseFacade;
use App\Models\SystemConfig;
use App\Repositories\SystemConfigRepository;
use App\Utils\Helper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ConfigController extends Controller
{
    public function __construct(SystemConfigRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 页面首页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        try {   //显示只有一条数据，写在配置文件里面
            $result = $this->repository->firstOrCreate(array('id' => 1));
            return view('system.base.config.edit')->with('config', json_encode($result['data']));
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    /**
     * 更新系统参数配置
     * @param Request $request
     * @return array|mixed
     */
    public function update(Request $request)
    {
        try {
            $data = $request->all();
            $result = $this->repository->update($data);
            $user = Auth::user()->name;
            Log::info('系统参数修改', ['用户' => $user,  '更改时间' => Helper::getNowTime()]);
            return $result;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

}