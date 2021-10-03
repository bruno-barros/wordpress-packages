<?php namespace WpPack\Http;

use WpPack\Exceptions\AjaxException;

/**
 * Ajax
 *
 * @author Bruno Barros  <bruno@brunobarros.com>
 * @copyright    Copyright (c) 2016 Bruno Barros
 */
class Ajax
{
    private static $instance;


    public static function make()
    {
        if(is_null(self::$instance))
        {
            self::$instance = new static;
        }

        return self::$instance;
    }

    /**
     * Handle the Ajax response. Run the appropriate
     * action hooks used by WordPress in order to perform
     * POST ajax request securely.
     * Developers have the option to run ajax for the
     * Front-end, Back-end either users are logged in or not
     * or both.
     *
     * @param string $action Your ajax 'action' name
     * @param string $logged Accepted values are 'no', 'yes', 'both'
     * @param string $callable The function to run when ajax action is called
     * @throws AjaxException
     */
    public function listen($action, $logged = 'no', $callable = null)
    {

        if (!is_string($action) || strlen($action) == 0)
        {
            throw new AjaxException("Invalid parameter for the action.");
        }

        // resolve callable
        if (is_callable($callable))
        {
            $this->registerWithClosure($action, $logged, $callable);
        }
        else
        {

            if (strpos($callable, '@') !== false)
            {
                $parts    = explode('@', $callable);
                $callable = $parts[0];
                $method   = $parts[1];
            }
            else
            {
                $method = 'run';// default
            }

            try
            {
                if(!is_object($callable))
                {
                    $callable = new $callable;
                }
                $this->registerWithClassInstance($action, $logged, $callable, $method);

            } catch (\Exception $e)
            {
                throw new AjaxException($e->getMessage());
            }

        }

    }


    /**
     * Check nonce against app.key
     *
     * @param null|string $nonce By default will use 'nonce' index
     * @param null|string $value By default will get app.key
     * @param null|string $message By default try to get translation on ajax.invalid
     * @param null|mixed $data
     */
    public function validate($nonce = null, $value = null, $message = null, $data = null)
    {

        $nonce = ($nonce) ? $nonce : Request::get('nonce');
        $value = ($value) ? $value : config('app.key');

        if (!wp_verify_nonce($nonce, $value))
        {
            $default = 'AJAX is invalid.';

            $data = ($data) ? $data : Request::except(['nonce', 'action']);

            wp_send_json([
                'error' => true,
                'msg'   => ($message) ? $message : $default,
                'data'  => $data
            ]);
        }
    }


    /**
     * Register actions with closure callback
     *
     * @param $action
     * @param $logged
     * @param callable $closure
     */
    private function registerWithClosure($action, $logged, callable $closure)
    {
        $this->registerActions($action, $logged, $closure);
    }


    /**
     * @param $action
     * @param $logged
     * @param $instance
     * @param string $method
     */
    private function registerWithClassInstance($action, $logged, $instance, $method = 'run')
    {
        $this->registerActions($action, $logged, array($instance, $method));
    }


    /**
     * @param $action
     * @param $logged
     * @param $callable
     */
    private function registerActions($action, $logged, $callable)
    {
        // Front-end ajax for non-logged users
        // Set $logged to FALSE
        if ($logged === 'no' || !$logged)
        {
            add_action('wp_ajax_nopriv_' . $action, $callable);
        }

        // Front-end and back-end for logged users
        if ($logged === 'yes')
        {
            add_action('wp_ajax_' . $action, $callable);
        }

        // Front-end and back-end for both logged in or out users
        if ($logged === 'both')
        {
            add_action('wp_ajax_nopriv_' . $action, $callable);
            add_action('wp_ajax_' . $action, $callable);
        }
    }



    public static function __callStatic($name, $params)
    {
        $self = static::make();

        if(method_exists($self, $name))
        {
            return call_user_func_array([$self, $name], $params);
        }
    }
}