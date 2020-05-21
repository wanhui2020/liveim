<?php

namespace App\Repositories;

use App\Models\MemberRecord;

class MemberRecordRepository extends BaseRepository
{
    public function model()
    {
        return MemberRecord::class;
    }

    public function lists($addWhere = null, $with = null)
    {
        $where = function ($query) {
            $query->where('amount','<>',0);
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
            if (request('type') != null) {
                $query->where('type', request('type'));
            }
            if (request('account_type') != null) {
                $query->where('account_type', request('account_type'));
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