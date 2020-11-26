<?php namespace WpPack\Support;


use Closure;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Cache
 *
 * @author Bruno Barros  <bruno@brunobarros.com>
 * @copyright    Copyright (c) 2016 Bruno Barros
 */
class Cache
{
    public static $instance;

    private $cache;

    /**
     * Cache constructor.
     * @param $driver
     */
    public function __construct($driver)
    {

        $this->cache = $driver;
    }


    public static function make()
    {
        if (is_null(self::$instance))
        {
//            $cacheManager = new CacheManager(array(
//                'files'  => new FileSystem(),
//                'config' => array(
//                    'cache.driver' => 'file',
//                    'cache.path'   => path('cache'),
//                    'cache.prefix' => 'wordpress_'
//                )
//            ));
//
//            $cache = $cacheManager->driver();
//
//            self::$instance = new static($cache);
            self::$instance = new FilesystemAdapter('', 0, path('cache'));
    
        }

        return self::$instance;
    }

    public static function get($key, $default = null)
    {
        return static::make()->get($key, function (ItemInterface $item) use($default) {
            return $default;
        });
//        return static::make()->get($key, $default);

    }
    
    /**
     * Store an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @param int $minutes
     * @return void
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public static function put($key, $value, $minutes = 10)
    {
        static::make()->get($key, function (ItemInterface $item) use($value, $minutes) {
                 $item->expiresAfter((int)$minutes * 60);
                 return $value;
             });
    }


    public static function remember($key, $minutes = 10, Closure $callback)
    {

        $self = static::make();

        if ($self->has($key))
        {
            return $self->get($key);
        }

        $self->set($key, $value = $callback(), (int)$minutes * 60);

        return $value;

    }


    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public static function forever($key, $value)
    {
        static::make()->set($key, $value, 0);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return void
     */
    public static function forget($key)
    {
        static::make()->delete($key);
    }


    /**
     * Store an item in the cache if the key does not exist.
     *
     * @param  string $key
     * @param  mixed $value
     * @param  \DateTime|int $minutes
     * @return bool
     */
    public static function add($key, $value, $minutes = 10)
    {
        $self = static::make();

        if (!$self->has($key))
        {
            $self->set($key, $value, (int)$minutes * 60);

            return true;
        }

        return false;
    }

    public static function has($key)
    {
        return static::make()->has($key);
    }


    /**
     * Clear all cache files
     */
    public static function flush()
    {
        static::make()->clear();
    }
}