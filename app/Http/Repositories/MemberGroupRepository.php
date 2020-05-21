<?php

namespace App\Http\Repositories;

use App\Models\MemberGroup;
use App\Models\MemberLevel;
use App\Repositories\BaseRepository;

class MemberGroupRepository extends BaseRepository
{
    public function model()
    {
        return MemberGroup::class;
    }

    public function lists($addWhere = null)
    {
        $where = function ($query) {
            if (request('key')) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . request('key') . '%');
                });
            }
            if (request('status') != null) {
                $query->where('status', request('status'));
            }
        };
        $this->where($where);
        if ($addWhere) {
            $this->where($addWhere);
        }
        return parent::paginate();
    }
}