<?php

namespace App\Repositories;

use App\Traits\ResultTrait;
use App\Repositories\Eloquent\Repository;
use Illuminate\Container\Container as App;

/**
 * Class Repository
 * @package Bosnadev\Repositories\Eloquent
 */
abstract class BaseRepository extends Repository
{


    /**
     * @param int $perPage
     * @param array $columns
     * @return mixed
     */

    public function paginate($perPage = 25, $columns = ['*'])
    {
        if (request('limit')) {
            $perPage = request('limit');
        }

        return parent::paginate($perPage, $columns);
    }

    public function orderBy($field = 'created_at', $order = 'desc')
    {
        if (request('field')) {
            $field = request('field');
        }
        if (request('order')) {
            $order = request('order');
        }
        return parent::orderBy($field, $order);
    }

    public function lists($addWhere = null)
    {
        if ($addWhere) {
            $this->where($addWhere);
        }
        return parent::paginate();
    }



}
