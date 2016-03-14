<?php namespace WpPack\Support;


use Closure;
use Illuminate\Cache\CacheManager;
use Illuminate\Filesystem\Filesystem;

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
            $cacheManager = new CacheManager(array(
                'files'  => new FileSystem(),
                'config' => array(
                    'cache.driver' => 'file',
                    'cache.path'   => path('cache'),
                    'cache.prefix' => 'wordpress_'
                )
            ));

            $cache = $cacheManager->driver();

            self::$instance = new static($cache);
        }

        return self::$instance;
    }

    public static function get($key)
    {
        $self = static::make();

        return $self->cache->get($key);

    }

    /**
     * Store an item in the cache.
     *
     * @param  string $key
     * @param  mixed $value
     * @param  \DateTime|int $minutes
     * @return void
     */
    public static function put($key, $value, $minutes = 10)
    {
        $self = static::make();

        $self->cache->put($key, $value, (int)$minutes);
    }


    public static function remember($key, $minutes = 10, Closure $callback)
    {

        $self = static::make();

        if (!is_null($value = $self->cache->get($key)))
        {
            return $value;
        }

        $self->cache->put($key, $value = $callback(), (int)$minutes);

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
        $self = static::make();

        return $self->cache->put($key, $value, 0);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return void
     */
    public static function forget($key, $value)
    {
        $self = static::make();

        return $self->cache->forget($key);
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

        if (is_null($self->cache->get($key)))
        {
            $self->cache->put($key, $value, (int)$minutes);

            return true;
        }

        return false;
    }

    public static function has($key)
    {
        $self = static::make();

        return !is_null($self->cache->get($key));
    }


    /**
     * Clear all cache files
     */
    public static function flush()
    {
        $self = static::make();

        $self->cache->flush();
    }
}