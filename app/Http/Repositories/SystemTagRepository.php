<?php

namespace App\Http\Repositories;

use App\Models\SystemTag;
use App\Repositories\BaseRepository;

class SystemTagRepository extends BaseRepository
{
    public function model()
    {
        return SystemTag::class;
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
            if (request('is_sys') != null) {
                $query->where('is_sys', request('is_sys'));
            }
        };
        $this->where($where);
        if ($addWhere) {
            $this->where($addWhere);
        }
        return parent::paginate();
    }
}