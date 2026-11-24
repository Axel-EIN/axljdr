<?php

use App\Kernel;

if (\PHP_SAPI !== 'cli') {
    unset($_SERVER['argv'], $_SERVER['argc']);
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
