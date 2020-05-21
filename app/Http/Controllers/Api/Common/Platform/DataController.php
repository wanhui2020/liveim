<?php


namespace App\Http\Controllers\Api\Common\Platform;

use App\Facades\CommonFacade;
use App\Http\Controllers\Api\ApiController;
use App\Http\Repositories\SystemDataRepository;
use App\Http\Repositories\SystemTagRepository;
use App\Http\Resources\SystemDataResource;
use App\Models\MemberRecharge;
use App\Models\SystemData;
use App\Utils\SelectList;
use Illuminate\Http\Request;

//平台基础数据接口
class DataController extends ApiController
{
    public function __construct(SystemDataRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 获取支付方式
     */
    public function getpay(Request $request)
    {
        try {
            $user = $request->user('api');
            if (!isset($user)){
                return $this->validation('api_token错误');
            }
            $type = $request['type'];
            if (!isset($type)) {
                return $this->validation('类型参数错误！');
            }
            if ($type!=10) {
                return $this->validation('类型参数为10！');
            }
            $recharge = new MemberRecharge();
            //way  6 微信原生支付  9 支付宝原生支付
            $wechatcon = $recharge->where('member_id',$user->id)->where('way',6)->where('status',1)->count(); //当前用户微信原生支付成功的次数
            $alicon = $recharge->where('member_id',$user->id)->where('way',9)->where('status',1)->count(); //当前用户支付宝原生支付成功的次数

            $wechatnum = $recharge->where('member_id',$user->id)->where('way',6)->where('status',1)->sum('amount');//当前用户微信原生支付成功的金额
            $alinum = $recharge->where('member_id',$user->id)->where('way',9)->where('status',1)->sum('amount');//当前用户支付宝原生支付成功的金额

            $othercon = $recharge->where('member_id',$user->id)->whereNotIn('way',[6,9])->where('status',1)->count(); //当前用户使用除了支付宝微信其他支付方式

            //查询使用哪一个恒云支付
            $hy1 = $recharge->where('way',10)->where('status',1)->count(); //恒云1支付成功的次数
            $hy2 = $recharge->where('way',11)->where('status',1)->count(); //恒云2支付成功的次数
            $hy3 = $recharge->where('way',12)->where('status',1)->count(); //恒云3支付成功的次数
            $hy4 = $recharge->where('way',13)->where('status',1)->count(); //恒云4支付成功的次数
            $hy5 = $recharge->where('way',14)->where('status',1)->count(); //恒云5支付成功的次数
            $arr = array(
                10=>$hy1,
                11=>$hy2,
                12=>$hy3,
                13=>$hy4,
                14=>$hy5,
            );
            asort($arr);
            $systemdatas = SystemData::whereIn('key',[10,11,12,13,14])->where('status',1)->get();
            $arr1 = array();
            foreach ($systemdatas as $kk=>$vv){
                $arr1[$vv['key']] = $vv['key'];
            }
            $num1 = null;
            foreach ($arr as $k=>$v){
                if (in_array($k,$arr1)){
                    $num1 = $k;
                    break;
                }
            }
            $where = function ($query) use ($othercon,$num1) {
                if ($othercon < 2){  //使用除了支付宝微信其他支付方式
                    $query->whereNotIn('key',[6,9]);
                }
                $query->whereIn('key',[6,8,9,20,21,22,$num1]);
               /* if ($alicon < 2){ //支付宝原生支付次数
                    $query->where('key','!=',9);
                }
                if ($wechatnum < 200){  //微信原生支付金额
                    $query->where('key','!=',6);
                }
                if ($alinum < 200){ //支付宝原生支付金额
                    $query->where('key','!=',9);
                }*/
            };
            $noun = $this->repository
                ->where($where)
                ->lists(function ($query){
                $query->where('status',1);
            });
            return $this->succeed(SystemDataResource::collection($noun));
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    //通过类型
    public function getListByType(Request $request)
    {
        try {
            $user = $request->user('api');
            if (!isset($user)){
                return $this->validation('api_token错误');
            }
            $type = $request['type'];
            if (!in_array($type, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10 ,11], false)) {
                return $this->validation('类型参数错误！');
            }
            $noun = $this->repository->orderBy('value', 'asc')->findWhere(['type' => $type, 'status' => 1]);
            return $this->succeed(SystemDataResource::collection($noun));
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    //获取分类标签列表
    public function getTags(Request $request, SystemTagRepository $tagRepository)
    {
        try {
            $noun = $tagRepository->orderBy('sort', 'asc')->findWhere(['is_sys' => 1, 'status' => 1]);
            $request['view_type'] = 'tags';
            return $this->succeed(SystemDataResource::collection($noun));
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }
}
