<?php

namespace App\Repositories;
;
use App\Models\MemberTakeNow;

//会员提现
class MemberTakeNowRepository extends BaseRepository
{
    public function model()
    {
        return MemberTakeNow::class;
    }

    public function lists($addWhere = null, $with = null)
    {
        $where = function ($query) {
            if (request('key')) {
                $query->where(function ($query) {
                    $query->where('id', 'like', '%' . request('key') . '%');
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
