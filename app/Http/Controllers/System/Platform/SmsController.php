<?php
/**
 *系统消息
 */

namespace App\Http\Controllers\System\Platform;

use App\Facades\CommonFacade;
use App\Http\Controllers\Controller;
use App\Http\Repositories\SmsRepository;
use App\Models\Sms;
use App\Utils\SelectList;
use Illuminate\Http\Request;

/*
 * 短信管理控制器
 * */

class SmsController extends Controller
{
    public function __construct(SmsRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        $type = SelectList::smsType();
        return view('system.platform.sms.index', compact('type'));
    }

    /*
    * 显示列表
    * */
    public function lists()
    {
        try {
            $list = $this->repository->lists();
            return $this->paginate($list);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /*
     * 添加（发送短信）
     * */
    public function create()
    {
        $type = SelectList::smsType();
        return view('system.platform.sms.create', compact('type'));
    }

    /*
    * 添加到数据库
    * */
    public function store(Request $request)
    {
        try {
            $data = $request->all();

            if ($data['type'] == 0) {
                //验证码短信，先查询1分钟内有没有验证码
                $dateTime = date("Y-m-d H:i:s", strtotime("-1 minute"));
                $smsModel = Sms::where(['phone' => $data['phone'], 'type' => 0])->where('created_at', '<', $dateTime)->orderBy('created_at', 'desc')->first();
                if (isset($smsModel)) {
                    return $this->validation('1分钟后再重新发送');
                }
                $verfiyCode = CommonFacade::randStr(6, 'NUMBER'); //6位随机数
                $data['verify_code'] = $verfiyCode;
                $data['content'] = '验证码:' . $verfiyCode . '。请勿泄露给他人。';
            }
            $result = $this->repository->store($data);
            if ($result['status']) {

                //调用短信接口发送
                // ....

                return $this->succeed($result);
            }
            return $this->failure(1, $result['msg']);
        } catch (\Exception $ex) {

            return $this->exception($ex);
        }
    }

    /*
     * 删除
     * */
    public function destroy(Request $request)
    {
        try {
            $result = $this->repository->destroy($request->ids);
            return $result;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }
}
