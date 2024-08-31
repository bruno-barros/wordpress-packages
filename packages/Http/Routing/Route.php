<?php

declare(strict_types=1);

namespace WpPack\Http\Routing;

/**
 * add_action('rest_api_init', 'create_api_routes_here');
 * 
 * $appRoutes = Route::make([
 *    'namespace' => 'app',
 *    'protected' => true,
 *  ]);
 *  $appRoutes->register('/debug', [(new DebugAppController), 'index'], ['POST', 'GET']);
 *  $appRoutes->post('/debug', [(new DebugAppController), 'index']);
 */
class Route
{
    protected $namespace = 'site/v1';
    protected $protected = true; // bool | array



    public function __construct($options = [])
    {
        if (isset($options['namespace'])) {
            $this->namespace = $options['namespace'];
        }
        if (isset($options['protected'])) {
            $this->protected = $options['protected'];
        }
    }

    public static function make($options = [])
    {
        return new static($options);
    }

    public function register($path = '/', $controller = [], $methods = ['POST'], $protected = null)
    {
        $bypass = [$this, 'bypassValidation'];
        $basic = [BasicRequestValidation::class, 'validate'];
        $permissionController = $basic;
        if (is_array($protected)) {
            $permissionController = $protected;
        } else if (is_bool($protected)) {
            $permissionController = $protected ? $basic : $bypass;
        } else if (is_bool($this->getProtected())) {
            $permissionController = $this->getProtected() ? $basic : $bypass;
        }

        register_rest_route($this->getNamespace(), $path, [
            'methods' => (array)$methods,
            'callback' => $controller,
            'permission_callback' => $permissionController
        ]);
    }


    public function get($path = '/', $controller = [], $protected = null)
    {
        $this->register($path, $controller, ['GET'], $protected);
    }

    public function post($path = '/', $controller = [], $protected = null)
    {
        $this->register($path, $controller, ['POST'], $protected);
    }

    public function put($path = '/', $controller = [], $protected = null)
    {
        $this->register($path, $controller, ['PUT'], $protected);
    }

    public function delete($path = '/', $controller = [], $protected = null)
    {
        $this->register($path, $controller, ['DELETE'], $protected);
    }

    public function bypassValidation()
    {
        return true;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getProtected()
    {
        return $this->protected;
    }
}
