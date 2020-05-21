<?php

namespace App\Repositories;

use App\Models\SystemUser;

class SystemUserRepository extends BaseRepository
{
    public function model()
    {
        return SystemUser::class;
    }

    public function lists($addWhere = null)
    {
        $where = function ($query) {
            $query->where('email', '<>', 'admin@yeah.net');
            if (request('key')) {
                $query->where(function ($query) {
                    $query->where('id', 'like', '%' . request('key') . '%')
                        ->orWhere('name', 'like', '%' . request('key') . '%')
                        ->orWhere('email', 'like', '%' . request('key') . '%')
                        ->orWhere('mobile', 'like', '%' . request('key') . '%');
                });
            }
            if (request('status') != null) {
                $query->where('status', request('status'));
            }
            if (request('name')) {
                $query->where('name', 'like', '%' . request('name') . '%');
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
        $data['password'] = bcrypt($data['password']);
        return parent::store($data);
    }

    public function update($data, $attribute = "id")
    {
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = bcrypt($data['password']);
        }
        return parent::update($data, $attribute);
    }

    public function destroy(array $ids)
    {
        /*if (request()->user('system')->type != 0) {
            return $this->validation('无权操作');
        }*/
        return parent::destroy($ids);
    }
}