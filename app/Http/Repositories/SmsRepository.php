<?php

namespace App\Http\Repositories;

use App\Models\Sms;
use App\Repositories\BaseRepository;

class SmsRepository extends BaseRepository
{
    public function model()
    {
        return Sms::class;
    }

    public function lists($addWhere = null, $with = null)
    {
        $where = function ($query) {
            if (request('key')) {
                $query->where(function ($query) {
                    $query->where('content', 'like', '%' . request('key') . '%');
                });
            }
            //通过手机号查询
            if (request('phone') != null) {
                $query->where('phone', request('phone'));
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
