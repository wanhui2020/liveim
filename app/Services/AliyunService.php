<?php

namespace App\Services;

use App\Facades\OssFacade;
use App\Models\MemberAccount;
use App\Models\MemberInfo;
use App\Models\MemberRealName;
use App\Traits\ResultTrait;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

//阿里云
class AliyunService
{
    use ResultTrait;
    private $cloud;
    private $scene;

    public function __construct()
    {
        $this->scene = 'weiyoulive';
        try {
            $this->cloud = AlibabaCloud::accessKeyClient('LTAI4FvzGixgQ95fu7HezU5X', '3kefhmpwFz9shVzZqRrmEx9e0YRSUV')->regionId('cn-hangzhou')->asDefaultClient();
        } catch (ClientException $e) {
            $this->exception($e);
        }
    }

    /**
     * 人脸适别
     * @param $memberId
     * @param $idcard
     * @param $name
     * @return array
     */
    public function DescribeVerifyToken($memberId)
    {
        try {
            // 访问产品 APIs
            $request = AlibabaCloud::Cloudauth()->V20190307()->DescribeVerifyToken();

            $result = $request->withBizType($this->scene)
                ->withBizId($memberId)
//                ->withIdCardNumber($idcard)
//                ->withName($name)
                ->format('JSON')
                ->connectTimeout(10)
                ->timeout(10)
                ->request();
            return $this->succeed($result->toArray());
        } catch (ClientException $e) {
            return $this->exception($e);
        }
    }

    public function DescribeVerifyResult($memberId)
    {
        try {
            $member = MemberInfo::where('realname_id',$memberId)->first();
            if (!isset($member)) {
                return $this->validation('数据不存在' . $memberId);
            }
            // 访问产品 APIs
            $request = AlibabaCloud::Cloudauth()->V20190307()->DescribeVerifyResult();
            $result = $request->withBizType($this->scene)//创建方法参见业务设置
            ->withBizId($member->realname_id)
                ->connectTimeout(10)
                ->timeout(10)
                ->format('JSON')
                ->request();
            $data = $result->toArray();
            if ($data['VerifyStatus'] == 1) {
                $Material = $data['Material'];
                $this->logs('$Material', $Material);
                if (isset($Material)) {
                    $realName = MemberRealName::firstOrNew(['member_id' => $member->id]);
                    if (isset($Material['IdCardName'])) {
                        $realName->name = $Material['IdCardName'];
                    }
                    if (isset($Material['IdCardNumber'])) {

                        $realName->cert_no = $Material['IdCardNumber'];
                    }
                    if (isset($Material['FaceImageUrl'])) {
                        $putBackImageUrl = OssFacade::putUrl($Material['FaceImageUrl']);
                        if ($putBackImageUrl['status']) {
                            $realName->selfie_pic = $putBackImageUrl['src'];
                        }

                    }
                    if (isset($Material['IdCardInfo'])) {
                        $IdCardInfo = $Material['IdCardInfo'];
                        if (isset($IdCardInfo['FrontImageUrl'])) {
                            $putBackImageUrl = OssFacade::putUrl($IdCardInfo['FrontImageUrl']);
//                            $this->logs('$putBackImageUrl',$putBackImageUrl);
                            if ($putBackImageUrl['status']) {
                                $realName->cert_zm = $putBackImageUrl['src'];
                            }

                        }
                        if (isset($IdCardInfo['BackImageUrl'])) {
                            $putBackImageUrl = OssFacade::putUrl($IdCardInfo['BackImageUrl']);
                            if ($putBackImageUrl['status']) {
                                $realName->cert_fm = $putBackImageUrl['src'];
                            }
                        }
                    }


                    $realName->status = 0;
                    $realName->save();
                }
                return $this->succeed($Material);
            }
            return $this->succeed();
        } catch (ClientException $e) {
            return $this->exception($e);
        } catch (ServerException $e) {
            return $this->exception($e);
        }
    }

}
