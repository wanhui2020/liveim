<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
         * 系统用户表
         * */
        Schema::create('system_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('姓名');
            $table->string('email')->unique()->comment('邮箱');
            $table->string('phone')->nullable()->comment('电话');
            $table->string('password')->comment('密码');
            $table->unsignedTinyInteger('type')->default(0)->comment('角色,0系统管理员1运营主管2业务经理3产品运维4财务结算5风控人员');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态0 禁用 1正常');
            $table->unsignedTinyInteger('sort')->default(0)->comment('排序');
            $table->text('remark')->nullable()->comment('备注');
            $table->unsignedInteger('versions')->default(0)->comment('版本号');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        /*
         * 系统角色
         * */
        Schema::create('system_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->unsignedTinyInteger('status')->default(1)->comment('状态');
            $table->unsignedTinyInteger('sort')->default(0)->comment('排序');
            $table->text('remark')->nullable()->comment('备注');
            $table->softDeletes();
            $table->timestamps();
        });

        /*
         *  系统权限表
         * */
        Schema::create('system_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->unsignedTinyInteger('status')->default(1)->comment('状态');
            $table->unsignedTinyInteger('sort')->default(0)->comment('排序');
            $table->text('remark')->nullable()->comment('备注');
            $table->unsignedInteger('versions')->default(0)->comment('版本号');
            $table->softDeletes();
            $table->timestamps();
        });

        /*
         * 系统参数设置表
         * */
        Schema::create('system_config', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50)->nullable()->comment('站点名称');
            $table->string('ba_no', 50)->nullable()->comment('备案号');
            $table->string('version', 20)->nullable()->comment('版本号');
            $table->string('ios_version', 20)->nullable()->comment('ios版本号');
            $table->string('android_down')->nullable()->comment('安卓下载链接');
            $table->string('ios_down')->nullable()->comment('苹果下载链接');
            $table->string('app_pic', 200)->nullable()->comment('APP图标');
            $table->string('cp_name', 50)->nullable()->comment('公司名称');
            $table->string('footer', 50)->nullable()->comment('站点底部');
            $table->string('domain')->nullable()->comment('平台域名');
            $table->string('tel')->nullable()->comment('客服热线');
            $table->string('weixin')->nullable()->comment('客服微信');
            $table->string('address')->nullable()->comment('地址');
            $table->text('fwxy')->nullable()->comment('服务协议');
            $table->string('reg_welcome', 200)->nullable()->comment('首次注册欢迎语');
            $table->text('vip_explain')->nullable()->comment('VIP说明');
            $table->text('about_us')->nullable()->comment('关于我们');
            $table->text('warm_prompt')->nullable()->comment('商务温馨提示');
            $table->text('vip_privilege')->nullable()->comment('VIP特权说明');
            $table->text('app_explain')->nullable()->comment('微游说明');
            $table->text('takenow_explain')->nullable()->comment('提现说明');
            $table->text('keyword')->nullable()->comment('过滤关键字');
            $table->softDeletes();
            $table->timestamps();
        });

        /*
         * 系统平台基础数据表
         * */
        Schema::create('system_basic', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('yebz_remind')->default(120)->comment('余额不足提前提示秒数');
            $table->integer('rate')->default(40)->comment('平台费率');
            $table->integer('gift_rate')->default(0)->comment('赠送礼物分成');
            $table->integer('bk_kf')->default(0)->comment('补签扣费');
            $table->integer('dh_min')->default(0)->comment('最低兑换金币数量');
            $table->integer('dh_rate')->default(0)->comment('金币兑换比例(%)');
            $table->integer('tx_min')->default(100)->comment('提现最低限额');
            $table->integer('tx_rate')->default(1)->comment('提现手续费率(%)');
            $table->integer('tx_nofee_count')->default(3)->comment('提现单日免手续费笔数');
            $table->integer('tx_max_count')->default(0)->comment('单日最高提现次数');
            $table->integer('tx_max_amount')->default(0)->comment('单日最高提现金额');
            $table->integer('ds_rate')->default(0)->comment('金币打赏分成比例(%)');
            $table->integer('pj_dstj')->default(0)->comment('信誉评价打赏金币条件');
            $table->integer('reg_give_gold')->default(0)->comment('注册赠送金币');
            $table->integer('yqzc_give_gold')->default(0)->comment('邀请用注册奖励金币');
            $table->integer('invite_rate')->default(0)->comment('邀请用户充值奖励(%)');
            $table->integer('invite_gift_rate')->default(0)->comment('下级视频礼物奖励(%)');
            $table->integer('consume_rate')->default(0)->comment('下级用户消费奖励(%)');
            $table->integer('yield_rate')->default(0)->comment('下级商务收益奖励(%)');
            $table->integer('selfie_check_award')->default(0)->comment('自拍认证奖励金币');
            $table->integer('hy_rate')->default(0)->comment('换衣收益分成(%)');
            $table->integer('free_text')->default(3)->comment('会员文本消息免费数');
            $table->integer('business_sin_fee')->default(0)->comment('商务单项收费');
            $table->integer('business_mul_fee')->default(0)->comment('商务多项收费');
            $table->integer('business_rate')->default(0)->comment('商务平台分成比例(%)');
            $table->softDeletes();
            $table->timestamps();
        });

        //系统数据表
        Schema::create('system_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer("type")->default(1)->comment("数据类型(1.消息收费 2.语音收费 3.视频收费 4.颜照库收费 5.视频库收费 6.换衣收费 7.举报理由 10支付方式");
            $table->integer('key')->default(0)->comment('对应键 6原生微信支付 8聚合支付 9支付宝原生支付');
            $table->string('value', 255)->nullable()->comment('对应值');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态0 禁用 1正常');
            $table->string('remark', 255)->nullable()->comment('备注');
            $table->softDeletes();
            $table->timestamps();
        });

        //系统文件表
        Schema::create('system_file', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->nullable()->comment('名称');
            $table->string('url', 500)->nullable()->comment('原图路径');
            $table->string('small_url', 500)->nullable()->comment('缩略图路径');
            $table->string('suffix', 20)->nullable()->comment('文件后缀类型');
            $table->tinyInteger('place')->default(0)->comment('存储引擎(0本地 1阿里云 2七牛云)');
            $table->integer('size')->default(0)->comment('文件大小(KB)');
            $table->string('mime', 50)->nullable()->comment('文件mime类型');
            $table->softDeletes();
            $table->timestamps();
        });

        //平台banner图库
        Schema::create('system_banner', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('图名称');
            $table->string('url', 2000)->comment('图片地址');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态0 禁用 1正常');
            $table->integer('sort')->default(0)->comment('排序');
            $table->softDeletes();
            $table->timestamps();
        });

        //平台标签设置表
        Schema::create('system_tag', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50)->comment('标签名称');
            $table->tinyInteger('is_sys')->default(0)->comment('是否系统默认0否 1是');
            $table->string('create_user', 50)->nullable()->comment('添加人');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态0 禁用 1正常');
            $table->integer('sort')->default(0)->comment('排序');
            $table->softDeletes();
            $table->timestamps();
        });

        //礼物管理
        Schema::create('system_gift', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 50)->comment('礼物标题');
            $table->integer('gold')->default(0)->comment('价格金币');
            $table->string('url', 500)->nullable()->comment('效果地址');
            $table->integer('sort')->default(0)->comment('排序');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态0 禁用 1正常');
            $table->string('remark', 255)->nullable()->comment('礼物备注说明');
            $table->softDeletes();
            $table->timestamps();
        });

        //充值列管理
        Schema::create('system_rec', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50)->comment('名称');
            $table->tinyInteger('type')->default(0)->comment('类型(0充值 1兑换 2充值VIP)');
            $table->decimal('old_cost', 18, 2)->default(0)->comment('原价');
            $table->decimal('cost', 18, 2)->default(0)->comment('现价');
            $table->integer('quantity')->default(0)->comment('对应金币/天数');
            $table->integer('give')->default(0)->comment('普通会员赠送金币/天数');
            $table->integer('vip_give')->default(0)->comment('VIP会员赠送金币/天数');
            $table->string('remark', 255)->nullable()->comment('备注说明');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态0 禁用 1正常');
            $table->integer('sort')->default(0)->comment('排序');
            $table->softDeletes();
            $table->timestamps();
        });

        //系统消息表
        Schema::create('system_message', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 50)->nullable()->comment('消息标题');
            $table->text('content')->nullable()->comment('消息内容');
            $table->bigInteger('to_id')->default('0')->comment('接收人ID，默认0所有');
            $table->integer('type')->default(0)->comment('类型(0系统消息 1认证通知 2会员充值通知)');
            $table->bigInteger('member_id')->default(0)->comment('相关会员ID');
            $table->string('push_token')->nullable()->comment('客户端Token');
            $table->tinyInteger('is_push')->default(0)->comment('是否推送：0否 1是');
            $table->tinyInteger('is_read')->default(0)->comment('是否已读：0未读 1已读');
            $table->dateTime('read_time')->nullable()->comment('阅读时间');
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
        Schema::dropIfExists('system_config');
        Schema::dropIfExists('system_basic');
        Schema::dropIfExists('system_users');
        Schema::dropIfExists('system_roles');
        Schema::dropIfExists('system_system_roles');
        Schema::dropIfExists('system_permissions');
        Schema::dropIfExists('system_file');
        Schema::dropIfExists('system_data');
        Schema::dropIfExists('system_banner');
        Schema::dropIfExists('system_tag');
        Schema::dropIfExists('system_rec');

    }
}
