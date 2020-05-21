<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WeixinWorkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $type;
    protected $params;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($type, array $params = null)
    {
        $this->type = $type;
        $this->params = $params;
    }

    /**
     * @return void
     */
    public function handle()
    {
        try {

            switch ($this->type) {
                case 'user.sync':
                    $resp = WorkFacade::listUser();
                    if ($resp['errcode'] == 0) {
                        foreach ($resp['userlist'] as $item) {
                            $user = WorkUser::firstOrNew(['mobile' => data_get($item, 'mobile')]);
                            $user->userid = data_get($item, 'userid');
                            $user->name = data_get($item, 'name');
                            $user->department = json_encode(data_get($item, 'department'));
                            $user->order = json_encode(data_get($item, 'order'));
                            $user->position = data_get($item, 'position');
                            $user->gender = data_get($item, 'gender');
                            $user->email = data_get($item, 'email');
                            $user->isleader = data_get($item, 'isleader');
                            $user->avatar = data_get($item, 'avatar');
                            $user->telephone = data_get($item, 'telephone');
                            $user->enable = data_get($item, 'enable');
                            $user->english_name = data_get($item, 'english_name');
                            $user->extattr = json_encode(data_get($item, 'extattr'));
                            $user->status = data_get($item, 'status');
                            $user->qr_code = data_get($item, 'qr_code');
                            $user->external_profile = json_encode(data_get($item, 'external_profile'));

                            $user->save();
                        }
                    }
                    Result::logs('企业微信用户同步',$resp);
                    break;
                case 'message.send':
                    $data = ['touser' => data_get($this->params,'touser'),
                        'toparty' => data_get($this->params,'toparty'),
                        'totag' =>data_get($this->params,'totag'),
                        'msgtype' => 'text',
                        'agentid' => env('WEIXIN_WORK_PLATFORM_AGENT_ID'),
                        'text' => ['content' => data_get($this->params,'content').'['.env('APP_NAME').']',],
                        'safe' => 0];
                    $resp = WorkFacade::send($data);
                    break;
            }
        } catch (Exception $ex) {
            Result::exception($ex);
        }

    }

    /**
     * 要处理的失败任务。
     *
     * @param  Exception $exception
     * @return void
     */
    public function failed(Exception $ex)
    {
        Result::exception($ex);
    }
}
