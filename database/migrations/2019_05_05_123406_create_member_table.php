<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberTable extends Migration
{
    /**
     * 会员相关数据表
     *
     * @return void
     */
    public function up()
    {
        //会员等级表
        Schema::create('member_level', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('type')->default(0)->comment('规则类型（0积分 1富豪 2魅力）');
            $table->integer('lvl')->default(0)->comment('会员级别');
            $table->string('name', 50)->comment('会员级别名称');
            $table->integer('min_score')->default(0)->comment('范围下限(最低)');
            $table->integer('max_score')->default(0)->comment('范围上限(最高)');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态0 禁用 1正常');
            $table->string('remark', 255)->nullable()->comment('备注');
            $table->softDeletes();
            $table->timestamps();
        });

        /*
         * 会员积分规则表
         * 用于判判断会员获得积分数量
         * */
        Schema::create('member_score_rule', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('type')->default(0)->comment('规则类型（0积分 1富豪 2魅力）');
            $table->integer('desc')->default(101)->comment('描述');
            $table->integer('score')->default(0)->comment('获得积分');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态0 禁用 1正常');
            $table->string('remark', 255)->nullable()->comment('备注');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员分组表
        Schema::create('member_group', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50)->comment('会员级别名称');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态0 禁用 1正常');
            $table->string('remark', 255)->nullable()->comment('备注');
            $table->integer('sort')->default(0)->comment('排序');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员基础信息表
        Schema::create('member_info', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('code')->default(1)->comment('会员编号');
            $table->string('user_name', 50)->comment('用户名');
            $table->string('password', 200)->comment('登录密码');
            $table->string('api_token')->unique()->nullable()->comment('用户Token');
            $table->char('pid')->nullable()->comment('上级ID');

            $table->tinyInteger('is_inviter')->default(0)->comment('是否开通邀请人 0 未开通 默认未开通 1开通');
            $table->tinyInteger('is_inviter_zb')->default(0)->comment('是否开通邀请主播 0 未开通 默认未开通 1开通');
            $table->char('inviter_id')->default(0)->comment('邀请人id');
            $table->char('inviter_zbid')->default(0)->comment('经纪人id');

            $table->string('nick_name', 50)->nullable()->comment('昵称');
            $table->string('head_pic', 200)->nullable()->comment('头像图片');
            $table->string('new_head_pic', 200)->nullable()->comment('待审核头像图片');
            $table->string('openid', 50)->nullable()->comment('微信OPENID');
            $table->string('token', 200)->unique()->nullable()->comment('三方imtoken');
            $table->string('push_token')->nullable()->comment('推送token');
            $table->string('take_pwd', 200)->nullable()->comment('提现密码');
            $table->char('level_id')->nullable()->comment('所属等级ID');
            $table->char('group_id')->nullable()->comment('所属分组ID');
            $table->integer('meili')->default(0)->comment('魅力值');
            $table->integer('haoqi')->default(0)->comment('豪气值');
            $table->string('true_name', 50)->nullable()->comment('真实姓名');
            $table->string('mobile', 11)->nullable()->comment('手机号码');
            $table->string('email', 50)->nullable()->comment('电子邮件');
            $table->tinyInteger('sex')->default(-1)->comment('姓别 0男 1女');
            $table->string('birth', 20)->nullable()->comment('生日');
            $table->string('maxim', 50)->nullable()->comment('格言');
            $table->dateTime('reg_time')->nullable()->comment('注册时间');
            $table->string('reg_ip', 20)->nullable()->comment('注册IP');
            $table->string('invitation_code', 20)->nullable()->comment('邀请码(注册时自动生成)');
            $table->tinyInteger('selfie_check')->default(0)->comment('自拍认证(0否 1是)');
            $table->tinyInteger('realname_check')->default(0)->comment('实名认证(0否 1是)');
            $table->string('realname_id')->default(0)->comment('实名认证生成唯一ID值');
            $table->tinyInteger('business_check')->default(0)->comment('商务认证(0否 1是)');
            $table->tinyInteger('online_status')->default(0)->comment('在线状态:0离线 1在线');
            $table->tinyInteger('vv_busy')->default(0)->comment('语音视频是否忙碌(0空闲 1忙碌)');
            $table->tinyInteger('is_recommend')->default(0)->comment('是否推荐（0否 1是）');
            $table->tinyInteger('is_agent')->default(0)->comment('是否代理商（0否 1是）');
            $table->bigInteger('agent_id')->default(0)->comment('所属代理商ID(0无代理商)');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态0 禁用 1正常');
            $table->integer('sort')->default(0)->comment('排序');
            $table->string('platform', 20)->nullable()->comment('会员使用客户端');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        //会员信息扩展表
        Schema::create('member_extend', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('所属会员');
            $table->string('signature', 200)->nullable()->comment('主播签名');
            $table->string('city', 200)->nullable()->comment('所在城市');
            $table->string('address', 200)->nullable()->comment('联系地址');
            $table->string('hobbies', 200)->nullable()->comment('兴趣爱好');
            $table->integer('height')->default(0)->comment('身高(cm)');
            $table->decimal('weight', 18, 2)->default(0)->comment('体重(KG)');
            $table->string('constellation', 20)->nullable()->comment('星座');
            $table->tinyInteger('is_business')->default(0)->comment('是否开启商务（0否 1是）');
            $table->integer('talk_rate')->default(0)->comment('聊天平台分成占比（%）');
            $table->integer('gift_rate')->default(0)->comment('礼物平台分成占比（%）');
            $table->integer('other_rate')->default(0)->comment('其他消费平台分成占比（%）');
            $table->integer('invit_reg_rate')->default(0)->comment('邀请注册充值奖励比（%）');
            $table->integer('text_fee')->default(0)->comment('普通消息收费');
            $table->integer('voice_fee')->default(0)->comment('语音消息收费');
            $table->integer('video_fee')->default(0)->comment('视频消息收费');
            $table->integer('picture_view_fee')->default(0)->comment('颜照库收费');
            $table->integer('video_view_fee')->default(0)->comment('视频库收费');
            $table->integer('coat_fee')->default(0)->comment('换衣收费');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员账户表
        Schema::create('member_account', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('所属会员');
            $table->integer('surplus_gold')->default(0)->comment('剩余金币');
            $table->integer('notuse_gold')->default(0)->comment('不可用金币(冻结)');
            $table->integer('lock_gold')->default(0)->comment('前端用户充值的币不可以进行兑换提现');
            $table->integer('cantx_gold')->default(0)->comment('会员可提现金币');
            $table->decimal('surplus_rmb', 18, 2)->default(0)->comment('余额');
            $table->decimal('notuse_rmb', 18, 2)->default(0)->comment('不可用余额');
            $table->integer('total_consume')->default(0)->comment('累计消费金币');
            $table->integer('total_income')->default(0)->comment('累计收益金币');
            $table->integer('sys_plus')->default(0)->comment('后台添加金币');
            $table->integer('sys_minus')->default(0)->comment('后台扣除金币');
            $table->integer('score')->default(0)->comment('积分');
            $table->integer('ml_score')->default(0)->comment('魅力积分');
            $table->integer('fh_score')->default(0)->comment('富豪积分');
            $table->integer('sign_days')->default(0)->comment('连续签到天数');
            $table->integer('bq_count')->default(0)->comment('补签次数');
            $table->integer('visit_count')->default(0)->comment('被访问次数');
            $table->integer('lx_login_days')->default(0)->comment('连续登录天数');
            $table->integer('lx_login_max_days')->default(0)->comment('最大连续登录天数');
            $table->integer('text_charge')->default(0)->comment('普通消息累计收费');
            $table->integer('voice_charge')->default(0)->comment('语音消息累计收费');
            $table->integer('video_charge')->default(0)->comment('视频消息累计收费');
            $table->integer('picture_view_charge')->default(0)->comment('颜照库累计收费');
            $table->integer('video_view_charge')->default(0)->comment('视频库累计收费');
            $table->tinyInteger('vip_level')->default(0)->comment('vip等级');
            $table->string('vip_expire_date', 20)->nullable()->comment('vip到期日期');
            $table->integer('gift_count')->default(0)->comment('收到礼物数量');
            $table->integer('xy_score')->default(0)->comment('信誉评分');
            $table->integer('myd_score')->default(0)->comment('满意度评分');
            $table->integer('yz_score')->default(0)->comment('颜值评分');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员标签表
        Schema::create('member_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('所属会员');
            $table->char('tag_id')->comment('标签ID');
            $table->string('tag_name', 50)->nullable()->comment('标签名称');
            $table->integer('sort')->default(0)->comment('排序');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员关注/被关注表
        Schema::create('member_attention', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('关注会员ID');
            $table->char('to_member_id')->comment('被关注会员ID');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员好友表
        Schema::create('member_friends', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('会员ID');
            $table->char('to_member_id')->comment('好友会员ID');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态0 待审核 1审核通过 2审核拒绝');
            $table->dateTime('deal_time')->nullable()->comment('通过时间');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员登录日志表
        Schema::create('member_login', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('会员ID');
            $table->dateTime('login_time')->comment('登录时间');
            $table->string('login_ip', 20)->nullable()->comment('登录IP');
            $table->string('remark', 200)->nullable()->comment('登录备注(可记录登录设备)');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员排行榜
        Schema::create('member_top', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('会员ID');
            $table->tinyInteger('type')->default(0)->comment('0魅力 1富豪');
//            $table->tinyInteger('date_type')->default(0)->comment('时间类型(0实时榜 1日榜 2周榜 3月榜)');
            $table->integer('count')->default(0)->comment('数量');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员意见反馈表
        Schema::create('member_idea', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('会员ID');
            $table->string('content', 500)->comment('意见内容');
            $table->string('replay', 500)->nullable()->comment('回复内容');
            $table->string('replay_user', 50)->nullable()->comment('回复人');
            $table->dateTime('replay_time')->nullable()->comment('回复时间');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态(0未回复  1已回复)');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员自拍认证表
        Schema::create('member_selfie', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('会员ID');
            $table->char('pic')->comment('自拍照资源ID');
            $table->char('conver')->nullable()->comment('默认封面照源ID');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态(0未审核  1审核通过 2审核拒绝)');
            $table->string('deal_user', 50)->nullable()->comment('审核人');
            $table->dateTime('deal_time')->nullable()->comment('审核时间');
            $table->string('deal_reason', 255)->nullable()->comment('审核意见');
            $table->softDeletes();
            $table->timestamps();
        });

        //主播会员商务认证表
        Schema::create('member_business', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('会员ID');
            $table->char('pic')->comment('自拍照资源ID');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态(0未审核  1审核通过 2审核拒绝)');
            $table->string('deal_user', 50)->nullable()->comment('审核人');
            $table->dateTime('deal_time')->nullable()->comment('审核时间');
            $table->string('deal_reason', 255)->nullable()->comment('审核意见');
            $table->softDeletes();
            $table->timestamps();
        });


        //会员实名认证表
        Schema::create('member_realname', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('会员ID');
            $table->string('cert_no', 30)->nullable()->comment('身份证号');
            $table->string('name', 50)->nullable()->comment('真实姓名');
            $table->string('cert_zm')->nullable()->comment('身份证正面照');
            $table->string('cert_fm')->nullable()->comment('身份证反面照');
            $table->string('cert_sc')->nullable()->comment('手持身份证');
            $table->string('selfie_pic')->nullable()->comment('自拍照');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态(0未审核  1审核通过 2审核拒绝,9已审核待后台确认)');
            $table->string('deal_user', 50)->nullable()->comment('审核人');
            $table->dateTime('deal_time')->nullable()->comment('审核时间');
            $table->string('deal_reason', 255)->nullable()->comment('审核意见');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员资源库表
        Schema::create('member_file', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('会员ID');
            $table->tinyInteger('type')->default(0)->comment('类型（0视频库 1颜照库）');
//            $table->char('file_id')->comment('资源ID');
            $table->string('url', 255)->nullable()->comment('资源路径');
            $table->tinyInteger('is_cover')->default(0)->comment('是否设为封面(0否 1是）');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态(0未审核  1审核通过 2审核拒绝)');
            $table->string('deal_user', 50)->nullable()->comment('审核人');
            $table->dateTime('deal_time')->nullable()->comment('审核时间');
            $table->string('deal_reason', 255)->nullable()->comment('审核意见');
            $table->integer('sort')->default(0)->comment('排序');
            $table->softDeletes();
            $table->timestamps();
        });

        //主播会员资源库查看记录表
        Schema::create('member_file_view', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('查看会员');
            $table->char('to_member_id')->comment('被查看主播');
            $table->char('member_file_id')->comment('主播资源ID');
            $table->integer('gold')->default(0)->comment('花费金币');
            $table->tinyInteger('type')->default(0)->comment('查看资源类型(0视频 1颜照)');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员举报
        Schema::create('member_report', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('会员ID');
            $table->char('to_member_id')->comment('举报对象会员ID');
            $table->string('type', 50)->nullable()->comment('举报类型');
            $table->string('explain', 200)->nullable()->comment('举报内容说明');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态(0未处理  1处理中 2已处理)');
            $table->string('deal_user', 50)->nullable()->comment('处理人');
            $table->dateTime('deal_time')->nullable()->comment('处理时间');
            $table->string('deal_reason', 255)->nullable()->comment('处理意见');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员签到记录
        Schema::create('member_signin', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('会员ID');
            $table->integer('award')->default(0)->comment('奖励金币');
            $table->integer('lx_days')->default(0)->comment('连续签到天数');
            $table->dateTime('qd_date')->comment('签到日期');
            $table->dateTime('bq_date')->nullable()->comment('补签日期');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员聊天记录
        Schema::create('member_talk', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('发起会员ID');
            $table->char('to_member_id')->comment('接收会员ID');
            $table->string('channel_code', 50)->nullable()->comment('通道编号');
            $table->tinyInteger('type')->default(0)->comment('聊天类型（0文本 1语音 2视频）');
            $table->integer('price')->default(0)->comment('用户消费金币(单价)');
            $table->integer('amount')->default(0)->comment('总消费金币');
            $table->integer('profit')->default(0)->comment('收益(单价*收益占比)');
            $table->integer('total_profit')->default(0)->comment('总收益(总消费*收益占比)');
            $table->integer('times')->default(0)->comment('时长(秒)');
            $table->dateTime('begin_time')->nullable()->comment('开始时间');
            $table->dateTime('end_time')->nullable()->comment('结束时间');
            $table->tinyInteger('status')->default(0)->comment('状态（0发起聊天 1正在聊天 2聊天结束 3已结算）');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员资金流水
        Schema::create('member_record', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('会员ID');
            $table->char('talk_id')->nullable()->comment('聊天订单ID');
            $table->string('order_no')->nullable()->comment('订单号');
            $table->integer('type')->default(1)->comment('资金类型（1充值 2送礼物 3兑换 4退款 5冻结资金 6解冻资金 7补签 8后台管理资金 9收到礼物 10自拍奖励 11普通消息消费 12语音消费 13视频消费 14看颜照 15看视频 16普通消息收益 17语音通话收益 18视频通话收益 19颜照被查看收益 20视频被查看收益 21邀请人员充值奖励、22购买VIP 23注册赠送币 24注册赠送邀请人 25冻结已使用 26解冻已使用 27解冻音视频通话币 28付费换衣服 29换衣服收益 30换衣服退款 31换衣服退还收益 32打赏 33收到打赏 34邀约收益 -1提现）');
            $table->tinyInteger('account_type')->default(0)->comment('账户类型(0余额 1金币 2不可提现金币)');
            $table->decimal('amount', 18, 2)->default(0)->comment('发生金额');
            $table->decimal('freeze_amount', 18, 2)->default(0)->comment('冻结金额');
            $table->decimal('before_amount', 18, 2)->default(0)->comment('操作前金额');
            $table->decimal('balance', 18, 2)->default(0)->comment('实时余额');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态(0交易进行中 1交易成功 2交易失败)');
            $table->string('remark', 50)->nullable()->comment('交易备注');
            $table->string('admin_remark', 50)->nullable()->comment('后台备注');
            $table->softDeletes();
            $table->timestamps();
        });


        //会员信誉评价
        Schema::create('member_credit', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('会员ID');
            $table->char('to_member_id')->comment('评分对象会员ID');
            $table->integer('xy_score')->default(0)->comment('信誉评分');
            $table->integer('yz_score')->default(0)->comment('颜值评分');
            $table->integer('myd_score')->default(0)->comment('满意度评分');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员访问记录
        Schema::create('member_visit', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('访问会员ID');
            $table->char('to_member_id')->comment('被访会员ID');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员积分明细记录
        Schema::create('member_score', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('会员ID');
            $table->integer('type')->default(1)->comment('类型');
            $table->integer('score')->default(0)->comment('积分数量');
            $table->string('remark', 200)->nullable()->comment('备注说明');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员富豪积分明细记录
        Schema::create('member_fh_score', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('会员ID');
            $table->integer('type')->default(0)->comment('类型(0送礼物 1私聊 2语音 3视频 4看颜照 5看视频 6充值vip 7充值金币)');
            $table->integer('score')->default(0)->comment('积分数量');
            $table->string('remark', 200)->nullable()->comment('备注说明');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员(主播)魅力积分明细记录
        Schema::create('member_ml_score', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('会员ID');
            $table->integer('type')->default(0)->comment('类型(0语音 1视频 2礼物 3看颜照 4看视频)');
            $table->integer('score')->default(0)->comment('积分数量');
            $table->string('remark', 200)->nullable()->comment('备注说明');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员充值记录
        Schema::create('member_recharge', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('会员ID');
            $table->string('order_no')->comment('充值单号');
            $table->tinyInteger('type')->default(0)->comment('充值类型(0充值金币 1扣除金币 2充值VIP)');
            $table->tinyInteger('way')->default(0)->comment('支付方式(1微信、2支付宝、3其他、4余额、5手动 6 微信原生支付 8聚合支付 9支付宝原生支付 10 恒云支付1 11 恒云支付2 12 恒云支付3)');
            $table->decimal('amount', 18, 2)->default(0)->comment('支付金额');
            $table->integer('quantity')->default(0)->comment('对应金币/天数');
            $table->integer('give')->default(0)->comment('普通会员赠送金币/天数');
            $table->integer('vip_give')->default(0)->comment('vip会员赠送金币/天数');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态(0未支付 1支付成功 2支付失败)');
            $table->string('remark', 200)->nullable()->comment('备注说明');
            $table->tinyInteger('is_sys')->default(0)->comment('是否系统操作(0否 1是)');
            $table->string('operator', 50)->nullable()->comment('操作人员');
            $table->dateTime('pay_time')->nullable()->comment('支付时间');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员提现记录
        Schema::create('member_takenow', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('会员ID');
            $table->string('order_no')->comment('提款单号');
            $table->decimal('amount', 18, 2)->default(0)->comment('提现金额');
            $table->decimal('fee_money', 18, 2)->default(0)->comment('手续费');
            $table->decimal('real_amount', 18, 2)->default(0)->comment('实际到账金额');
            $table->tinyInteger('way')->default(0)->comment('提现方式(1支付宝，2微信)');
            $table->string('account_no', 50)->nullable()->comment('提现账号');
            $table->string('account_name', 50)->nullable()->comment('账号名称');
            $table->string('bank', 50)->nullable()->comment('所属银行');
            $table->string('desc', 200)->nullable()->comment('简介描述');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态(0未审核 1审核通过 2审核拒绝)');
            $table->string('deal_user', 50)->nullable()->comment('审核人员');
            $table->dateTime('deal_time')->nullable()->comment('审核时间');
            $table->string('deal_reason', 50)->nullable()->comment('审批意见');
            $table->string('remark', 200)->nullable()->comment('备注说明');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员(主播)发/收礼物记录
        Schema::create('member_gift', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('赠送会员ID');
            $table->char('to_member_id')->comment('接收会员ID');
            $table->char('gift_id')->comment('所属礼物');
            $table->char('gift_name')->comment('礼物标题');
            $table->integer('quantity')->default(1)->comment('数量');
            $table->integer('gold')->default(1)->comment('总金币');
            $table->tinyInteger('is_sys')->default(0)->comment('是否后台赠送(0否 1是)');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员打赏记录
        Schema::create('member_reward', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('打赏会员ID');
            $table->char('to_member_id')->comment('打赏对象会员ID');
            $table->integer('gold')->default(1)->comment('打赏金币数量');
            $table->string('remark', 200)->nullable()->comment('备注说明');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员金币兑换记录
        Schema::create('member_exchange', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('会员ID');
            $table->integer('gold')->default(0)->comment('金币数量');
            $table->decimal('rmb', 18, 2)->default(0)->comment('兑换人民币');
            $table->string('remark', 200)->nullable()->comment('备注说明');
            $table->softDeletes();
            $table->timestamps();
        });

        //主播衣服库表
        Schema::create('member_coat', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('主播会员ID');
            $table->string('title', 50)->comment('衣服标题');
            $table->string('url', 255)->nullable()->comment('衣服路径');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态0 禁用 1正常');
            $table->integer('sort')->default(0)->comment('排序');
            $table->softDeletes();
            $table->timestamps();
        });

        //主播换衣订单表
        Schema::create('member_coat_order', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('查看会员');
            $table->char('to_member_id')->comment('被查看主播');
            $table->char('member_coat_id')->comment('主播衣服ID');
            $table->integer('gold')->default(0)->comment('花费金币');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态(0申请中 1换衣中 2换衣完成 3已取消 4已结束 5已拒绝)');
            $table->softDeletes();
            $table->timestamps();
        });

        //主播商务服务计划安排
        Schema::create('member_plan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('主播会员ID');
            $table->string('project', 50)->comment('服务项');
            $table->text('content')->nullable()->comment('服务内容');
            $table->integer('sort')->default(0)->comment('排序');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态(0禁用 1正常)');
            $table->softDeletes();
            $table->timestamps();
        });

        //主播商务服务计划图片
        Schema::create('member_plan_pic', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('plan_id')->comment('计划项ID');
            $table->string('pic', 2000)->comment('图片地址');
            $table->softDeletes();
            $table->timestamps();
        });

        //主播商务服务订单
        Schema::create('member_plan_order', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('发起会员');
            $table->char('to_member_id')->comment('邀约主播');
            $table->char('to_data_id')->comment('退款id');
            $table->string('order_no')->comment('订单号');
            $table->string('pay_url')->nullable()->comment('支付url');
            $table->integer('way')->default(1)->comment('支付方式(1.微信 2.支付宝 3.三方支付');
            $table->string('service_date', 50)->comment('服务日期');
            $table->string('project', 255)->comment('服务项，多项逗号隔开');
            $table->decimal('amount', 18, 2)->default(0)->comment('服务费用');
            $table->decimal('profit', 18, 2)->default(0)->comment('主播收益');
            $table->string('remark', 200)->nullable()->comment('备注说明');
            $table->string('refund_type', 200)->nullable()->comment('退款类型 全额退款 部分退款');
            $table->integer('score')->default(0)->comment('服务评分');
            $table->string('evaluation', 200)->nullable()->comment('服务评价');
            $table->tinyInteger('cancel_type')->default(0)->comment('取消状态:0未取消 1会员取消 2主播取消 3平台取消');
            $table->unsignedTinyInteger('pay_status')->default(0)->comment('支付状态(0待支付 1已支付 2未支付 3待退款 4已退款)');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态(0待处理 1待接单 2已接单 3已拒绝 4服务中 5待结算 6已结算 7已退单 9已取消)');
            $table->softDeletes();
            $table->timestamps();
        });

        //主播商务服务订单行程安排
        Schema::create('member_planorder_content', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('order_id')->comment('商务订单ID');
            $table->string('project', 50)->comment('服务项');
            $table->text('content')->nullable()->comment('服务内容');
            $table->integer('sort')->default(0)->comment('排序');
            $table->unsignedTinyInteger('status')->default(9)->comment('审核状态');
            $table->softDeletes();
            $table->timestamps();
        });

        //主播商务服务订单行程安排图片
        Schema::create('member_planorder_content_pic', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('plan_id')->comment('订单服务计划项ID');
            $table->string('pic', 2000)->comment('图片地址');
            $table->softDeletes();
            $table->timestamps();
        });

        //会员日统计
        Schema::create('member_day_count', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('所属会员');
            $table->integer('dayint')->default(0)->comment('统计日期');
            $table->integer('rec_gold')->default(0)->comment('充值金币');
            $table->integer('award_gold')->default(0)->comment('奖励金币');
            $table->integer('profit_gold')->default(0)->comment('收益金币(主播)');
            $table->integer('consume_gold')->default(0)->comment('消费金币(会员)');
            $table->decimal('rec_money', 18, 2)->default(0)->comment('充值金额');
            $table->decimal('take_money', 18, 2)->default(0)->comment('提现金额');
            $table->decimal('profit_money', 18, 2)->default(0)->comment('收益金额');
            $table->softDeletes();
            $table->timestamps();
        });

        //邀请奖励明细记录
        Schema::create('member_invite_award', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('member_id')->comment('所属会员ID');
            $table->char('from_member_id')->comment('贡献会员ID');
            $table->integer('type')->default(0)->comment('贡献类型(0充值奖励 1消费奖励 2商务收益奖励)');
            $table->integer('gold')->default(0)->comment('贡献金币');
            $table->decimal('money', 18, 2)->default(0)->comment('贡献金额');
            $table->softDeletes();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_level');
        Schema::dropIfExists('member_score_rule');
        Schema::dropIfExists('member_group');
        Schema::dropIfExists('member_info');
        Schema::dropIfExists('member_extend');
        Schema::dropIfExists('member_account');
        Schema::dropIfExists('member_attention');
        Schema::dropIfExists('member_tags');
        Schema::dropIfExists('member_friends');
        Schema::dropIfExists('member_top');
        Schema::dropIfExists('member_idea');
        Schema::dropIfExists('member_selfie');
        Schema::dropIfExists('member_file');
        Schema::dropIfExists('member_file_view');
        Schema::dropIfExists('member_report');
        Schema::dropIfExists('member_signin');
        Schema::dropIfExists('member_visit');
        Schema::dropIfExists('member_score');
        Schema::dropIfExists('member_fh_score');
        Schema::dropIfExists('member_ml_score');
        Schema::dropIfExists('member_recharge');
        Schema::dropIfExists('member_takenow');
        Schema::dropIfExists('member_gift');
        Schema::dropIfExists('member_reward');

    }
}
