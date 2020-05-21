<?php

namespace App\Repositories;

use App\Models\MemberPlanOrder;

class MemberPlanOrderRepository extends BaseRepository
{
    public function model()
    {
        return MemberPlanOrder::class;
    }

    public function lists($addWhere = null, $with = null)
    {
        $where = function ($query) {
            if (request('key')) {
                $query->where(function ($query) {
                    $query->where('order_no', 'like', '%' . request('key') . '%');
                });
                $query->orWhere(function ($query) {
                    $query->whereHas('member', function ($query) {
                        $query->where('code', 'like', '%' . request('key') . '%')
                            ->orWhere('user_name', 'like', '%' . request('key') . '%')
                            ->orWhere('nick_name', 'like', '%' . request('key') . '%');

                    });
                });
            }
            if (request('zbkey') != null) {
                $query->where(function ($query) {
                    $query->whereHas('tomember', function ($query) {
                        $query->where('code', 'like', '%' . request('zbkey') . '%')
                            ->orWhere('user_name', 'like', '%' . request('zbkey') . '%')
                            ->orWhere('nick_name', 'like', '%' . request('zbkey') . '%');

                    });
                });
            }
            if (request('status') != null) {
                $query->where('status', request('status'));
            }
            if (request('pay_status') != null) {
                $query->where('pay_status', request('pay_status'));
            }
            if (request('search_status') != null) {
                if (request('search_status') == 1) {
                    //处理中
                    $query->whereIn('status', [0, 1, 2, 4, 5]);
                }
                if (request('search_status') == 2) {
                    //已完成的
                    $query->whereIn('status', [3, 6, 7]);
                }
            }
            if (request('bdate') != null && request('edate')) {
                if (request('search_date') == 0) {
                    $query->WhereBetween('created_at', [request('bdate') . ' 00:00:00', request('edate') . ' 23:59:59']);
                }
            }
        };
        $this->where($where);
        if ($addWhere) {
            $this->where($addWhere);
        }
        if ($with != null) {
            $this->with($with);
        }
        return parent::paginate();
    }
}
