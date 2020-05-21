<?php

namespace App\Repositories\Eloquent;

use App\Exceptions\GeneralException;
use App\Repositories\Contracts\CriteriaInterface;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Criteria\Criteria;
use App\Repositories\Exceptions\RepositoryException;
use App\Traits\ResultTrait;
use Exception;
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

/**
 * Class Repository
 * @package Bosnadev\Repositories\Eloquent
 */
abstract class Repository implements RepositoryInterface, CriteriaInterface
{
    use ResultTrait;
    /**
     * @var App
     */
    private $app;

    /**
     * @var
     */
    protected $model;

    protected $newModel;

    /**
     * @var Collection
     */
    protected $criteria;

    /**
     * @var bool
     */
    protected $skipCriteria = false;

    /**
     * Prevents from overwriting same criteria in chain usage
     * @var bool
     */
    protected $preventCriteriaOverwriting = true;

    /**
     * Repository constructor.
     * @param App $app
     * @param Collection $collection
     * @throws RepositoryException
     */
    public function __construct(App $app, Collection $collection)
    {
        $this->app = $app;
        $this->criteria = $collection;
        $this->resetScope();
        $this->makeModel();
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public abstract function model();

    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array('*'))
    {
        $this->applyCriteria();
        return $this->model->get($columns);
    }


    /**
     * @param int $perPage
     * @param array $columns
     * @return mixed
     */

