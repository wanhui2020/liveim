<?php


namespace App\Utils;


class SelectList
{
    //通用状态
    public static function statusList()
    {
        $arry = ['0' => '禁用', '1' => '正常'];
        return $arry;
    }

    //在线状态
    public static function onLineStatusList()
    {
        $arry = ['0' => '离线', '1' => '在线'];
        return $arry;
    }

    //忙碌状态
    public static function busyStatusList()
    {
        $arry = ['0' => '空闲', '1' => '忙碌'];
        return $arry;
    }

    //通用是否状态
    public static function yesOrNo()
    {
        $arry = [0 => '否', 1 => '是'];
        return $arry;
    }

    //基础数据类型
    public static function dataTypeList()
    {
        $list = [1 => '消息收费', 2 => '语音收费', 3 => '视频收费', 4 => '颜照库收费', 5 => '视频库收费', 6 => '换衣收费', 7 => '举报理由', 8 => '商务选项', 9 => '提现银行', 10 => '支付方式',11=>'退款理由'];
        return $list;
    }

    //文件存储地
    public static function fileStorage()
    {
        $list = [0 => '本地', 1 => '阿里云', 2 => '七牛云', 3 => '其他云'];
        return $list;
    }

    //会员资源库
    public static function fileLibrary()
    {
        $arry = [0 => '视频库', 1 => '颜照库'];
        return $arry;
    }

    //充值列类型
    public static function recType()
    {
        $arry = [0 => '充值', 1 => '兑换', 2 => '充值VIP'];
        return $arry;
    }

    //聊天类型
    public static function talkType()
    {
        $arry = [0 => '文本', 1 => '语音', 2 => '视频'];
        return $arry;
    }

    //聊天状态
    public static function talkStatus()
    {
        $arry = [0 => '发起聊天', 1 => '正在聊天', 2 => '聊天结束', 3 => '已结算'];
        return $arry;
    }

    //充值明细类型
    public static function rechargeType()
    {
        $arry = [0 => '充值金币', 1 => '扣除金币', 2 => '充值VIP'];
        return $arry;
    }

    //充值支付方式
    public static function recPayWay()
    {
        $arry = [1 => 'PDD微信', 2 => 'PDD支付宝', 3 => '三方支付', 4 => '余额', 5 => '手动', 6 => '微信APP支付' ,8 => '聚合支付',9 => '支付宝',10 => '恒云支付1',11 => '恒云支付2',12 => '恒云支付3' ,13 => '恒云支付4',14 => '恒云支付5',15 => '铭科支付',20 => '微信H5支付',21 => '汇潮支付',22 => '恒云微信'];
        return $arry;
    }

    //提现方式
    public static function takeNowWay()
    {
        $arry = [1 => '微信', 2 => '支付宝', 3 => '银行卡'];
        return $arry;
    }

    //支付状态
    public static function payStatus()
    {
        $arry = [0 => '未支付', 1 => '支付成功', 2 => '支付失败'];
        return $arry;
    }

    //审核状态
    public static function checkStatus()
    {
        $arry = [0 => '未审核', 1 => '审核通过', 2 => '审核拒绝',9 => '未审核'];
        return $arry;
    }

    //处理状态
    public static function dealStatus()
    {
        $arry = [0 => '未处理', 1 => '处理中', 2 => '已处理'];
        return $arry;
    }

    //资金明细状态
    public static function recordStatus()
    {
        $arry = [0 => '交易进行中', 1 => '交易成功', 2 => '交易失败'];
        return $arry;
    }

    //资金明细账户状态
    public static function recordAccountType()
    {
        $arry = [0 => '余额', 1 => '金币', 2 => '不可用金币'];
        return $arry;
    }

    //回复状态
    public static function replayStatus()
    {
        $arry = [0 => '未回复', 1 => '已回复'];
        return $arry;
    }

    //积分类型
    public static function scoreType()
    {
        $arry = [0 => '积分', 1 => '富豪', 2 => '魅力'];
        return $arry;
    }

    //等级类型
    public static function levelType()
    {
        $arry = [0 => '积分等级', 1 => '富豪等级', 2 => '魅力等级'];
        return $arry;
    }

    //换衣订单状态
    public static function coatOrderStatus()
    {
        $arry = [0 => '申请中', 1 => '换衣中', 2 => '换衣完成', 3 => '已取消', 4 => '已结束', 5 => '已拒绝'];
        return $arry;
    }

