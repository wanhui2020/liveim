<?php

Route::get('/', 'HomeController@index');
Route::get('/download', 'HomeController@download');
Route::any('/test', 'HomeController@test');
Route::any('/share', 'HomeController@share');
Route::any('/testflight', 'HomeController@testflight');
Route::get('/hook', function () {//099测试环境更新
    exec("cd /data/www && ./stock3.sh 2>&1", $output);
    foreach ($output as $v) {
        dump($v);
    }
});

//回调
Route::group(['prefix' => 'callback', 'namespace' => 'Callback'], function () {
    //支付回调
    Route::group(['prefix' => 'pay'], function () {
        Route::any('/alipay', 'PayController@alipay');
        Route::any('/alipayCallback', 'PayController@alipayCallback');
        Route::any('/wechat', 'PayController@wechat');
        Route::any('/typay', 'PayController@typay'); //充值回调
        Route::any('/typay/business', 'PayController@typayBusiness'); //商务邀约支付回调

        Route::any('/pddpay/recharge', 'PayController@pddPayRecharge'); //pdd支付充值回调
        Route::any('/pddpay/business', 'PayController@pddPayBusiness'); //pdd支付商务邀约支付回调

        Route::any('/appwechat/recharge', 'PayController@appWechatRecharge'); //微信APP支付充值回调
        Route::any('/appwechat/business', 'PayController@appWechatBusiness'); //微信APP支付商务邀约支付回调


    });
    Route::group(['prefix' => 'im'], function () {
        Route::any('/', 'IMController@index');
    });
});
Route::any('/wechat', 'WeChatController@serve');

Route::group(['middleware' => ['web', 'wechat.oauth']], function () {
    Route::get('/wechat/user', function () {
        $user = session('wechat.oauth_user.default'); // 拿到授权用户资料

        dd($user);
    });
});

Route::group(['prefix' => 'common', 'namespace' => 'Common'], function () {

    Route::post('/code', 'SmsController@sendCode')->name('sendmsg');
    // 图片上传
    Route::any('/oss/put', 'OssController@putObject');

    // 聚合支付
    Route::any('/juhepay', 'PayController@juhepay');
    Route::any('/juhepayback', 'PayController@juhepayback');
    Route::any('/juhequery', 'PayController@juhequery');
    //阿里云支付及回调
    Route::any('/alipay', 'PayController@alipay');
    Route::any('/alipays', 'PayController@alipays'); //支付宝手机网站支付
    Route::any('/wechath5', 'PayController@wechath5'); //微信H5支付

    Route::any('/alipaycallback', 'PayController@alipaycallback');
    Route::any('/wechatcallback', 'PayController@wechatcallback'); //微信H5支付回调
    //恒云支付回调
    Route::any('/hypayback', 'PayController@hypayback');
    Route::any('/mkpayback', 'PayController@mkpayback'); //铭科支付回调
});


