<?php

declare(strict_types=1);

namespace WpPack\Support\Hooks;

/**
 * ActionListener::listen('my_action_name_here', $numberOfArguments, [
 *   ExampleListener::class,
 *   - or -
 *   [ExampleListener:class, 99],
 *]);
 * If Exception >> add_action('wppack/action_listener/exception', $action, $exception, $listener);
 * 
 * 
 */
class ExampleListener extends AbstractListener
{
    /**
     * All hook arguments will be passed to this method
     *
     * @param mixed $arguments
     *
     * @return void
     */
    public function handle($arguments = null)
    {
        // do something
    }
}
