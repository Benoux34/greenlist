<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

$api = dirname(__DIR__, 2).'/api';

require $api.'/vendor/autoload.php';

// No environment file is versioned: on a fresh clone (the CI) the variables
// come from the environment itself.
if (is_file($api.'/.env')) {
    (new Dotenv())->bootEnv($api.'/.env');
}
