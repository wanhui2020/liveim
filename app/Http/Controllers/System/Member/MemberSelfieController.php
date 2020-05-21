<?php
/**
 *  会员自拍认证管理控制器
 */

namespace App\Http\Controllers\System\Member;

use App\Facades\CommonFacade;
use App\Facades\MemberFacade;
use App\Http\Controllers\Controller;
use App\Models\MemberBusiness;
use App\Models\MemberFile;
use App\Models\MemberGroup;
use App\Models\MemberInfo;
use App\Models\MemberTags;
use App\Models\SystemTag;
use App\Repositories\MemberInfoRepository;
use App\Repositories\MemberRecordRepository;
use App\Repositories\MemberSelfieRepository;
use App\Utils\Helper;
use App\Utils\SelectList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MemberSelfieController extends Controller
{
    public function __construct(MemberSelfieRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        $status = SelectList::checkStatus();
        return view('member.selfie.index', compact('status'));
    }

    /*
    *  显示列表(获取数据)
    * */
    public function lists()
    {
        try {
            $list = $this->repository->lists(null, ['member']);
            return $this->paginate($list);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /*
     * 添加视图
     * */
    public function create()
    {
        $member = MemberInfo::where('selfie_check', 0)->get(['id', 'code', 'user_name', 'nick_name']); //会员
        return view('member.selfie.create', compact('member'));
    }

    /*
    * 保存到数据库
    * */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $result = $this->repository->store($data);
            if ($result['status']) {
                if ($request["is_business"] == 1) {
                    //同步到商务认证,先查看会员是否已进行了商务认证
                    $member = MemberInfo::find($data['member_id']);
                    if ($member->business_check == 0) {
                        $busi = new MemberBusiness();
                        $busi->member_id = $data['member_id'];
                        $busi->pic = $data['pic'];
                        $busi->save();
                    }
                }
                return $this->succeed($result);
            }
            return $this->failure(1, $result['msg']);
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

    /*
     * 处理审核视图
     * */
    public function deal(Request $request)
    {
        $model = $this->repository->find($request->id);
        $tags = SystemTag::where(['status' => 1, 'is_sys' => 1])->orderBy('sort', 'desc')->get(['id', 'name']);
        return view('member.selfie.deal', compact('tags'))->with('cons', $model);
    }


    /*
    * 处理保存
    * */
    public function dealSave(Request $request, MemberInfoRepository $memberInfoRepository)
    {
        try {
            $data = $request->all();
            $status = $data['status']; //状态
            $data['deal_user'] = Auth::user()->name;
            $data['deal_time'] = Helper::getNowTime();
            $result = $this->repository->update($data);
            if ($result['status']) {
                //成功后进行处理，对通过的进行处理
                if ($status == 1) {
                    $tagStr = rtrim($request['tagstr'], ',');
                    if ($tagStr != '') {
                        MemberFacade::memberToTag($data['member_id'], $tagStr);
                    }
                    $istj = $data['is_recommend'];
                    $result = $memberInfoRepository->update(['id' => $data['member_id'], 'selfie_check' => 1, 'is_recommend' => $istj]);

                    //同时添加自拍照为封面图
                    $memberFile = new MemberFile();
                    $memberFile->member_id = $data['member_id'];
                    $memberFile->type = 1;
                    $memberFile->url = $data['pic'];
                    $memberFile->is_cover = 1;
                    $memberFile->status = 1;
                    $memberFile->deal_user = $data['deal_user'];
                    $memberFile->deal_time = $data['deal_time'];
                    $memberFile->save();
                }
                return $this->succeed($result);
            }
            return $result;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

}