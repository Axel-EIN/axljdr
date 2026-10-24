<?php

use App\Command\OptimizeImagesCommand;
use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Dotenv\Dotenv;

const DEPLOY_TOKEN = '__TOKEN__';

if (!hash_equals(DEPLOY_TOKEN, (string) ($_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? ''))) {
    http_response_code(404);
    exit;
}

set_time_limit(0);
ignore_user_abort(true);
ini_set('memory_limit', '512M');

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';
(new Dotenv())->bootEnv($root . '/.env');

header('Content-Type: text/plain; charset=utf-8');

if (($_SERVER['HTTP_X_IMAGE_ACTION'] ?? '') === 'list') {
    echo implode("\n", array_keys(OptimizeImagesCommand::TYPES));
    exit;
}

$types = preg_split('/\s+/', trim((string) ($_SERVER['HTTP_X_IMAGE_TYPES'] ?? '')), -1, PREG_SPLIT_NO_EMPTY);

$application = new Application(new Kernel('prod', false));
$application->setAutoExit(false);

$output = new BufferedOutput();
$status = $application->run(new ArrayInput([
    'command' => 'app:images:optimize',
    'types'   => $types,
]), $output);

if ($status !== 0) {
    http_response_code(500);
}

echo $output->fetch() . "\nexit=$status\n";
