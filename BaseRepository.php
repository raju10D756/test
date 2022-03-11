<?php

namespace DTApi\Repository;

use DTApi\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Validator;

class BaseRepository
{

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $validationRules = [];

    /**
     * @param Model $model
     */
    public function __construct(Model $model = null)
    {
        $this->model = $model;
    }

    /**
     * @return array
     */
    public function validatorAttributeNames(): array
    {
        return [];
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|Model[]
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * @param integer $id
     * @return Model|null
     */
    public function find(int $id)
    {
        return $this->model->find($id);
    }

    public function with($array)
    {
        return $this->model->with($array);
    }

    /**
     * @param integer $id
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * @param string $slug
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findBySlug(string $slug): Model
    {

        return $this->model->where('slug', $slug)->first();

    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->query();
    }

    /**
     * @param array $attributes
     * @return Model
     */
    public function instance(array $attributes = []): Model
    {
        $model = $this->model;
        return new $model($attributes);
    }

    /**
     * @param int|null $perPage
     * @return mixed
     */
    public function paginate(int $perPage = null)
    {
        return $this->model->paginate($perPage);
    }

    /**
     * @param $key
     * @param $where
     * @return mixed
     */
    public function where($key, $where)
    {
        return $this->model->where($key, $where);
    }

    /**
     * @param array $data
     * @param null $rules
     * @param array $messages
     * @param array $customAttributes
     * @return \Illuminate\Validation\Validator
     */
    public function validator(array $data = [], $rules = null, array $messages = [], array $customAttributes = []): \Illuminate\Validation\Validator
    {
        if (is_null($rules)) {
            $rules = $this->validationRules;
        }

        return Validator::make($data, $rules, $messages, $customAttributes);
    }

    /**
     * @param array $data
     * @param null $rules
     * @param array $messages
     * @param array $customAttributes
     * @return bool
     * @throws ValidationException
     */
    public function validate(array $data = [], $rules = null, array $messages = [], array $customAttributes = []): bool
    {
        $validator = $this->validator($data, $rules, $messages, $customAttributes);
        return $this->_validate($validator);
    }

    /**
     * @param array $data
     * @return Model
     */
    public function create(array $data = []): Model
    {
        return $this->model->create($data);
    }

    /**
     * @param integer $id
     * @param array $data
     * @return Model
     */
    public function update(int $id, array $data = []): Model
    {
        $instance = $this->findOrFail($id);
        $instance->update($data);
        return $instance;
    }

    /**
     * @param integer $id
     * @return Model
     * @throws \Exception
     */
    public function delete(int $id): Model
    {
        $model = $this->findOrFail($id);
        $model->delete();
        return $model;
    }

    /**
     * @param \Illuminate\Validation\Validator $validator
     * @return bool
     * @throws ValidationException
     */
    protected function _validate(\Illuminate\Validation\Validator $validator): bool
    {
        if (!empty($attributeNames = $this->validatorAttributeNames())) {
            $validator->setAttributeNames($attributeNames);
        }

        if ($validator->fails()) {
            throw (new ValidationException)->setValidator($validator);
        }

        return true;
    }

}