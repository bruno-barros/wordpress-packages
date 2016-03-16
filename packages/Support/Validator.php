<?php namespace WpPack\Support;

/**
 * Validator
 *
 * @author Bruno Barros  <bruno@brunobarros.com>
 * @copyright    Copyright (c) 2016 Bruno Barros
 */
class Validator
{
    protected static $factory;

    public static function instance()
    {
        if ( ! static::$factory)
        {
            static::$factory = new \Illuminate\Validation\Factory(new \Symfony\Component\Translation\Translator(get_locale()));
        }

        return static::$factory;
    }

    public static function __callStatic($method, $args)
    {
        $instance = static::instance();

        switch (count($args))
        {
            case 0:
                return $instance->$method();

            case 1:
                return $instance->$method($args[0]);

            case 2:
                return $instance->$method($args[0], $args[1]);

            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);

            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);

            default:
                return call_user_func_array(array($instance, $method), $args);
        }
    }
}