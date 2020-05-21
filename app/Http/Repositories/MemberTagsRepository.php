<?php

namespace App\Repositories;

use App\Models\MemberFileView;
use App\Models\MemberTags;

/*
 * 会员分类标签管理
 * */

class MemberTagsRepository extends BaseRepository
{
    public function model()
    {
        return MemberTags::class;
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
            if (request('tag')) {
                $query->where(function ($query) {
                    $query->whereHas('tag', function ($query) {
                        $query->where('name', 'like', '%' . request('tag') . '%');
                    });
                });
            }
            if (request('tag_id')) {
                $query->where('tag_id', request('tag_id'))->orderBy('sort', 'desc');
            }
            $query->orderBy('member_info.online_status', 'desc');
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
