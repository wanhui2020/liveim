<?php

namespace App\Http\Controllers\Api\Member;

use App\Facades\MemberFacade;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Member\MemberAccountResource;
use App\Http\Resources\Member\MemberFileResource;
use App\Models\MemberFile;
use App\Models\MemberFileView;
use App\Repositories\MemberFileRepository;
use App\Repositories\MemberInfoRepository;
use App\Services\MemberService;
use Illuminate\Http\Request;

//主播会员资源库管理
class MemberFileController extends ApiController
{

    public function __construct(MemberFileRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 主播魅照库（会员查看）
     * */
    public function lists(Request $request, MemberInfoRepository $memberInfoRepository)
    {
        try {
            //必须登录会员才能查看
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            $zbid = $request['zbid']; //主播Id
            if (!isset($zbid)) {
                return $this->validation('主播ID不能为空！');
            }
            $zbinfo = $memberInfoRepository->find($zbid);
            if ($zbinfo == null) {
                return $this->validation('未找到主播！');
            }
            $list = $this->repository->orderBy('sort', 'desc')->findWhere(['member_id' => $zbid, 'status' => 1]);
            $request['memberid'] = $member->id;
            $request['is_vip'] = $member->is_vip;
            $zbextend = $zbinfo->extend;
            $request['picture_view_fee'] = $zbextend['picture_view_fee'];
            $request['vido_view_fee'] = $zbextend['picture_view_fee'];
            return $this->succeed(MemberFileResource::collection($list));
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 查看资源库
     * */
    public function viewFile(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            //可修改字段
            $fileid = $request['fileid']; //资源ID
            if (!isset($fileid)) {
                return $this->validation('参数错误，请传入必填参数值！');
            }
            $file = $this->repository->find($fileid);
            if (!isset($file)) {
                return $this->validation('资源ID错误！');
            }
            //添加资源库查看记录
            $viewModel = new MemberFileView();
            $viewModel->member_id = $member->id;
            $viewModel->to_member_id = $file->member_id;
            $viewModel->member_file_id = $fileid;
            $viewModel->type = $file->type; //类型
//            $viewModel->save();
            return MemberFacade::viewFile($viewModel); //调用查看方法
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }

    }

    /*
     * 我的颜照库
     * */
    public function myLists(Request $request)
    {
        try {
            //必须登录会员才能查看
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            $type = $request['type'];
            $where = array(
                'member_id' => $member->id,
            );
            if (!is_null($type)) {
                $where['type'] = $type;
            }
            $list = $this->repository->orderBy('sort', 'desc')->findWhere($where)->where('status','<>',2);
            $request['view_place'] = 'mylists';
            return $this->succeed(MemberFileResource::collection($list));
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 主播上传颜照视频库
     * */
    public function addFile(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            $memberId = $member->id;
            $type = $request['type']; //类型0、视频 1、照片
            $url = $request['url']; //地址
            $iscover = $request['iscover']; //是否设为封面
            if (!isset($type) || !isset($url)) {
                return $this->validation('传入必填参数值！');
            }
            if (!in_array($type, [0, 1])) {
                return $this->validation('类型参数错误！');
            }
            if ($member->selfie_check == 0) {
                return $this->validation('请先进行自拍认证！');
            }
            $data['member_id'] = $memberId;
            $data['type'] = $type;
            $data['url'] = $url;
            $result = $this->repository->store($data);
            if (!$result['status']) {
                return $this->failure(1, '操作失败');
            }
            return $this->succeed();
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }

    }

    /*
    * 主播删除颜照库
    * */
    public function deleteFile(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $fileid = $request['fileid']; //删除颜照库
            if (!isset($fileid)) {
                return $this->validation('请输入所有必填参数！');
            }
            $result = $this->repository->destroy([$fileid]);
            if (!$result['status']) {
                return $this->failure(1, '操作失败');
            }
            return $this->succeed();
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
    * 设置魅库为封面照
    * */
    public function setCoverFile(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $fileid = $request['fileid']; //删除颜照库
            if (!isset($fileid)) {
                return $this->validation('请输入所有必填参数！');
            }
            $fileModel = $this->repository->find($fileid);
            if (!isset($fileModel)) {
                return $this->validation('未找到记录！');
            }
            if ($fileModel['type'] == 0) {
                return $this->validation('暂时只支持图片设为封面！');
            }
            //设置封面逻辑修改   将原来的是封面的修改为否，将现在的修改为是
            $memberfiles = MemberFile::where('member_id',$member->id)->where('is_cover',1)->get();
            if (isset($memberfiles)){
                foreach ($memberfiles as $memberfile){
                    $memberfile->is_cover = 0;
                    $memberfile->save();
                }
            }
            $fileModel['is_cover'] = 1;
//            if ($fileModel['is_cover'] == 0) {
//                $fileModel['is_cover'] = 1;
//            } else {
//                $fileModel['is_cover'] = 0;
//            }
            $result = $this->repository->update($fileModel);
            if (!$result['status']) {
                return $this->failure(1, '操作失败');
            }
            return $this->succeed();
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

}
