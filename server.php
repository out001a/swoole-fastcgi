<?php
require __DIR__ . '/vendor/autoload.php';

use PHPFastCGI\FastCGIDaemon\Http\RequestInterface;
use PHPFastCGI\FastCGIDaemon\ConnectionHandler\ConnectionHandler;
use PHPFastCGI\FastCGIDaemon\Connection\ConnectionInterface;
use PHPFastCGI\FastCGIDaemon\CallbackWrapper;
use Zend\Diactoros\Response\HtmlResponse;
// use Zend\Diactoros\Response;

class Connection implements ConnectionInterface {
    private $_buffer = '';
    private $_server = null;
    private $_fd     = 0;
    private $_closed = false;
    
    public function __construct($buffer, $server, $fd) {
        var_dump(1);
        $this->_buffer = $buffer;
        $this->_server = $server;
        $this->_fd     = $fd;
    }
    
    public function read($length) {
        var_dump(2);
        return $this->_buffer;
    }
    
    public function write($buffer) {
        var_dump(3);
        $this->_server->send($this->_fd, $buffer);
    }
    
    public function isClosed() {
        return boolval($this->_closed);
    }
    
    public function close() {
        $this->_closed = true;
    }
}

$server = new swoole_server('0.0.0.0', 9501);
$server->set(array(
    'daemonize' => !true,
));

// worker充当fastcgi的工作进程，这里进行初始化，可以分配一些公共资源
$server->on('WorkerStart', function($serv, $worker_id) {
    echo "worker {$worker_id} starts ...\n";
});

// worker进程，接受数据，监听并处理请求
$server->on('receive', function($serv, $fd, $from_id, $data) {
    var_dump("fd: {$fd}");
    
    // A simple kernel. This is the core of your application
    $kernel = function (RequestInterface $request) {
        // $request->getServerRequest()         returns PSR-7 server request object
        // $request->getHttpFoundationRequest() returns HTTP foundation request object
        return new HtmlResponse('<h1>Hello, World!</h1>');
    };
    
    (new ConnectionHandler(new CallbackWrapper($kernel), new Connection($data, $serv, $fd)))->ready();
    var_dump(4);
    $serv->close($fd);
    
    return;
});

$server->start();
