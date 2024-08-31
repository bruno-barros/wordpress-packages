<?php

declare(strict_types=1);

namespace WpPack\Http\Routing;

use WP_REST_Request;

interface RequestValidationInterface
{
    public static function validate(WP_REST_Request $request);
}
