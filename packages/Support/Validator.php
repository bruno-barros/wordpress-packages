<?php namespace WpPack\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;

/**
 * Validator
 *
 * @author Bruno Barros  <bruno@brunobarros.com>
 * @copyright    Copyright (c) 2016 Bruno Barros
 */
class Validator
{
    protected static $factory;

    public static function setup()
    {
        if ( ! static::$factory)
        {
            static::$factory = new \Illuminate\Validation\Factory(static::loadTranslator(get_locale()));
        }

        return static::$factory;
    }
    
    protected static function loadTranslator($lang = 'pt_br')
    {
        $lang = strtolower($lang);
        
        $filesystem = new Filesystem();

        $loader = new FileLoader(
            $filesystem, SRC_PATH . '/languages');
        $loader->addNamespace(
            'lang',
            SRC_PATH . '/languages'
        );
        $loader->load($lang, 'validation', 'lang');
        return new Translator($loader, $lang);
    }

    public static function __callStatic($method, $args)
    {
        $instance = static::setup();

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
