<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Reset test database and load fixtures once per test run
passthru(sprintf(
    'php "%s/bin/console" doctrine:database:drop --force --if-exists --env=test 2>/dev/null',
    dirname(__DIR__),
));
passthru(sprintf(
    'php "%s/bin/console" doctrine:database:create --env=test 2>/dev/null',
    dirname(__DIR__),
));
passthru(sprintf(
    'php "%s/bin/console" doctrine:migrations:migrate --no-interaction --env=test 2>/dev/null',
    dirname(__DIR__),
));
passthru(sprintf(
    'php "%s/bin/console" doctrine:fixtures:load --no-interaction --env=test 2>/dev/null',
    dirname(__DIR__),
));
