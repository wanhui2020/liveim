<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 主播商务服务订单记录
 * */

class MemberPlanOrder extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_plan_order';
    protected $guarded = [];
    protected $fillable = ['member_id', 'to_member_id', 'to_data_id', 'order_no', 'way', 'service_date', 'project', 'amount', 'profit', 'remark', 'score', 'evaluation', 'pay_status', 'status'];

    protected static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function ($model) {

        });
    }

    /**
     * 关联退款id
     */
    public function data()
    {
        return $this->belongsTo(SystemData::class, 'to_data_id', 'id')->withDefault();
    }
    //查看会员
    public function member()
    {
        return $this->belongsTo(MemberInfo::class, 'member_id', 'id')->withDefault();
    }

    //主播会员
    public function tomember()
    {
        return $this->belongsTo(MemberInfo::class, 'to_member_id', 'id')->withDefault();
    }

    //项目
    public function projects()
    {
        return $this->hasMany(MemberPlanOrderContent::class, 'order_id', 'id')->orderBy('sort', 'asc')->get(['project', 'content']);
    }

    protected $appends = ['status_cn', 'pay_status_cn'];

    public function getStatusCnAttribute()
    {
        if (isset($this->status)) {
            return SelectList::planOrderStatus()[$this->status];
        }
        return '';
    }

    //支付订单状态（中文）
    public function getPayStatusCnAttribute()
    {
        if (isset($this->pay_status)) {
            return SelectList::planOrderPayStatus()[$this->pay_status];
        }
        return '';
    }
}
