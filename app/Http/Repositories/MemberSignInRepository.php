<?php

namespace App\Http\Repositories;

use App\Models\MemberSignIn;
use App\Repositories\BaseRepository;

//签到管理
class MemberSignInRepository extends BaseRepository
{
    public function model()
    {
        return MemberSignIn::class;
    }

    public function lists($addWhere = null, $with = null)
    {
        $where = function ($query) {
            if (request('key')) {
                $query->where(function ($query) {
                    $query->where('id', 'like', '%' . request('key') . '%');
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