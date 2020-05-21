<?php

namespace App\Http\Repositories;

use App\Models\SystemData;
use App\Repositories\BaseRepository;

class SystemDataRepository extends BaseRepository
{
    public function model()
    {
        return SystemData::class;
    }

    public function lists($addWhere = null)
    {
        $where = function ($query) {
            if (request('key')) {
                $query->where(function ($query) {
                    $query->where('key', 'like', '%' . request('key') . '%')
                    ->orWhere('value', 'like', '%' . request('key') . '%');

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