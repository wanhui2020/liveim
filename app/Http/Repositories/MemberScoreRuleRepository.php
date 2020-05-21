<?php

namespace App\Http\Repositories;

use App\Models\MemberScoreRule;
use App\Repositories\BaseRepository;

//积分规则
class MemberScoreRuleRepository extends BaseRepository
{
    public function model()
    {
        return MemberScoreRule::class;
    }

    public function lists($addWhere = null)
    {
        $where = function ($query) {
            if (request('key')) {
                $query->where(function ($query) {
                    $query->where('id', 'like', '%' . request('key') . '%')
                    ->orWhere('remark', 'like', '%' . request('key') . '%');
                });
            }
            if (request('status') != null) {
                $query->where('status', request('status'));
            }
            if (request('type') != null) {
                $query->where('type', request('type'));
            }
            if (request('desc') != null) {
                $query->where('desc', request('desc'));
            }
        };
        $this->where($where);
        if ($addWhere) {
            $this->where($addWhere);
        }
        return parent::paginate();
    }
}