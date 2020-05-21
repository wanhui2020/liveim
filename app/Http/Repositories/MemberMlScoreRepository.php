<?php

namespace App\Http\Repositories;

use App\Models\MemberMlscore;
use App\Repositories\BaseRepository;

//魅力积分
class MemberMlScoreRepository extends BaseRepository
{
    public function model()
    {
        return MemberMlscore::class;
    }

    public function lists($addWhere = null, $with = null)
    {
        $where = function ($query) {
            if (request('key')) {
                $query->where(function ($query) {
                    $query->where('id', 'like', '%' . request('key') . '%');
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