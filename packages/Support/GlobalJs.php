<?php namespace WpPack\Support;

use Illuminate\Support\Arr;

/**
 * GlobalJs
 *
 * @author Bruno Barros  <bruno@brunobarros.com>
 * @copyright    Copyright (c) 2015 Bruno Barros
 */
class GlobalJs
{

    private static $instance;

    /**
     * Data to be converted in JSON on view
     * @var array
     */
    private $data = [];

    private $metas = [];



    public static function make()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new static;
        }

        return self::$instance;
    }


    /**
     * Append new data
     *
     * @param string $index Unique identifier
     * @param null $data
     * @param bool $toAdmin If show on admin environment
     * @return $this
     */
    public function add($index, $data = null, $toAdmin = false)
    {
        if (!$this->has($index))
        {
            $this->data = Arr::add($this->data, $index, $data);

            $this->metas = Arr::add($this->metas, $this->firstIndex($index), ['admin' => $toAdmin]);
        }

        return $this;
    }

    private function firstIndex($index)
    {
        return $firstIdx = explode('.', $index)[0];

    }

    /**
     * Update existing index
     *
     * @param $index
     * @param null $data
     * @param null|bool $toAdmin
     * @return $this
     */
    public function update($index, $data = null, $toAdmin = null)
    {

        return $this;
    }

    /**
     * Remove index
     *
     * @param $index
     * @return $this
     */
    public function remove($index)
    {
        if ($this->has($index))
        {
            Arr::forget($this->data, $index);
            Arr::forget($this->metas, $this->firstIndex($index));
        }

        return $this;
    }

    /**
     * Remove all data
     *
     * @return $this
     */
    public function removeAll()
    {
        $this->data  = [];
        $this->metas = [];

        return $this;
    }

    /**
     * check if index already exists
     *
     * @param null $index
     * @return bool
     */
    public function has($index = null)
    {
        return Arr::has($this->data, $index);
    }

    /**
     * Return the data as JSON
     *
     * @param bool $admin
     * @return string|void
     */
    public function toJson($admin = false)
    {
        $data = $this->prepareDataToJson($admin);

        if (count($data) == 0)
        {
            return '{}';
        }

        return json_encode($data);
    }

    /**
     * Return the data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    private function prepareDataToJson($admin = false)
    {
        // apply admin filter
        return Arr::where($this->getData(), function ($key, $value) use ($admin)
        {
            $meta = Arr::get($this->metas, $key);

            return ($admin === false && $meta['admin'] === true) ? false : true;
        });
    }

    /**
     * Generate a nonce hash
     */
    public function generateNonce()
    {
        $key = config('app.key');
        $this->add('nonce', wp_create_nonce($key));
    }

    /**
     * Generate a nonce hash
     */
    public function generateAjaxUrl()
    {
        $this->add('ajaxurl', admin_url('admin-ajax.php'));
    }

}