<?php

namespace App\Repositories;


use App\Models\MemberExtend;

class MemberExtendRepository extends BaseRepository
{
    public function model()
    {
        return MemberExtend::class;
    }

    public function lists($addWhere = null, $with = null)
    {
        $where = function ($query) {
            $query->orWhere(function ($query) {
                $query->whereHas('member', function ($query) {
                    $query->where('code', 'like', '%' . request('key') . '%')
                        ->orWhere('user_name', 'like', '%' . request('key') . '%')
                        ->orWhere('nick_name', 'like', '%' . request('key') . '%');

                });
            });
            if (request('sex')) {
                $query->where(function ($query) {
                    $query->whereHas('member', function ($query) {
                        $query->where('sex', request('sex'));
                    });
                });
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