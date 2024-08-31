<?php

declare(strict_types=1);

namespace WpPack\Support\Hooks;

use Exception;

class ActionListener
{
    public static function listen(string $action, int $arguments = 1, array $listeners = [])
    {

        if(count($listeners) == 0) {
            return;
        }

        $priority = 10;
        foreach ($listeners as $listener) {

            if (is_array($listener)) {
                $class = $listener[0];
                $priority = $listener[1];
            } else {
                $class = $listener;
            }

            try {

                $obj = new $class;
                $obj->action = $action;

                add_action($action, [$obj, 'handle'], $priority, $arguments);
            } catch (Exception $e) {
                do_action('wppack/action_listener/exception', $action, $e, $listener);
            }
        }
    }
}
