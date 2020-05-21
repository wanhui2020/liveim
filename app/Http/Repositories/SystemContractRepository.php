<?php

namespace App\Http\Repositories;

use App\Models\PlatformContract;
use App\Repositories\BaseRepository;

class SystemContractRepository extends BaseRepository
{
    public function model()
    {
        return PlatformContract::class;
    }

    public function lists($addWhere = null)
    {

        $where = function ($query) {
            if (request('key')) {
                $query->where(function ($query) {
                    $query->where('no', 'like', '%' . request('key') . '%'); //策略产品编号
                });
            }

            if (request('start_time')!=null){           //开始时间
                $query->where('created_at','>',request('start_time'));
            }

            if (request('end_time')!=null){             //结束时间
                $query->where('created_at','<',request('end_time').'23:59:59');
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