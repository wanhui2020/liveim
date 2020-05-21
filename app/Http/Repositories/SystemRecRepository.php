<?php

namespace App\Http\Repositories;

use App\Models\SystemRec;
use App\Repositories\BaseRepository;

/*
 * 平台充值列管理
 * */
class SystemRecRepository extends BaseRepository
{
    public function model()
    {
        return SystemRec::class;
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
            if (request('type') != null) {
                $query->where('type', request('type'));
            }
        };
        $this->where($where);
        if ($addWhere) {
            $this->where($addWhere);
        }
        return parent::paginate();
    }
}