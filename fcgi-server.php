<?php
require __DIR__ . '/vendor/autoload.php';

use Swoole\Fastcgi\Server\Server;

$server = new Server('0.0.0.0', 9501);
$server->set(array(
    'daemonize' => !true,
    'sapi_name' => 'swoole-fcgi',   // swoole_server不支持此配置，并没有什么卵用
));

$server->start();