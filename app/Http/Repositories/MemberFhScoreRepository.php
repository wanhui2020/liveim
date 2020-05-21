<?php

namespace App\Http\Repositories;

use App\Models\MemberFhscore;
use App\Repositories\BaseRepository;

//富豪积分
class MemberFhScoreRepository extends BaseRepository
{
    public function model()
    {
        return MemberFhscore::class;
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