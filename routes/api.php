<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['namespace' => 'Api', 'middleware' => 'verifyApiSign'], function () {

    // Route::group(['prefix' => 'v1'], function () {

    //公共接口（不需要auth验证）
    Route::group(['prefix' => 'common', 'namespace' => 'Common'], function () {
        Route::any('/test', 'CommonController@test');
        Route::post('/code', 'CommonController@sendCode'); // 验证码发送

        //系统接口
        Route::group(['prefix' => 'system', 'namespace' => 'System'], function () {
            //系统配置
            Route::any('/config', 'ConfigController@config');
            //获取分享33短链接
            Route::any('/shareurl', 'ConfigController@getShare3url');
        });

        // 平台管理
        Route::group(['prefix' => 'platform', 'namespace' => 'Platform'], function () {
            Route::any('/data', 'DataController@getListByType'); //通过类型获取数据
            Route::post('/getpay', 'DataController@getpay'); //获取支付方式
            Route::any('/tags', 'DataController@getTags'); //获取分类标签数据
            //banner图片
            Route::any('/banner', 'BannerController@getList');
            //平台礼物
            Route::any('/gift', 'GiftController@getList');
            //充值项获取
            Route::any('/rec', 'RecController@getListByType');
            //查询积分获取规则
            Route::any('/score/rule', 'ScoreController@getListByType');
        });

    });
    // 用户登录注册
    Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
        Route::post('/login', 'AuthController@login'); // 登录
        Route::post('/register', 'AuthController@register'); // 注册
        Route::post('/forget', 'AuthController@forget'); // 忘记密码
    });

    // 会员管理
    Route::group(['prefix' => 'member', 'namespace' => 'Member'], function () {
        Route::post('/login', 'MemberInfoController@login'); // 会员登录
        Route::any('/account', 'MemberAccountController@account'); // 会员账户
        Route::any('/info', 'MemberInfoController@info'); // 会员详情
        Route::any('/info/uid', 'MemberInfoController@getInfoById'); // 通过ID查询会员详情
        Route::post('/record/list', 'MemberRecordController@lists'); // 资金流水记录
        Route::post('/edit', 'MemberInfoController@editInfo'); // 编辑会员资料    开启商务
        Route::post('/invite', 'MemberInfoController@inviteLists'); // 会员邀请用户列表
        Route::any('/share', 'MemberInfoController@shareInfo'); // 会员分享页
        Route::any('/top', 'MemberInfoController@topLists'); // 排行榜
        Route::post('/sms/sendtophone', 'MemberInfoController@sendSmsByPhone'); //发送短信到号码
        Route::post('/sms/send', 'MemberInfoController@sendSms'); //发送短信到已绑定的手机号上
        Route::post('/info/bindmobile', 'MemberInfoController@bindMobile'); //绑定手机号码
        Route::post('/info/uptkpwd', 'MemberInfoController@updateTakeNowPwd'); //修改提现密码

        //会员消息列表
        Route::group(['prefix' => 'message'], function () {
            Route::any('/list', 'MemberInfoController@myMessageList'); //会员消息列表
        });

        //会员认证管理
        Route::group(['prefix' => 'check'], function () {
            Route::post('/selfie', 'MemberInfoController@selfieCheck'); // 自拍认证
            Route::post('/selfie/info', 'MemberInfoController@selfieInfo'); // 自拍认证详情
            Route::post('/business', 'MemberInfoController@businessCheck'); // 商务认证
            Route::post('/business/info', 'MemberInfoController@businessInfo'); // 商务认证详情
            Route::post('/realname', 'MemberInfoController@realNameCheck'); // 实名认证
            Route::post('/realname/info', 'MemberInfoController@realNameInfo'); // 实名认证详情

        });
        //活体认证管理
        Route::group(['prefix' => 'describe'], function () {
            Route::post('/token', 'MemberDescribeController@DescribeVerifyToken'); // 实名认证
            Route::post('/result', 'MemberDescribeController@DescribeVerifyResult'); // 实名认证详情

        });

        //主播会员管理
        Route::group(['prefix' => 'zb'], function () {
            Route::any('/list', 'MemberInfoController@zblist'); // 主播列表
            Route::any('/info', 'MemberInfoController@zbinfo'); // 主播详情
            Route::any('/manlist', 'MemberInfoController@manList'); // 男神列表
        });

        //主播会员资源管理
        Route::group(['prefix' => 'file'], function () {
            Route::any('/list', 'MemberFileController@lists'); // 主播资源列表
            Route::any('/mylist', 'MemberFileController@myLists'); // 我的颜照库
            Route::post('/view', 'MemberFileController@viewFile'); // 查看资源
            Route::post('/add', 'MemberFileController@addFile'); //添加资源
            Route::post('/delete', 'MemberFileController@deleteFile'); //删除资源
            Route::post('/setcover', 'MemberFileController@setCoverFile'); //设置/取消封面
        });

        //主播会员资源管理
        Route::group(['prefix' => 'gift'], function () {
            Route::any('/list', 'MemberGiftController@lists'); // 主播礼物列表
            Route::any('/mylist', 'MemberGiftController@myLists'); // 我的礼物列表
            Route::post('/give', 'MemberGiftController@giveGift'); // 赠送礼物
            Route::post('/reward', 'MemberGiftController@reward'); // 打赏
            Route::post('/test', 'MemberGiftController@test'); //测试
        });

        //会员充值管理
        Route::group(['prefix' => 'recharge'], function () {
            Route::post('/gold', 'MemberRechargeController@rechargeGold'); //充值金币
            Route::post('/vip', 'MemberRechargeController@rechargeVIP'); //充值VIP
            Route::post('/topay', 'MemberRechargeController@rechargePay'); //充值跳转支付
        });

        //会员提现管理
        Route::group(['prefix' => 'takenow'], function () {
            Route::post('/apply', 'MemberTakeNowController@apply'); //余额提现申请
            Route::post('/gtr', 'MemberTakeNowController@goldExchange'); //金币兑换
        });

        //会员意见反馈
        Route::group(['prefix' => 'idea'], function () {
            Route::post('/add', 'MemberIdeaController@addIdea'); //会员意见反馈
        });

        //会员举报
        Route::group(['prefix' => 'report'], function () {
            Route::post('/add', 'MemberReportController@addReport'); //会员举报
        });

        //会员签到
        Route::group(['prefix' => 'signin'], function () {
            Route::post('/today', 'MemberSigninController@today'); //会员签到今日
            Route::post('/before', 'MemberSigninController@buqian'); //会员补签
        });

        //会员关注
        Route::group(['prefix' => 'attention'], function () {
            Route::post('/add', 'MemberAttentionController@add'); //会员关注/取消关注
            Route::post('/list', 'MemberAttentionController@lists'); //会员关注列表
        });

        //好友管理
        Route::group(['prefix' => 'friend'], function () {
            Route::post('/add', 'MemberFriendsController@add'); //添加好友
            Route::post('/applylist', 'MemberFriendsController@applyList'); //好友申请列表
            Route::post('/mylist', 'MemberFriendsController@myList'); //我的好友列表
            Route::post('/delete', 'MemberFriendsController@delete'); //删除好友
            Route::post('/deal', 'MemberFriendsController@applyDo'); //好友申请验证
        });

        //会员换衣管理
        Route::group(['prefix' => 'coat'], function () {
            Route::post('/list', 'MemberCoatController@lists'); //主播衣服列表（会员查看）
            Route::post('/mylist', 'MemberCoatController@myLists'); //我的衣服
            Route::post('/add', 'MemberCoatController@addCoat'); //添加衣服
            Route::post('/delete', 'MemberCoatController@deleteCoat'); //删除衣服
            //换衣订单管理
            Route::group(['prefix' => 'order'], function () {
                Route::post('/list', 'MemberCoatController@myOrderList'); //我的换衣订单
                Route::post('/info', 'MemberCoatController@coatOrderInfo'); //订单详情
                Route::post('/add', 'MemberCoatController@addOrder'); //申请换衣订单
                Route::post('/deal', 'MemberCoatController@dealCoatOrder'); //操作换衣订单
            });
        });

        //会员聊天管理
        Route::group(['prefix' => 'talk'], function () {
            Route::post('/add', 'MemberTalkController@addTalkOrder'); //发起聊天
            Route::post('/deal', 'MemberTalkController@dealTalkOrder'); //订单处理
            Route::post('/check', 'MemberTalkController@checkTalkOrder'); //订单检查
            Route::post('/search', 'MemberTalkController@getTalkInfoById'); //订单查询
            Route::post('/test', 'MemberTalkController@testIm'); //订单处理
            Route::post('/query', 'MemberTalkController@query'); //查询订单状态
        });

        //商务邀约管理
        Route::group(['prefix' => 'plan'], function () {
            Route::any('/swlist', 'MemberPlanController@swList'); //商务列表
            Route::any('/swinfo', 'MemberPlanController@swInfo'); //商务详情
            Route::post('/add', 'MemberPlanController@addPlan'); //主播发起商务服务计划
            Route::post('/delete', 'MemberPlanController@deletePlan'); //主播删除服务计划
            Route::post('/mylist', 'MemberPlanController@myPlanList'); //主播查询商务服务计划
            Route::group(['prefix' => 'order'], function () {
                Route::post('/add', 'MemberPlanController@addPlanOrder'); //申请邀约订单
                Route::post('/list', 'MemberPlanController@myOrderList'); //我的邀约订单
                Route::post('/info', 'MemberPlanController@planOrderInfo'); //订单详情
                Route::post('/deal', 'MemberPlanController@dealPlanOrder'); //操作商务订单
                Route::post('/evaluate', 'MemberPlanController@evaluatePlanOrder'); //商务订单评价
                Route::post('/topay', 'MemberPlanController@toPay'); //订单支付
                Route::post('/refund', 'MemberPlanController@refund'); //商务订单退款
            });
            Route::group(['prefix' => 'pic'], function () {
                Route::post('/add', 'MemberPlanController@addPlanPic'); //
                Route::post('/delete', 'MemberPlanController@deletePlanPic'); //
            });
        });

    });

    //});

});

