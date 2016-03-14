<?php namespace WpPack\Http;

/**
 * Request
 *
 * @author Bruno Barros  <bruno@brunobarros.com>
 * @copyright    Copyright (c) 2016 Bruno Barros
 */
class Request
{
    public static $instance;

    private $request;

    /**
     * Request constructor.
     * @param $driver
     */
    public function __construct($driver)
    {

        $this->request = $driver;
    }


    public static function make()
    {
        if (is_null(self::$instance))
        {
            $request = \Illuminate\Http\Request::createFromGlobals();

            self::$instance = new static($request);
        }

        return self::$instance;
    }

    public function __call($name, $params)
    {
        if(method_exists($this, $name))
        {
            return call_user_func_array([$this, $name], $params);
        }
    }

    public static function __callStatic($name, $params)
    {
        $self = static::make();

        if(method_exists($self->request, $name))
        {
            return call_user_func_array([$self->request, $name], $params);
        }
    }
}