    //商务服务订单状态
    public static function planOrderStatus()
    {
        $arry = [0 => '待处理', 1 => '待接单', 2 => '已接单', 3 => '已拒单', 4 => '服务中', 5 => '待结算', 6 => '已结算', 7 => '已退单', 8 => '已评价', 9 => '已取消'];
        return $arry;
    }

    //商务服务支付订单状态
    public static function planOrderPayStatus()
    {
        $arry = [0 => '待支付', 1 => '已支付', 2 => '未支付', 3 => '待退款', 4 => '已退款'];
        return $arry;
    }

    //账户资金流水类型
    public static function recordType()
    {
        $arry = [1 => '充值', 2 => '送礼物', 3 => '兑换', 4 => '退款', 5 => '冻结资金', 6 => '解冻资金', 7 => '补签', 8 => '后台管理资金', 9 => '收到礼物', 10 => '自拍奖励', 11 => '普通消息消费', 12 => '语音消费', 13 => '视频消费', 14 => '看颜照', 15 => '看视频', 16 => '普通消息收益', 17 => '语音通话收益', 18 => '视频通话收益', 19 => '颜照被查看收益', 20 => '视频被查看收益', 22 => '购买VIP', 23 => '注册赠送币', 24 => '注册赠送邀请人', 25 => '冻结已使用', 26 => '解冻已使用'
            , 27 => '解冻音视频通话币', 28 => '付费换衣服', 29 => '换衣服收益', 30 => '换衣服退款', 31 => '换衣服退还收益', 32 => '打赏', 33 => '收到打赏', 34 => '邀约收益', 35 => '充值赠送', 36 => '下级消费奖励', 37 => '下级商务收益奖励', 38 => '间接下级充值奖励', -1 => '提现',101 => '邀约主播礼物分成',102 => '邀约主播视频语音分成',103 => '直接下级充值奖励'];
        return $arry;
    }


    //积分规则类型
    public static function scoreRuleType()
    {
        $arry = [1 => '点赞', 2 => '被点赞', 3 => '签到', 4 => '补签', 5 => '连续签到'//针对积分用
            , 101 => '充值金币', 102 => '充值VIP', 103 => '看视频', 104 => '看颜照', 105 => '视频', 106 => '语音', 107 => '送礼物', 108 => '付费换衣', 109 => '付费邀约' //针对富豪积分
            , 201 => '被查看视频', 202 => '被查看颜照', 203 => '收到视频', 204 => '收到语音', 205 => '收到礼物', 206 => '换衣收益', 207 => '邀约收益']; //针对魅力积分
        return $arry;
    }

    //消息类型
    public static function messageType()
    {
        $arry = [0 => '系统消息', 1 => '认证通知', 2 => '会员充值通知', 3 => '客户发起订单', 4 => '客户取消订单', 5 => '微导游确认接单', 6 => '微导游取消订单'];
        return $arry;
    }

    //邀请奖励类型
    public static function inviteAwardType()
    {
        $arry = [0 => '下级充值奖励', 1 => '下级消费奖励', 2 => '商务收益奖励'];
        return $arry;
    }

    //短信类型
    public static function smsType()
    {
        $arry = [0 => '验证码短信', 1 => '自定义短信'];
        return $arry;
    }

    //评价类型
    public static function evaluateType()
    {
        $arry = [0 => '普通', 1 => '满意', 2 => '很满意'];
        return $arry;
    }

    //通过积分类型获取对应规则类型
    public static function DescListByType($type = 0)
    {
        $arr = self::scoreRuleType();
        if ($type == 0) {
            $newArr = array_filter($arr, function ($val) {
                return $val < 10;
            }, ARRAY_FILTER_USE_KEY);
            return $newArr;
        } else if ($type == 1) {
            $newArr = array_filter($arr, function ($val) {
                return $val > 100 && $val < 200;
            }, ARRAY_FILTER_USE_KEY);
            return $newArr;
        } else if ($type == 2) {
            $newArr = array_filter($arr, function ($val) {
                return $val > 200 && $val < 300;
            }, ARRAY_FILTER_USE_KEY);
            return $newArr;
        }
        return self::scoreRuleType();
    }

}
