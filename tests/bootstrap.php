<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

// Suffixe la DB par worker pour permettre l'exécution parallèle (paratest)
$token = $_SERVER['TEST_TOKEN'] ?? $_ENV['TEST_TOKEN'] ?? '0';
$dbUrl = sprintf('sqlite:///%s/var/data_test_%s.db', dirname(__DIR__), $token);
$_SERVER['DATABASE_URL'] = $dbUrl;
$_ENV['DATABASE_URL'] = $dbUrl;
putenv('DATABASE_URL='.$dbUrl);

if (method_exists(Dotenv::class, 'bootEnv')) {
    new Dotenv()->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Reset cette DB worker + migrations + fixtures, une fois par process
$projectDir = dirname(__DIR__);
$console = sprintf('DATABASE_URL=%s php "%s/bin/console" --env=test --no-interaction', escapeshellarg($dbUrl), $projectDir);

// SQLite : "drop database" via suppression du fichier ; on n'utilise pas la commande qui ne supporte pas la plateforme
@unlink(sprintf('%s/var/data_test_%s.db', $projectDir, $token));
passthru("$console doctrine:migrations:migrate --quiet 2>/dev/null");
passthru("$console doctrine:fixtures:load 2>/dev/null");
