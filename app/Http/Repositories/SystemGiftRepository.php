<?php

namespace App\Http\Repositories;

use App\Models\SystemGift;
use App\Models\SystemTag;
use App\Repositories\BaseRepository;

/*
 * 系统礼物管理
 * */
class SystemGiftRepository extends BaseRepository
{
    public function model()
    {
        return SystemGift::class;
    }

    public function lists($addWhere = null)
    {
        $where = function ($query) {
            if (request('key')) {
                $query->where(function ($query) {
                    $query->where('title', 'like', '%' . request('key') . '%');
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