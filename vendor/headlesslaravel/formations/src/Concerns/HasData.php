<?php

namespace HeadlessLaravel\Formations\Concerns;

use Illuminate\Support\Str;

trait HasData
{
    /**
     * Override the index method's data.
     *
     * @param $collection
     * @return array
     */
    public function indexData($collection):array
    {
        return [];
    }

    /**
     * Override the show method's data.
     *
     * @param $model
     * @return array
     */
    public function showData($model):array
    {
        return [];
    }

    /**
     * Override the create method's data.
     * @return array
     *
     */
    public function createData():array
    {
        return [];
    }

    /**
     * Override the store method's data.
     *
     * @param $model
     * @return array
     */
    public function storeData($model):array
    {
        return [];
    }

    /**
     * Override the edit method's data.
     *
     * @param $model
     * @return array
     */
    public function editData($model):array
    {
        return [];
    }

    /**
     * Override the update method's data.
     *
     * @param $model
     * @return array
     */
    public function updateData($model):array
    {
        return [];
    }

    /**
     * Override the restore method's data.
     *
     * @param $model
     * @return array
     */
    public function restoreData($model):array
    {
        return [];
    }

    /**
     * Override the destroy method's data.
     *
     * @param $model
     * @return array
     */
    public function destroyData($model):array
    {
        return [];
    }

    /**
     * Override the force delete method's data.
     *
     * @param $model
     * @return array
     */
    public function forceDeleteData($model):array
    {
        return [];
    }

    /**
     * Append the index method's data.
     *
     * @param $collection
     * @return array
     */
    public function extraIndexData($collection):array
    {
        return [];
    }

    /**
     * Append the show method's data.
     *
     * @param $model
     * @return array
     */
    public function extraShowData($model):array
    {
        return [];
    }

    /**
     * Append the create method's data.
     *
     * @return array
     */
    public function extraCreateData():array
    {
        return [];
    }

    /**
     * Append the edit method's data.
     *
     * @param $model
     * @return array
     */
    public function extraEditData($model):array
    {
        return [];
    }

    /**
     * Append the store method's data.
     *
     * @param $model
     * @return array
     */
    public function extraStoreData($model):array
    {
        return [];
    }

    /**
     * Append the update method's data.
     *
     * @param $model
     * @return array
     */
    public function extraUpdateData($model):array
    {
        return [];
    }

    /**
     * Append the restore method's data.
     *
     * @param $model
     * @return array
     */
    public function extraRestoreData($model):array
    {
        return [];
    }

    /**
     * Append the destroy method's data.
     *
     * @param $model
     * @return array
     */
    public function extraDestroyData($model):array
    {
        return [];
    }

    /**
     * Append the force delete method's data.
     *
     * @param $model
     * @return array
     */
    public function extraForceDeleteData($model):array
    {
        return [];
    }

    /**
     * Call the proper response callback.
     *
     * @param $method
     * @param $data
     * @param $props
     * @return mixed
     */
    public function dataCallback($method, $data, $props)
    {
        $method = Str::of($method)->camel();

        $override = $method . 'Data';

        $output = $this->$override($props);

        if(count($output)) {
            return $output;
        }

        $extra = 'extra' . ucfirst($method) . 'Data';

        $output = array_merge($this->$extra($props), $data);

        if(count($output)) {
            return $output;
        }

        return $props ?? [];
    }
}
