<?php

namespace App\Repositories;

use App\Models\MemberPlan;
use App\Models\MemberPlanPic;

//主播商务计划图片
class MemberPlanPicRepository extends BaseRepository
{
    public function model()
    {
        return MemberPlanPic::class;
    }

    public function lists($addWhere = null, $with = null)
    {
        $where = function ($query) {
            if (request('key')) {
                $query->where(function ($query) {
                    $query->where('id', 'like', '%' . request('key') . '%');
                });
                $query->orWhere(function ($query) {
                    $query->whereHas('plan', function ($query) {
                        $query->where('project', 'like', '%' . request('key') . '%');
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