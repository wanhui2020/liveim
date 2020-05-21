<?php

namespace App\Http\Repositories;

use App\Models\SystemMessage;
use App\Repositories\BaseRepository;

class SystemMessageRepository extends BaseRepository
{
    public function model()
    {
        return SystemMessage::class;
    }

    public function lists($addWhere = null, $with = null)
    {
        $where = function ($query) {
            if (request('key')) {
                $query->where(function ($query) {
                    $query->where('content', 'like', '%' . request('key') . '%');
                    $query->orWhere('title', 'like', '%' . request('key') . '%');
                });
            }
            if (request('type') != null) {
                $query->where('type', request('type'));
            }
            if (request('toid') != null) {
                $query->where(function ($query) {
                    $query->where('to_id', request('toid'))->orWhere('to_id', '0');
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
