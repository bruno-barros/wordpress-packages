<?php

if (!function_exists('env'))
{
    /**
     * retrieve the ENV var
     *
     * @param      $envVar
     * @param null $default
     *
     * @return null|string
     */
    function env($envVar, $default = null)
    {
        if (!$value = getenv($envVar))
        {
            return $default;
        }

        return $value;
    }
}

if(! function_exists('path'))
{
    /**
     * Return a specified path, or all paths
     *
     * @param string $folder
     * @return mixed
     */
    function path($folder = 'app')
    {
        $paths = require SRC_PATH . '/bootstrap/paths.php';

        if (isset($paths[$folder]))
        {
            return $paths[$folder];
        }

        return $paths;
    }

}

if(! function_exists('config'))
{

    /**
     * @param string $file
     * @return mixed|null
     */
    function config($file = 'app')
    {
        $path = path('app') . '/config';

        $levels = explode('.', $file);

        if (count($levels) == 1)
        {
            if (file_exists($path . DS . $file . '.php'))
            {
                return require($path . DS . $file . '.php');
            }
            else
            {
                die("<h1>{$path}/{$file}.php not found</h1>");
            }
        }
        else
        {
            if (count($levels) > 1)
            {
                $file = array_shift($levels);

                if (file_exists($path . DS . $file . '.php'))
                {
                    $array = require($path . DS . $file . '.php');
                }
                else
                {
                    die("<h1>{$path}/{$file}.php not found</h1>");
                }

                if (count($levels) == 1 && isset($array[$levels[0]]))
                {
                    return $array[$levels[0]];
                }
                else
                {

                    $pointer = $array;
                    foreach ($levels as $k => $v)
                    {
                        if (isset($pointer[$v]))
                        {
                            $pointer = $pointer[$v];
                        }
                        else
                        {
                            return null;
                        }
                    }

                    return $pointer;

                }

            }
        }

        return null;
    }
}

if(! function_exists('e_view'))
{
    function e_view($view = '', $data = array(), $default = '')
    {
        echo view($view, $data, $default);
    }
}

if(! function_exists('view'))
{

    function view($view = '', $data = array(), $default = '')
    {

        $view = str_replace('.php', '', $view);
        $view = str_replace('.', DIRECTORY_SEPARATOR, $view);
        
        // check if is a module
        $preView = '';
        if(str_contains($view, ':'))
        {
            $arrV = explode(':', $view);
            $preView = $arrV[0] . DIRECTORY_SEPARATOR;
            $view = $arrV[1];
        }

        $path  = $original  = get_stylesheet_directory() . DIRECTORY_SEPARATOR . $preView . 'views' . DIRECTORY_SEPARATOR . $view . '.php';


        if (!file_exists($path))
        {
            $default = str_replace('.php', '', $default);
            $default = str_replace('.', DIRECTORY_SEPARATOR, $default);
            $path = get_stylesheet_directory() . DIRECTORY_SEPARATOR . $preView . 'views' . DIRECTORY_SEPARATOR . $default . '.php';

            if (!file_exists($path))
            {
                die("<h1>View \"{$original}\" not found</h1>");
            }
        }

        global $wp_query, $withcomments, $post, $wpdb, $id, $comment, $user_login, $user_ID, $user_identity, $overridden_cpage;

        ob_start();

        extract((array)$data);
        
        do_action('before_load_view', $path, $data);

        include apply_filters('include_load_view', $path, $data);
        
        do_action('after_load_view', $path, $data);

        return ob_get_clean();

    }
}
