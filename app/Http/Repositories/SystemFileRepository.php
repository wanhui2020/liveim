<?php

namespace App\Http\Repositories;

use App\Models\SystemData;
use App\Models\SystemFile;
use App\Repositories\BaseRepository;

class SystemFileRepository extends BaseRepository
{
    public function model()
    {
        return SystemFile::class;
    }

    public function lists($addWhere = null)
    {
        $where = function ($query) {
            if (request('key')) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . request('key') . '%')
                    ->orWhere('suffix', 'like', '%' . request('key') . '%');

                });
            }
        };
        $this->where($where);
        if ($addWhere) {
            $this->where($addWhere);
        }
        return parent::paginate();
    }
}