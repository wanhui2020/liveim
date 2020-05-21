<?php

namespace App\Http\Controllers\System\PlatForm;

use App\Http\Controllers\Controller;
use App\Models\PlatformPayment;
use App\Repositories\PlatformPaymentRepository;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(PlatformPaymentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 后台配置线下支付方式首页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('system.platform.payment.index');
    }

    /**
     * 平台支付方式列表
     * @return array
     */
    public function lists()
    {
        try {
            $lists = $this->repository->lists();

            return $this->paginate($lists);
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    /**
     *  线下支付方式添加页面
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('system.platform.payment.create');
    }

    /**
     * 添加支付方式
     * @param Request $request
     * @return array
     */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            unset($data['file']);


            $data['name'] = config('feildmap.pay_type')[$data['type']];
            $result = $this->repository->store($data);
            if ($result['status']) {
                return $this->succeed(null, '添加成功');
            } else {
                return $this->failure(1, '添加失败');
            }
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    /**
     * 支付方式的启用、禁用
     * @param Request $request
     * @return array
     */
    public function powerable(Request $request)
    {
        try {
            $systempay = PlatformPayment::findOrFail($request->id);
            if ($systempay) {
                $systempay->status = $systempay->status == 1 ? 0 : 1;
                if ($systempay->save()) {
                    return $this->succeed('操作成功');
                } else {
                    return $this->validation('操作失败');
                }
            } else {
                return $this->validation('支付不存在');
            }

        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    /**
     * 支付方式修改页面
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request)
    {
        try {
            $cons = $this->repository->find($request->id);
            return view('system.platform.payment.edit', compact('cons'));
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    // 支付方式修改
    public function update(Request $request)
    {
        try {
            $data = $request->all();
            unset($data['file']);
            $result = $this->repository->update($data);
            return $result;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }


    /**
     * 删除
     * @param Request $request
     * @return array|mixed
     */
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
