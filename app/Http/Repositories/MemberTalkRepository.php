<?php

namespace App\Repositories;

use App\Models\MemberTalk;

//会员聊天管理
class MemberTalkRepository extends BaseRepository
{
    public function model()
    {
        return MemberTalk::class;
    }

    public function lists($addWhere = null, $with = null)
    {
        $where = function ($query) {
            if (request('key')) {
//                $query->where(function ($query) {
//                    $query->where('order_no', 'like', '%' . request('key') . '%');
//                });
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
            if (request('type') != null) {
                $query->where('type', request('type'));
            }
            if (request('bdate') != null && request('edate') != null) {
                if (request('search_data') == 0) {
                    $query->WhereBetween('created_at', [request('bdate') . ' 00:00:00', request('edate') . ' 23:59:59']);
                }
                if (request('search_data') == 1) {
                    $query->WhereBetween('begin_time', [request('bdate') . ' 00:00:00', request('edate') . ' 23:59:59']);
                }
                if (request('search_data') == 2) {
                    $query->WhereBetween('end_time', [request('bdate') . ' 00:00:00', request('edate') . ' 23:59:59']);
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
