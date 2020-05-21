<?php

namespace App\Repositories;


use App\Models\MemberInfo;

class MemberInfoRepository extends BaseRepository
{
    public function model()
    {
        return MemberInfo::class;
    }

    public function lists($addWhere = null, $with = null,$is_app = 0,$head = 0)
    {
        $where = function ($query) use ($is_app,$head){
            if ($head == 1){
                $query->where('new_head_pic','<>','');
            }
            if (request('type') == 'recommend'){
                $query->where('is_recommend',1);
            }
            if($is_app == 1){
                $query->where('online_status',1);
            }
            if (request('key')) {
                $query->where(function ($query) {
                    $query->where('id', 'like', '%' . request('key') . '%')
                        ->orWhere('code', 'like', '%' . request('key') . '%')
                        ->orWhere('nick_name', 'like', '%' . request('key') . '%')
                        ->orWhere('email', 'like', '%' . request('key') . '%')
                        ->orWhere('mobile', 'like', '%' . request('key') . '%');
                });
            }
            if (request('parent')) {
                $query->where(function ($query) {
                    $query->where('code', 'like', '%' . request('parent') . '%')
                        ->orWhere('nick_name', 'like', '%' . request('parent') . '%')
                        ->orWhere('mobile', 'like', '%' . request('parent') . '%');
                });
                $query->orWhere(function ($query) {
                    $query->whereHas('parent', function ($query) {
                        $query->where('code', 'like', '%' . request('parent') . '%')
                            ->orWhere('nick_name', 'like', '%' . request('parent') . '%');

                    });
                });

            }
            if (request('sex') != null) {
                $query->where('sex', request('sex'));
            }
            if (request('status') != null) {
                $query->where('status', request('status'));
            }
            if (request('is_inviter') != null) {
                $query->where('is_inviter', request('is_inviter'));
            }
            if (request('online_status') != null) {
                $query->where('online_status', request('online_status'));
            }
            if (request('selfie_check') != null) {
                $query->where('selfie_check', request('selfie_check'));
            }
            if (request('realname_check') != null) {
                $query->where('realname_check', request('realname_check'));
            }
            if (request('business_check') != null) {
                $query->where('business_check', request('business_check'));
            }
            if (request('is_agent') != null) {
                $query->where('is_agent', request('is_agent'));
            }
            if (request('vv_busy') != null) {
                $query->where('vv_busy', request('vv_busy'));
            }
            if (request('real_name') != null) {
                $query->where('real_name', 'like', '%' . request('name') . '%');
            }
            if (request('tag_id') != null) {
                $query->where(function ($query) {
                    $query->whereHas('tags', function ($query) {
                        $query->where('tag_id', request('tag_id'));
                    });
                });
            }
            if (request('open_business') != null) {
                $query->where(function ($query) {
                    $query->whereHas('extend', function ($query) {
                        $query->where('is_business', request('open_business'));
                        if (request('city')) {
                            $query->where('address', 'like', '%' . request('city') . '%');
                        }
                    });
                });
            }
            if (request('bdate') != null && request('edate')) {
                if (request('search_date') == 0) {
                    $query->WhereBetween('created_at', [request('bdate') . ' 00:00:00', request('edate') . ' 23:59:59']);
                }
            }
        };
        $this->where($where);
        if ($addWhere) {
            if (request('key') == null) {
                $this->where($addWhere);
            }
        }
        if ($with != null) {
            $this->with($with);
        }
        if (request('size') != null ){
            $perPage = request('size');
            return parent::paginate($perPage);
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
}
