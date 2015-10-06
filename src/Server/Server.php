<?php
namespace Swoole\Fastcgi\Server;

use Swoole\Fastcgi\Connection\WorkerConnection;

use PHPFastCGI\FastCGIDaemon\Http\RequestInterface;
use PHPFastCGI\FastCGIDaemon\ConnectionHandler\ConnectionHandler;
use PHPFastCGI\FastCGIDaemon\CallbackWrapper;
use Zend\Diactoros\Response\HtmlResponse;
// use Zend\Diactoros\Response;

class Server extends \swoole_server {
    public function __construct($host, $port) {
        parent::__construct($host, $port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
    }
    
    public function start() {
        $this->on('workerStart', array($this, 'onWorkerStart'));
        $this->on('receive', array($this, 'onReceive'));
        parent::start();
    }
    
    public function onWorkerStart($serv, $worker_id) {
        echo "worker {$worker_id} starts ...\n";
    }
    
    public function onReceive($serv, $fd, $from_id, $data) {
        var_dump("fd: {$fd}");
        
        // A simple kernel. This is the core of your application
        $kernel = function (RequestInterface $request) {
            $server_request = $request->getServerRequest();
            $_GET     = $server_request->getQueryParams();
            $_POST    = $server_request->getParsedBody();
            $_REQUEST = array_merge($_GET, $_POST);
            $_SERVER  = $server_request->getServerParams();
            $_COOKIE  = $server_request->getCookieParams();
            $_FILES   = $server_request->getUploadedFiles();
            // $request->getServerRequest()         returns PSR-7 server request object
            // $request->getHttpFoundationRequest() returns HTTP foundation request object
            return new HtmlResponse('<h1>Hello, World!</h1>');
        };
        
        (new ConnectionHandler(new CallbackWrapper($kernel), new WorkerConnection($data, $serv, $fd)))->ready();
        var_dump(4);
        $serv->close($fd);
        
        var_dump($_GET);
        
        return;
    }
}