<?php

namespace App\Http\Repositories;

use App\Repositories\BaseRepository;
use App\Models\SystemNotice;

class SystemNoticeRepository extends BaseRepository
{
    public function model()
    {
        return SystemNotice::class;
    }

    public function lists($addWhere = null)
    {
        $where = function ($query) {
            if (request('key')) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . request('key') . '%');
                });
            }
            if(request('start_time')!=null&&request('end_time')!=null)
            {
                $query->whereBetween('created_at',[request('start_time'), request('end_time')])->get();
            }
            if (request('status')!=null) {
                $query->where('status', request('status'));
            }
        };
        $this->where($where);
        if ($addWhere) {
            $this->where($addWhere);
        }
        return parent::paginate();
    }

    public function store(array $data)
    {
        return parent::store($data);
    }

    public function update($data, $attribute = "id")
    {
        return parent::update($data, $attribute);
    }

    public function destroy(array $ids)
    {
        return parent::destroy($ids);
    }
}