Route::group(['prefix' => 'system', 'namespace' => 'System'], function () {
    Auth::routes();
    Route::get('/test', 'HomeController@test');
    Route::group(['middleware' => ['auth:system']], function () {
        Route::get('/', 'HomeController@index');

        Route::get('/home', 'HomeController@home');
        Route::get('/info', 'HomeController@info');

        //系统设置
        Route::group(['prefix' => 'base', 'namespace' => 'Base'], function () {
            //用户管理
            Route::group(['prefix' => 'user'], function () {
                Route::get('/', 'UserController@index');
                Route::any('/lists', 'UserController@lists');
                Route::get('/create', 'UserController@create');
                Route::post('/store', 'UserController@store');
                Route::get('/edit', 'UserController@edit');
                Route::post('/update', 'UserController@update');
                Route::post('/powerable', 'UserController@powerable');
                Route::post('/destroy', 'UserController@destroy');
                Route::post('/status', 'UserController@status');
            });
            //系统参数
            Route::group(['prefix' => 'config'], function () {
                Route::get('/', 'ConfigController@index');
                Route::get('/edit', 'ConfigController@edit');
                Route::post('/update', 'ConfigController@update');
            });
            // 系统公告
            Route::group(['prefix' => 'notice'], function () {
                Route::get('/', 'NoticeController@index');
                Route::any('/lists', 'NoticeController@lists');
                Route::get('/create', 'NoticeController@create');
                Route::post('/store', 'NoticeController@store');
                Route::post('/powerable', 'NoticeController@powerable');
                Route::get('/edit', 'NoticeController@edit');
                Route::post('/update', 'NoticeController@update');
                Route::post('/destroy', 'NoticeController@destroy');
            });
            // 协议管理
            Route::group(['prefix' => 'agreement'], function () {
                Route::get('/', 'AgreementController@index');
                Route::any('/lists', 'AgreementController@lists');
                Route::get('/create', 'AgreementController@create');
                Route::post('/store', 'AgreementController@store');
                Route::post('/powerable', 'AgreementController@powerable');
                Route::get('/edit', 'AgreementController@edit');
                Route::post('/update', 'AgreementController@update');
                Route::post('/destroy', 'AgreementController@destroy');
            });
        });

        // 会员管理配置
        Route::group(['prefix' => 'member', 'namespace' => 'Member'], function () {

            // 会员等级
            Route::group(['prefix' => 'level'], function () {
                Route::get('/', 'MemberLevelController@index');
                Route::any('/lists', 'MemberLevelController@lists');
                Route::get('/create', 'MemberLevelController@create');
                Route::post('/store', 'MemberLevelController@store');
                Route::post('/status', 'MemberLevelController@status');
                Route::get('/edit', 'MemberLevelController@edit');
                Route::post('/update', 'MemberLevelController@update');
                Route::post('/destroy', 'MemberLevelController@destroy');
            });
            // 会员分组
            Route::group(['prefix' => 'group'], function () {
                Route::get('/', 'MemberGroupController@index');
                Route::any('/lists', 'MemberGroupController@lists');
                Route::get('/create', 'MemberGroupController@create');
                Route::post('/store', 'MemberGroupController@store');
                Route::post('/status', 'MemberGroupController@status');
                Route::get('/edit', 'MemberGroupController@edit');
                Route::post('/update', 'MemberGroupController@update');
                Route::post('/destroy', 'MemberGroupController@destroy');
            });

            //主播会员分类标签
            Route::group(['prefix' => 'tag'], function () {
                Route::get('/', 'MemberTagsController@index');
                Route::any('/lists', 'MemberTagsController@lists');
                Route::get('/edit', 'MemberTagsController@edit');
                Route::post('/update', 'MemberTagsController@update');
                Route::get('/create', 'MemberTagsController@create');
                Route::post('/store', 'MemberTagsController@store');
                Route::post('/destroy', 'MemberTagsController@destroy');
            });

            // 会员信息
            Route::group(['prefix' => 'info'], function () {
                Route::get('/', 'MemberInfoController@index');
                Route::any('/lists', 'MemberInfoController@lists');
                Route::get('/create', 'MemberInfoController@create');
                Route::post('/store', 'MemberInfoController@store');
                Route::post('/status', 'MemberInfoController@status');
                Route::post('/inviter', 'MemberInfoController@inviter'); //是否开通邀请人
                Route::post('/inviterzb', 'MemberInfoController@inviterzb'); //是否开通经纪人
                Route::get('/edit', 'MemberInfoController@edit');
                Route::post('/update', 'MemberInfoController@update');
                Route::post('/destroy', 'MemberInfoController@destroy');
                Route::get('/changegold/{mid}', 'MemberInfoController@changeGold');
                Route::post('/changegold/save', 'MemberInfoController@changeGoldSave');
                Route::get('/setpid/{mid}', 'MemberInfoController@setPid');
                Route::post('/setpid/save', 'MemberInfoController@setPidSave');
                Route::post('/head/audit', 'MemberInfoController@headAudit');
            });

            // 会员扩展信息(主播信息)
            Route::group(['prefix' => 'extend'], function () {
                Route::get('/', 'MemberExtendController@index');
                Route::any('/lists', 'MemberExtendController@lists');
                Route::get('/edit', 'MemberExtendController@edit');
                Route::post('/update', 'MemberExtendController@update');
            });

            // 会员账户信息
            Route::group(['prefix' => 'account'], function () {
                Route::get('/', 'MemberAccountController@index');
                Route::any('/lists', 'MemberAccountController@lists');
                Route::get('/edit', 'MemberAccountController@edit');
                Route::get('/setvip', 'MemberAccountController@setVip');
                Route::post('/update', 'MemberAccountController@update');
            });

            // 会员充值记录
            Route::group(['prefix' => 'recharge'], function () {
                Route::get('/', 'MemberRechargeController@index');
                Route::any('/lists', 'MemberRechargeController@lists');
                Route::post('/status', 'MemberRechargeController@status');
                Route::post('/destroy', 'MemberRechargeController@destroy');
            });
            // 会员资金流水
            Route::group(['prefix' => 'record'], function () {
                Route::get('/', 'MemberRecordController@index');
                Route::any('/lists', 'MemberRecordController@lists');
                Route::post('/destroy', 'MemberRecordController@destroy');
            });

            // 会员主播自拍认证
            Route::group(['prefix' => 'selfie'], function () {
                Route::get('/', 'MemberSelfieController@index');
                Route::any('/lists', 'MemberSelfieController@lists');
                Route::get('/create', 'MemberSelfieController@create');
                Route::post('/store', 'MemberSelfieController@store');
                Route::get('/edit', 'MemberSelfieController@edit');
                Route::post('/update', 'MemberSelfieController@update');
                Route::post('/destroy', 'MemberSelfieController@destroy');
                Route::get('/deal', 'MemberSelfieController@deal');
                Route::post('/deal/save', 'MemberSelfieController@dealSave');
            });

            // 会员商务自拍认证
            Route::group(['prefix' => 'business'], function () {
                Route::get('/', 'MemberBusinessController@index');
                Route::any('/lists', 'MemberBusinessController@lists');
                Route::get('/create', 'MemberBusinessController@create');
                Route::post('/store', 'MemberBusinessController@store');
                Route::get('/edit', 'MemberBusinessController@edit');
                Route::post('/update', 'MemberBusinessController@update');
                Route::post('/destroy', 'MemberBusinessController@destroy');
                Route::get('/deal', 'MemberBusinessController@deal');
                Route::post('/deal/save', 'MemberBusinessController@dealSave');
            });
            // 会员头像审核
            Route::group(['prefix' => 'headpic'], function () {
                Route::get('/', 'MemberHeadpicController@index');
                Route::any('/lists', 'MemberHeadpicController@lists');
                Route::get('/create', 'MemberHeadpicController@create');
                Route::post('/store', 'MemberHeadpicController@store');
                Route::get('/edit', 'MemberHeadpicController@edit');
                Route::post('/update', 'MemberHeadpicController@update');
                Route::post('/destroy', 'MemberHeadpicController@destroy');
                Route::get('/deal', 'MemberHeadpicController@deal');
                Route::post('/deal/save', 'MemberHeadpicController@dealSave');
            });

            // 会员实名认证
            Route::group(['prefix' => 'realname'], function () {
                Route::get('/', 'MemberRealNameController@index');
                Route::any('/lists', 'MemberRealNameController@lists');
                Route::get('/create', 'MemberRealNameController@create');
                Route::post('/store', 'MemberRealNameController@store');
                Route::get('/edit', 'MemberRealNameController@edit');
                Route::post('/update', 'MemberRealNameController@update');
                Route::post('/destroy', 'MemberRealNameController@destroy');
                Route::get('/deal', 'MemberRealNameController@deal');
                Route::post('/deal/save', 'MemberRealNameController@dealSave');
            });

            // 会员积分管理
            Route::group(['prefix' => 'score'], function () {

                // 积分规则
                Route::group(['prefix' => 'rule'], function () {
                    Route::get('/', 'MemberScoreRuleController@index');
                    Route::any('/lists', 'MemberScoreRuleController@lists');
                    Route::get('/create', 'MemberScoreRuleController@create');
                    Route::post('/store', 'MemberScoreRuleController@store');
                    Route::get('/edit', 'MemberScoreRuleController@edit');
                    Route::post('/status', 'MemberScoreRuleController@status');
                    Route::post('/update', 'MemberScoreRuleController@update');
                    Route::post('/destroy', 'MemberScoreRuleController@destroy');
                    Route::get('/get/desc', 'MemberScoreRuleController@getDescList');
                });

                // 普通积分
                Route::group(['prefix' => 'pt'], function () {
                    Route::get('/', 'MemberScoreController@index');
                    Route::any('/lists', 'MemberScoreController@lists');
                    Route::get('/create', 'MemberScoreController@create');
                    Route::post('/store', 'MemberScoreController@store');
                    Route::get('/edit', 'MemberScoreController@edit');
                    Route::post('/status', 'MemberScoreController@status');
                    Route::post('/update', 'MemberScoreController@update');
                    Route::post('/destroy', 'MemberScoreController@destroy');
                });

                // 富豪积分
                Route::group(['prefix' => 'fh'], function () {
                    Route::get('/', 'MemberFhScoreController@index');
                    Route::any('/lists', 'MemberFhScoreController@lists');
                    Route::get('/create', 'MemberFhScoreController@create');
                    Route::post('/store', 'MemberFhScoreController@store');
                    Route::get('/edit', 'MemberFhScoreController@edit');
                    Route::post('/status', 'MemberFhScoreController@status');
                    Route::post('/update', 'MemberFhScoreController@update');
                    Route::post('/destroy', 'MemberFhScoreController@destroy');

                });

                // 魅力积分
                Route::group(['prefix' => 'ml'], function () {
                    Route::get('/', 'MemberMlScoreController@index');
                    Route::any('/lists', 'MemberMlScoreController@lists');
                    Route::get('/create', 'MemberMlScoreController@create');
                    Route::post('/store', 'MemberMlScoreController@store');
                    Route::get('/edit', 'MemberMlScoreController@edit');
                    Route::post('/status', 'MemberMlScoreController@status');
                    Route::post('/update', 'MemberMlScoreController@update');
                    Route::post('/destroy', 'MemberMlScoreController@destroy');

                });
            });

            // 会员意见反馈
            Route::group(['prefix' => 'idea'], function () {
                Route::get('/', 'MemberIdeaController@index');
                Route::any('/lists', 'MemberIdeaController@lists');
                Route::get('/create', 'MemberIdeaController@create');
                Route::post('/store', 'MemberIdeaController@store');
                Route::get('/edit', 'MemberIdeaController@edit');
                Route::post('/update', 'MemberIdeaController@update');
                Route::post('/destroy', 'MemberIdeaController@destroy');
                Route::get('/deal', 'MemberIdeaController@deal');
                Route::post('/deal/save', 'MemberIdeaController@dealSave');
            });

            // 会员举报
            Route::group(['prefix' => 'report'], function () {
                Route::get('/', 'MemberReportController@index');
                Route::any('/lists', 'MemberReportController@lists');
                Route::get('/create', 'MemberReportController@create');
                Route::post('/store', 'MemberReportController@store');
                Route::get('/edit', 'MemberReportController@edit');
                Route::post('/update', 'MemberReportController@update');
                Route::post('/destroy', 'MemberReportController@destroy');
                Route::get('/deal', 'MemberReportController@deal');
                Route::post('/deal/save', 'MemberReportController@dealSave');
            });

            // 签到管理
            Route::group(['prefix' => 'signin'], function () {
                Route::get('/', 'MemberSignInController@index');
                Route::any('/lists', 'MemberSignInController@lists');
                Route::get('/create', 'MemberSignInController@create');
                Route::post('/store', 'MemberSignInController@store');
                Route::get('/edit', 'MemberSignInController@edit');
                Route::post('/status', 'MemberSignInController@status');
                Route::post('/update', 'MemberSignInController@update');
                Route::post('/destroy', 'MemberSignInController@destroy');
            });

            // 会员信誉评价
            Route::group(['prefix' => 'credit'], function () {
                Route::get('/', 'MemberCreditController@index');
                Route::any('/lists', 'MemberCreditController@lists');
                Route::get('/create', 'MemberCreditController@create');
                Route::post('/store', 'MemberCreditController@store');
                Route::get('/edit', 'MemberCreditController@edit');
                Route::post('/update', 'MemberCreditController@update');
                Route::post('/destroy', 'MemberCreditController@destroy');
            });

            // 会员资源库管理表
            Route::group(['prefix' => 'file'], function () {
                Route::get('/', 'MemberFileController@index');
                Route::any('/lists', 'MemberFileController@lists');
                Route::get('/create', 'MemberFileController@create');
                Route::post('/store', 'MemberFileController@store');
                Route::get('/edit', 'MemberFileController@edit');
                Route::post('/update', 'MemberFileController@update');
                Route::post('/destroy', 'MemberFileController@destroy');
                Route::get('/deal', 'MemberFileController@deal');
                Route::post('/deal/save', 'MemberFileController@dealSave');
                Route::get('/doview', 'MemberFileController@view');
                Route::post('/doview/save', 'MemberFileController@viewSave');

                //资源查看记录
                Route::group(['prefix' => 'view'], function () {
                    Route::get('/', 'MemberFileViewController@index');
                    Route::any('/lists', 'MemberFileViewController@lists');
                    Route::post('/destroy', 'MemberFileViewController@destroy');
                });
            });
            // 会员赠送礼物管理
            Route::group(['prefix' => 'gift'], function () {
                Route::get('/', 'MemberGiftController@index');
                Route::any('/lists', 'MemberGiftController@lists');
                Route::get('/create', 'MemberGiftController@create');
                Route::post('/store', 'MemberGiftController@store');
                Route::get('/edit', 'MemberGiftController@edit');
                Route::post('/update', 'MemberGiftController@update');
                Route::post('/destroy', 'MemberGiftController@destroy');
            });
            // 会员打赏管理
            Route::group(['prefix' => 'reward'], function () {
                Route::get('/', 'MemberRewardController@index');
                Route::any('/lists', 'MemberRewardController@lists');
                Route::get('/create', 'MemberRewardController@create');
                Route::post('/store', 'MemberRewardController@store');
                Route::get('/edit', 'MemberRewardController@edit');
                Route::post('/update', 'MemberRewardController@update');
                Route::post('/destroy', 'MemberRewardController@destroy');
            });
            // 会员聊天管理
            Route::group(['prefix' => 'talk'], function () {
                Route::get('/', 'MemberTalkController@index');
                Route::any('/lists', 'MemberTalkController@lists');
                Route::get('/create', 'MemberTalkController@create');
                Route::post('/store', 'MemberTalkController@store');
                Route::get('/edit', 'MemberTalkController@edit');
                Route::post('/update', 'MemberTalkController@update');
                Route::post('/destroy', 'MemberTalkController@destroy');
                Route::post('/deal', 'MemberTalkController@dealSave');
                Route::post('/getcollect', 'MemberTalkController@getcCollect');//获取聊天订单的头部
            });

            // 会员金币兑换
            Route::group(['prefix' => 'exchange'], function () {
                Route::get('/', 'MemberExchangeController@index');
                Route::any('/lists', 'MemberExchangeController@lists');
                Route::get('/create', 'MemberExchangeController@create');
                Route::post('/store', 'MemberExchangeController@store');
                Route::post('/destroy', 'MemberExchangeController@destroy');
            });

            // 会员提现
            Route::group(['prefix' => 'takenow'], function () {
                Route::get('/', 'MemberTakeNowController@index');
                Route::any('/lists', 'MemberTakeNowController@lists');
                Route::get('/create', 'MemberTakeNowController@create');
                Route::post('/store', 'MemberTakeNowController@store');
                Route::get('/edit', 'MemberTakeNowController@edit');
                Route::post('/update', 'MemberTakeNowController@update');
                Route::post('/destroy', 'MemberTakeNowController@destroy');
                Route::get('/deal', 'MemberTakeNowController@deal');
                Route::post('/deal/save', 'MemberTakeNowController@dealSave');
            });

            // 主播会员衣服管理表
            Route::group(['prefix' => 'coat'], function () {
                Route::get('/', 'MemberCoatController@index');
                Route::any('/lists', 'MemberCoatController@lists');
                Route::get('/create', 'MemberCoatController@create');
                Route::post('/store', 'MemberCoatController@store');
                Route::get('/edit', 'MemberCoatController@edit');
                Route::post('/update', 'MemberCoatController@update');
                Route::post('/destroy', 'MemberCoatController@destroy');
                Route::post('/status', 'MemberCoatController@status');
                Route::get('/addorder', 'MemberCoatController@addOrder');
                Route::post('/addorder/save', 'MemberCoatController@addOrderSave');

                //换衣订单记录
                Route::group(['prefix' => 'order'], function () {
                    Route::get('/', 'MemberCoatOrderController@index');
                    Route::any('/lists', 'MemberCoatOrderController@lists');
                    Route::get('/create', 'MemberCoatOrderController@create');
                    Route::post('/store', 'MemberCoatOrderController@store');
                    Route::post('/destroy', 'MemberCoatOrderController@destroy');
                    Route::post('/deal', 'MemberCoatOrderController@dealSave');
                });
            });

            // 主播商务服务计划管理表
            Route::group(['prefix' => 'plan'], function () {
                Route::get('/', 'MemberPlanController@index');
                Route::any('/lists', 'MemberPlanController@lists');
                Route::get('/create', 'MemberPlanController@create');
                Route::post('/store', 'MemberPlanController@store');
                Route::get('/edit', 'MemberPlanController@edit');
                Route::post('/update', 'MemberPlanController@update');
                Route::post('/destroy', 'MemberPlanController@destroy');
                Route::post('/audit', 'MemberPlanController@audit');

                //商务服务订单记录
                Route::group(['prefix' => 'order'], function () {
                    Route::get('/', 'MemberPlanOrderController@index');
                    Route::any('/lists', 'MemberPlanOrderController@lists');
                    Route::get('/create', 'MemberPlanOrderController@create');
                    Route::post('/store', 'MemberPlanOrderController@store');
                    Route::post('/destroy', 'MemberPlanOrderController@destroy');
                    Route::post('/deal', 'MemberPlanOrderController@dealSave');
                    Route::get('/get/projects', 'MemberPlanOrderController@getProjectList');
                });

                //商务服务计划图片
                Route::group(['prefix' => 'pic'], function () {
                    Route::get('/', 'MemberPlanPicController@index');
                    Route::any('/lists', 'MemberPlanPicController@lists');
                    Route::get('/create', 'MemberPlanPicController@create');
                    Route::post('/store', 'MemberPlanPicController@store');
                    Route::post('/destroy', 'MemberPlanPicController@destroy');
                });
            });
        });

        // 平台参数配置
        Route::group(['prefix' => 'platform', 'namespace' => 'Platform'], function () {

            // 平台参数
            Route::group(['prefix' => 'config'], function () {
                Route::get('/', 'ConfigController@edit');
                Route::post('/update', 'ConfigController@update');
            });
            // 平台基础数据
            Route::group(['prefix' => 'data'], function () {
                Route::get('/', 'SystemDataController@index');
                Route::any('/lists', 'SystemDataController@lists');
                Route::get('/create', 'SystemDataController@create');
                Route::post('/store', 'SystemDataController@store');
                Route::post('/status', 'SystemDataController@status');
                Route::get('/edit', 'SystemDataController@edit');
                Route::post('/update', 'SystemDataController@update');
                Route::post('/destroy', 'SystemDataController@destroy');
            });
            // 平台文件数据
            Route::group(['prefix' => 'file'], function () {
                Route::get('/', 'SystemFileController@index');
                Route::any('/lists', 'SystemFileController@lists');
                Route::get('/create', 'SystemFileController@create');
                Route::post('/store', 'SystemFileController@store');
                Route::post('/status', 'SystemFileController@status');
                Route::get('/edit', 'SystemFileController@edit');
                Route::post('/update', 'SystemFileController@update');
                Route::post('/destroy', 'SystemFileController@destroy');
            });

            // banner图管理
            Route::group(['prefix' => 'banner'], function () {
                Route::get('/', 'SystemBannerController@index');
                Route::any('/lists', 'SystemBannerController@lists');
                Route::get('/create', 'SystemBannerController@create');
                Route::post('/store', 'SystemBannerController@store');
                Route::post('/status', 'SystemBannerController@status');
                Route::get('/edit', 'SystemBannerController@edit');
                Route::post('/update', 'SystemBannerController@update');
                Route::post('/destroy', 'SystemBannerController@destroy');
            });

            // 系统消息管理
            Route::group(['prefix' => 'message'], function () {
                Route::get('/', 'SystemMessageController@index');
                Route::any('/lists', 'SystemMessageController@lists');
                Route::get('/create', 'SystemMessageController@create');
                Route::post('/store', 'SystemMessageController@store');
                Route::get('/edit', 'SystemMessageController@edit');
                Route::post('/update', 'SystemMessageController@update');
                Route::post('/destroy', 'SystemMessageController@destroy');
            });

            // 系统短信管理
            Route::group(['prefix' => 'sms'], function () {
                Route::get('/', 'SmsController@index');
                Route::any('/lists', 'SmsController@lists');
                Route::get('/create', 'SmsController@create');
                Route::post('/store', 'SmsController@store');
                Route::post('/destroy', 'SmsController@destroy');
            });

            // 平台会员标签
            Route::group(['prefix' => 'tag'], function () {
                Route::get('/', 'SystemTagController@index');
                Route::any('/lists', 'SystemTagController@lists');
                Route::get('/create', 'SystemTagController@create');
                Route::post('/store', 'SystemTagController@store');
                Route::post('/status', 'SystemTagController@status');
                Route::get('/edit', 'SystemTagController@edit');
                Route::post('/update', 'SystemTagController@update');
                Route::post('/destroy', 'SystemTagController@destroy');
            });
            // 平台礼物管理
            Route::group(['prefix' => 'gift'], function () {
                Route::get('/', 'SystemGiftController@index');
                Route::any('/lists', 'SystemGiftController@lists');
                Route::get('/create', 'SystemGiftController@create');
                Route::post('/store', 'SystemGiftController@store');
                Route::post('/status', 'SystemGiftController@status');
                Route::get('/edit', 'SystemGiftController@edit');
                Route::post('/update', 'SystemGiftController@update');
                Route::post('/destroy', 'SystemGiftController@destroy');
            });
            // 平台充值项管理
            Route::group(['prefix' => 'rec'], function () {
                Route::get('/', 'SystemRecController@index');
                Route::any('/lists', 'SystemRecController@lists');
                Route::get('/create', 'SystemRecController@create');
                Route::post('/store', 'SystemRecController@store');
                Route::post('/status', 'SystemRecController@status');
                Route::get('/edit', 'SystemRecController@edit');
                Route::post('/update', 'SystemRecController@update');
                Route::post('/destroy', 'SystemRecController@destroy');
            });

            //统计管理
            Route::group(['prefix' => 'statistics'], function () {
                Route::get('/day/count', 'StatisticsController@dayCount');
                Route::get('/daycount', 'StatisticsController@getDayCount');
            });


        });
    });
});

/**
 * 代理商
 */
Route::group(['prefix' => 'agent', 'namespace' => 'Agent'], function () {
    Auth::routes();
    Route::group(['middleware' => ['auth:agent']], function () {
        Route::get('/', 'HomeController@index');
        Route::get('/home', 'HomeController@home');
        Route::any('/info', 'HomeController@info');
        Route::any('/uppwd', 'HomeController@editPwd');
        Route::any('/uppwd/save', 'HomeController@updatePwd');

        /**
         * 系统基础
         */
        Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
            Route::get('/logout', 'LoginController@logout');
        });

        //代理商
        Route::group(['prefix' => 'sub'], function () {
            Route::get('/', 'MemberInfoController@subindex');
            Route::any('/lists', 'MemberInfoController@sublists');
            Route::any('/income', 'MemberInfoController@income');
            Route::any('/incomes', 'MemberInfoController@incomeLists');
        });

    });
});

