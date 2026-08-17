<?php

/**
 * Script de déploiement éphémère.
 *
 * La CI le dépose sur le FTP sous un nom aléatoire, l'appelle une fois, puis le
 * supprime — il ne survit pas au déploiement. Le jeton `__TOKEN__` est réécrit à
 * chaque exécution : il n'est ni dans ce dépôt, ni dans un secret permanent.
 *
 * Ne jamais copier ce fichier à la main dans public/ : il n'y a pas d'accès SSH
 * ici, mais un tel fichier laissé en place vaut un accès équivalent.
 */

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;

const DEPLOY_TOKEN = '__TOKEN__';

// 404 plutôt que 403 : une sonde ne doit pas pouvoir distinguer un jeton faux
// d'un fichier absent. hash_equals pour ne pas fuiter le jeton au chronomètre.
if (!hash_equals(DEPLOY_TOKEN, (string) ($_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? ''))) {
    http_response_code(404);
    exit;
}

set_time_limit(0);
ignore_user_abort(true);

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';
(new Dotenv())->bootEnv($root . '/.env');

$log = '';

// Purge AVANT tout boot : le kernel se reconstruira ensuite sur le code
// fraîchement déployé, et non sur le conteneur compilé de la version précédente.
$cacheDir = $root . '/var/cache/prod';
if (is_dir($cacheDir)) {
    (new Filesystem())->remove($cacheDir);
    $log .= "cache : $cacheDir supprimé\n\n";
} else {
    $log .= "cache : déjà vide\n\n";
}

$application = new Application(new Kernel('prod', false));
$application->setAutoExit(false);

$output = new BufferedOutput();
$status = $application->run(new ArrayInput([
    'command'              => 'doctrine:migrations:migrate',
    '--no-interaction'     => true,
    '--allow-no-migration' => true,
]), $output);

$log .= $output->fetch() . "\nexit=$status\n";

// Le code HTTP se fixe avant le moindre echo, sinon les en-têtes sont déjà partis.
if ($status !== 0) {
    http_response_code(500);
}

header('Content-Type: text/plain; charset=utf-8');
echo $log;
