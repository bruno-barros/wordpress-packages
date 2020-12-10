<?php namespace WpPack\Support;

use Closure;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;
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
    public static $tagInstance;
    
    private $cache;
    
    /**
     * Cache constructor.
     * @param $driver
     */
    public function __construct($driver)
    {
        $this->cache = $driver;
    }
    
    /**
     * @param bool $tagSupport
     * @return FilesystemAdapter|FilesystemTagAwareAdapter
     */
    public static function make($tagSupport = false)
    {
        if (is_null(self::$instance))
        {
            self::$instance = new FilesystemAdapter('weloquent', 0, path('cache'));
        }
        if (is_null(self::$tagInstance))
        {
            self::$instance = new FilesystemTagAwareAdapter('weloquent', 0, path('cache'));
        }
        
        return $tagSupport ? self::$tagInstance : self::$instance;
    }
    
    public static function get($key, $default = null)
    {
        try
        {
            return static::make()->get($key, function (ItemInterface $item) use ($default) {
                return $default;
            });
        } catch (InvalidArgumentException $e)
        {
            return null;
        }
    }
    
    /**
     * Store an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @param int $minutes
     * @return void
     */
    public static function put($key, $value, $minutes = 10)
    {
        try
        {
            static::make()->get($key, function (ItemInterface $item) use ($value, $minutes) {
                $item->expiresAfter((int)$minutes * 60);
                
                return $value;
            });
        } catch (InvalidArgumentException $e)
        {
        }
    }
    
    /**
     * @param $key
     * @param $minutes
     * @param Closure $callback
     * @param null|string|array $tags
     * @return mixed
     */
    public static function remember($key, $minutes, Closure $callback, $tags = null)
    {
        try
        {
            return static::make((bool)$tags)->get($key, function (ItemInterface $item)
            use ($callback, $minutes, $tags) {
                $item->expiresAfter((int)$minutes * 60);
                if ($tags)
                {
                    $item->tag($tags);
                }
                
                return $callback();
            });
        } catch (InvalidArgumentException $e)
        {
        }
    }
    
    
    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function forever($key, $value)
    {
        try
        {
            static::put($key, $value, 525600);
        } catch (InvalidArgumentException $e)
        {
        }
    }
    
    /**
     * Remove an item from the cache.
     *
     * @param string $key
     * @return void
     */
    public static function forget($key)
    {
        try
        {
            static::make()->delete($key);
        } catch (InvalidArgumentException $e)
        {
        }
    }
    
    /**
     * Remove an item from the cache.
     *
     * @param array $tags
     * @return void
     */
    public static function forgetTags(array $tags)
    {
        try
        {
            static::make(true)->invalidateTags($tags);
        } catch (InvalidArgumentException $e)
        {
        }
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
        try
        {
            return static::make()->hasItem($key);
        } catch (InvalidArgumentException $e)
        {
            return false;
        }
    }
    
    
    /**
     * Clear all cache files
     */
    public static function flush()
    {
        static::make()->clear();
    }
}