<?php

namespace App\Repositories;

use App\Models\MemberPlanOrderContent;

class MemberPlanOrderContentRepository extends BaseRepository
{
    public function model()
    {
        return MemberPlanOrderContent::class;
    }

    public function lists($addWhere = null, $with = null)
    {
        $where = function ($query) {
            if (request('key')) {
                $query->where(function ($query) {
                    $query->where('id', 'like', '%' . request('key') . '%');
                });
                $query->orWhere(function ($query) {
                    $query->whereHas('order', function ($query) {
                        $query->where('order_no', 'like', '%' . request('key') . '%');
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