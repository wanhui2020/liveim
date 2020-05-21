<?php

namespace App\Repositories;

use App\Models\MemberFileView;

class MemberFileViewRepository extends BaseRepository
{
    public function model()
    {
        return MemberFileView::class;
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
            if (request('zbkey') != null) {
                $query->where(function ($query) {
                    $query->whereHas('tomember', function ($query) {
                        $query->where('code', 'like', '%' . request('zbkey') . '%')
                            ->orWhere('user_name', 'like', '%' . request('zbkey') . '%')
                            ->orWhere('nick_name', 'like', '%' . request('zbkey') . '%');

                    });
                });
            }
            if (request('type') != null) {
                $query->where('type', request('type'));
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
