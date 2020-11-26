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
            self::$instance = new FilesystemAdapter('', 0, path('cache'));
        }
        
        return self::$instance;
    }
    
    public static function get($key, $default = null)
    {
        return static::make()->get($key, function (ItemInterface $item) use ($default) {
            return $default;
        });
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
        static::make()->get($key, function (ItemInterface $item) use ($value, $minutes) {
            $item->expiresAfter((int)$minutes * 60);
            
            return $value;
        });
    }
    
    
    public static function remember($key, $minutes, Closure $callback)
    {
        return static::make()->get($key, function (ItemInterface $item)
        use ($callback, $minutes) {
            $item->expiresAfter((int)$minutes * 60);
            
            return $callback();
        });
    }
    
    
    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public static function forever($key, $value)
    {
        static::put($key, $value, 525600);
    }
    
    /**
     * Remove an item from the cache.
     *
     * @param string $key
     * @return void
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public static function forget($key)
    {
        static::make()->delete($key);
    }
    
    
    /**
     * Store an item in the cache if the key does not exist.
     *
     * @param string $key
     * @param mixed $value
     * @param \DateTime|int $minutes
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public static function add($key, $value, $minutes = 10)
    {
        /** @var FilesystemAdapter $adapter */
        $adapter = static::make();

        if (!$adapter->hasItem($key))
        {
            static::put($key, $value, (int)$minutes);
            return true;
        }
        
        return false;
    }
    
    public static function has($key)
    {
        return static::make()->hasItem($key);
    }
    
    
    /**
     * Clear all cache files
     */
    public static function flush()
    {
        static::make()->clear();
    }
}