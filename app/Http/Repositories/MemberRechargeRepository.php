<?php

namespace App\Repositories;


use App\Models\MemberRecharge;

class MemberRechargeRepository extends BaseRepository
{
    public function model()
    {
        return MemberRecharge::class;
    }

    public function lists($addWhere = null, $with = null)
    {
        $where = function ($query) {
            if (request('key')) {
                $query->where(function ($query) {
                    $query->where('id', 'like', '%' . request('key') . '%')
                        ->orWhere('order_no', 'like', '%' . request('key') . '%');
                });
                $query->orWhere(function ($query) {
                    $query->whereHas('member', function ($query) {
                        $query->where('code', 'like', '%' . request('key') . '%')
                            ->orWhere('user_name', 'like', '%' . request('key') . '%')
                            ->orWhere('nick_name', 'like', '%' . request('key') . '%');

                    });
                });
            }
            if (request('status') != null) {
                $query->where('status', request('status'));
            }
            if (request('way') != null) {
                $query->where('way', request('way'));
            }
            if (request('type') != null) {
                $query->where('type', request('type'));
            }
            if (request('bdate') != null && request('edate')) {
                if (request('search_date') == 0) {
                    $query->WhereBetween('created_at', [request('bdate') . ' 00:00:00', request('edate') . ' 23:59:59']);
                }
            }
//            if (request('bdate') != null && request('edate')) {
//                if (request('search_date') == 0) {
//                    $query->WhereBetween('pay_time', [request('bdate') . ' 00:00:00', request('edate') . ' 23:59:59']);
//                } else {
//                    $query->WhereBetween('created_at', [request('bdate') . ' 00:00:00', request('edate') . ' 23:59:59']);
//                }
//            }
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
