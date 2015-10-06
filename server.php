<?php
require __DIR__ . '/vendor/autoload.php';

use Swoole\Fastcgi\Server\Server;

$server = new Server('0.0.0.0', 9501);
$server->set(array(
    'daemonize' => !true,
));

$server->start();