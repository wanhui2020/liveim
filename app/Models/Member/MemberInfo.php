<?php

namespace App\Models;

use App\Facades\BaseFacade;
use App\Facades\ImFacade;
use App\Facades\MemberFacade;
use App\Facades\PlatformFacade;
use App\Traits\ResultTrait;
use App\Utils\Helper;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MemberInfo extends Authenticatable
{
    use ResultTrait;
    use SoftDeletes, Notifiable;
    protected $table = 'member_info';
//    public $incrementing = false;
//    protected $primaryKey = 'id';
//    protected $guarded = ['password'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'api_token',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'code', 'user_name', 'head_pic', 'new_head_pic', 'nick_name', 'real_name', 'email', 'mobile', 'sex', 'password', 'birth', 'maxim', 'true_name', 'invitation_code', 'openid', 'api_token', 'is_agent', 'agent_id'
        , 'sort', 'status', 'reg_time', 'reg_ip', 'level_id', 'group_id', 'meili', 'haoqi', 'pid', 'selfie_check', 'business_check', 'realname_check', 'online_status', 'vv_busy', 'is_recommend', 'links', 'take_pwd', 'push_token'
        ,'is_inviter','inviter_id','inviter_zbid'
    ];
    protected $appends = ['is_vip'];

    public function getIsVipAttribute()
    {
        if (isset($this->account->vip_expire_date)) {
            if ($this->account->vip_expire_date > date("Y-m-d")) {
                return true;
            }
        }
        return false;
    }

    protected static function boot()
    {
        parent::boot();
        static::retrieved(function ($model) {

        });
        /**
         * 创建开始
         */
        static::creating(function ($model) {
            if (empty($model->api_token)) {
                $model->api_token = Helper::rand_str(64);
            }
            if (empty($model->head_pic)){
                $model->head_pic = url('/images/head.png');
            }
            if (empty($model->token)) {
                $token = ImFacade::userSign($model->id);
                if ($token) {
                    $model->token = $token;
                }
            }
        });
        /**
         * 创建成功后
         */
        static::created(function ($model) {
            //创建一条账户表
            $account = new MemberAccount();
            $account->member_id = $model->id;
            $config = Cache::get('SystemBasic'); //取平台配置
            if ($config) {
                $gold = $config->reg_give_gold;
            } else {
                $gold = SystemBasic::first()->reg_give_gold;
            }
            if ($gold > 0) {
                $account->surplus_gold = $gold;
                //添加一条资金流水
                $record = new MemberRecord();
                $record->member_id = $model->id;
                $record->type = 23;//注册赠送
                $record->account_type = 1; //账户类型
                $record->amount = $gold; //发生金额
                $record->freeze_amount = 0;//冻结金额
                $record->before_amount = 0;//变动前额
                $record->balance = $gold;//实时余额
                $record->status = 1;//交易成功
                $record->remark = '注册赠送金币';//交易备注
                $record->save();
            }

            $account->save();
            //则创建一条扩展表
            $extend = new MemberExtend();
            $extend->member_id = $model->id;
            $extend->save();


        });

        /**
         * 更新成功
         */
        static::updating(function ($model) {
            if ($model->status == 0) {
                $model->api_token = null;
                $model->online_status = 0;
                Log::info('我是用户模型中的离线',[$model->nick_name]);
            } else {
                if (empty($model->api_token)) {
                    $model->api_token = Helper::rand_str(64);
                }
            }
            if ($model->isDirty('nick_name')) {
                if (!BaseFacade::keyword($model->nick_name)) {
                    return false;
                }
            }
        });
        /**
         * 删除成功
         */
        static::deleted(function ($model) {

        });

        /**
         * 创建开始
         */
        static::saving(function ($model) {
            if (empty($model->token)) {
                $token = ImFacade::userSign($model->id);
                if ($token) {
                    $model->token = $token;
                }
            }



            if ($model->isDirty('maxim')) {
                if (!BaseFacade::keyword($model->maxim)) {
                    return false;
                }
            }

        });
        /**
         * 创建开始
         */
        static::saved(function ($model) {
            //更新资料
            $im = ImFacade::userSetInfo($model->id, $model->nick_name, $model->head_pic);
            $token = ImFacade::userSign($model->id);
            if ($token) {
                $model->token = $token;
            }
            if ($model->isDirty('online_status') && $model->online_status == 0) {

                //查询该用户正在通话的订单
                $member_talk = MemberTalk::where('member_id',$model->id)->where('status',1)->first(); //用户退出后台还有聊天订单
                if (isset($member_talk)){
                    MemberFacade::dealTalkOrder($member_talk, 3); //调用处理方法
                }
                $tomember_talk = MemberTalk::where('to_member_id',$model->id)->where('status',1)->first(); //主播退出后台还有聊天订单
                if (isset($tomember_talk)){
                    MemberFacade::dealTalkOrder($tomember_talk, 3); //调用处理方法
                }
            }
        });


    }


    /**
     * 会员账户
     */
    public function account()
    {
        return $this->hasOne(MemberAccount::class, 'member_id', 'id');
    }

    //上级会员
    public function parent()
    {
        return $this->hasOne(get_class($this), $this->getKeyName(), 'pid');
    }

    //邀请人
    public function inviter()
    {
        return $this->hasOne(get_class($this), $this->getKeyName(), 'inviter_id');
    }
    //经纪人
    public function inviterzb()
    {
        return $this->hasOne(get_class($this), $this->getKeyName(), 'inviter_zbid');
    }
    /**
     * 邀请会员
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function childrens()
    {
        return $this->hasMany(MemberInfo::class, 'pid', 'id');
    }

    /**
     * 邀请会员
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inviterchilds()
    {
        return $this->hasMany(MemberInfo::class, 'inviter_id', 'id');
    }
    /**
     * 会员实名
     */
    public function realname()
    {
        return $this->hasOne(MemberRealName::class, 'member_id', 'id');
    }

    /**
     * 主播扩展
     */
    public function extend()
    {
        return $this->hasOne(MemberExtend::class, 'member_id', 'id');
    }

    //会员等级
    public function level()
    {
        return $this->belongsTo(MemberLevel::class, 'level_id', 'id')->withDefault();
    }

    /**
     * 获取会员的所有标签
     */
    public function tags()
    {
        return $this->belongsToMany(SystemTag::class, 'member_tags', 'member_id', 'tag_id');
    }

    public function covers()
    {
        return $this->hasMany(MemberFile::class, 'member_id', 'id')->where(['is_cover' => 1, 'status' => 1]);
    }

    public function lastlogin()
    {
        return $this->hasMany(MemberLogin::class, 'member_id', 'id')->orderBy('updated_at', 'desc');
    }

    public function daycounts()
    {
        return $this->hasMany(MemberDayCount::class, 'member_id', 'id');
    }
}