    public function paginate($perPage = 25, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->orderBy();
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * @return $this
     */
    public function orderBy($field = 'created_at', $order = 'asc')
    {
        $this->model = $this->model->orderBy($field, $order);
        return $this;
    }

    /**
     * @return $this
     */
    public function with(array $relations)
    {
        $this->model = $this->model->with($relations);
        return $this;
    }


    /**
     * @param array $relations
     * @return $this
     */
    public function withCount(array $relations)
    {
        $this->applyCriteria();
        $this->model = $this->model->withCount($relations);
        return $this;
    }

    public function withTrashed()
    {
        $this->model = $this->model->withTrashed();
        return $this;
    }

    public function where($where)
    {
//        $this->applyCriteria();
        $this->model = $this->model->where($where);
        return $this;
    }

    public function whereHas($attribute, $where)
    {
//        $this->applyCriteria();
        $this->model = $this->model->whereHas($attribute, $where);
        return $this;
    }

    public function has($attribute)
    {
//        $this->applyCriteria();
        $this->model = $this->model->has($attribute);
        return $this;
    }

    public function whereBy($attribute, $value)
    {
        $this->applyCriteria();
        $this->model = $this->model->where($attribute, '=', $value);
        return $this;
    }

    public function whereNotIn($attribute, $ids)
    {
        $this->applyCriteria();
        $this->model = $this->model->whereNotIn($attribute, $ids);
        return $this;
    }

    public function store(array $data)
    {
        try {
            if ($this->model->fill($data)->save()) {
                return $this->succeed($this->model);
            }
            return $this->failure(1, '创建失败');
        } catch (Exception $exception) {
            return $this->exception($exception);

        }
    }


    /**
     * @param array $data
     * @return mixed
     */
    public function firstOrCreate(array $data)
    {
        $model = $this->model->firstOrCreate($data);
        if ($model) {
            $model = json_decode(json_encode($model), true);
            return $this->succeed($model);
        }
        return $this->failure();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function firstOrNew(array $data)
    {
        $model = $this->model->firstOrNew($data);
        if ($model) {
            $model = json_decode(json_encode($model), true);
            return $this->succeed($model);
        }
        return $this->failure();
    }


    /**
     * @param   $data
     * @param string $attribute
     * @return array|mixed
     */
    public function update($data, $attribute = "id")
    {
        $this->applyCriteria();

        $model = $this->findBy($attribute, $data[$attribute]);
        if (!isset($model)) {
            return $this->failure(1, '未找到数据');
        }
        if (!is_array($data)) {
            $data = json_decode(json_encode($data), true);
        }

        if ($model->fill($data)->save()) {
            return $this->succeed($this->model);
        }
        return $this->failure(1, '更新失败');
    }

    /**
     * @param $id
     * @return mixed
     */
    public function destroy(array $ids)
    {
        $ids = is_array($ids) ? $ids : (is_string($ids) ? explode(',', $ids) : func_get_args());
        $count = $this->model->destroy($ids);
        if ($count > 0) {
            return $this->succeed($count);
        }
        return $this->failure();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function forceDelete(array $ids)
    {
        $ids = is_array($ids) ? $ids : (is_string($ids) ? explode(',', $ids) : func_get_args());
        $list = $this->model->withTrashed()->whereIn('id', $ids)->get();
        foreach ($list as $item) {
            $item->forceDelete();
        }
        return $this->succeed(count($list));
//        if ($count > 0) {
//            return $this->succeed($count);
//        }
//        return $this->failure();
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = array('*'))
    {
        $this->applyCriteria();
        return $this->model->find($id, $columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($attribute, $value, $columns = array('*'))
    {
        $this->applyCriteria();
        return $this->model->where($attribute, '=', $value)->first($columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findAllBy($attribute, $value, $columns = array('*'))
    {
        $this->applyCriteria();
        return $this->model->where($attribute, '=', $value)->get($columns);
    }

    /**
     * Find a collection of models by the given query conditions.
     *
     * @param array $where
     * @param array $columns
     * @param bool $or
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function findWhere($where, $columns = ['*'], $or = false)
    {
        $this->applyCriteria();
        $model = $this->model;
        foreach ($where as $field => $value) {
            if ($value instanceof \Closure) {
                $model = (!$or)
                    ? $model->where($value)
                    : $model->orWhere($value);
            } elseif (is_array($value)) {
                if (count($value) === 3) {
                    list($field, $operator, $search) = $value;
                    $model = (!$or)
                        ? $model->where($field, $operator, $search)
                        : $model->orWhere($field, $operator, $search);
                } elseif (count($value) === 2) {
                    list($field, $search) = $value;
                    $model = (!$or)
                        ? $model->where($field, '=', $search)
                        : $model->orWhere($field, '=', $search);
                }
            } else {
                $model = (!$or)
                    ? $model->where($field, '=', $value)
                    : $model->orWhere($field, '=', $value);
            }
        }
        return $model->get($columns);
    }

    /**
     * 创建查询构造器
     *
     * @return mixed
     */
    public function query()
    {
        return call_user_func($this->model . '::query');
    }

    /**
     * @return Model
     * @throws RepositoryException
     */
    public function makeModel()
    {
        return $this->setModel($this->model());
    }

    /**
     * Set Eloquent Model to instantiate
     *
     * @param $eloquentModel
     * @return Model
     * @throws RepositoryException
     */
    public function setModel($eloquentModel)
    {
        $this->newModel = $this->app->make($eloquentModel);

        if (!$this->newModel instanceof Model)
            throw new RepositoryException("Class {$this->newModel} must be an instance of Illuminate\\Database\\Eloquent\\Model");

        return $this->model = $this->newModel;
    }

    /**
     * @return $this
     */
    public function resetScope()
    {
        $this->skipCriteria(false);
        return $this;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function skipCriteria($status = true)
    {
        $this->skipCriteria = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @param Criteria $criteria
     * @return $this
     */
    public function getByCriteria(Criteria $criteria)
    {
        $this->model = $criteria->apply($this->model, $this);
        return $this;
    }

    /**
     * @param Criteria $criteria
     * @return $this
     */
    public function pushCriteria(Criteria $criteria)
    {
        if ($this->preventCriteriaOverwriting) {
            // Find existing criteria
            $key = $this->criteria->search(function ($item) use ($criteria) {
                return (is_object($item) && (get_class($item) == get_class($criteria)));
            });

            // Remove old criteria
            if (is_int($key)) {
                $this->criteria->offsetUnset($key);
            }
        }

        $this->criteria->push($criteria);
        return $this;
    }

    /**
     * @return $this
     */
    public function applyCriteria()
    {
        if ($this->skipCriteria === true)
            return $this;

        foreach ($this->getCriteria() as $criteria) {
            if ($criteria instanceof Criteria)
                $this->model = $criteria->apply($this->model, $this);
        }

        return $this;
    }
}
