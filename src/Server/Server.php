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
        // TODO 还可以使用 SWOOLE_UNIX_STREAM
        parent::__construct($host, $port, SWOOLE_BASE, SWOOLE_SOCK_TCP);
    }
    
    public function start() {
        $this->on('workerStart', array($this, 'onWorkerStart'));
        $this->on('receive', array($this, 'onReceive'));
        parent::start();
    }
    
    public function onWorkerStart($serv, $worker_id) {
        echo "worker {$worker_id} starts ...\n";
        // TODO 怎样才能把sapi_name从cli改成cgi？可否在swoole_server的配置选项中加一个sapi_name的配置？
        var_dump(php_sapi_name());
    }
    
    // TODO 一次receive不一定能获取完所有的请求数据，需要人工拼接，可以根据请求头中的CONTENT_LENGTH判断是否拼接完整
    public function onReceive($serv, $fd, $from_id, $data) {
        var_dump("fd: {$fd}");
        // file_put_contents('./data.tmp', $data);
        
        // A simple kernel. This is the core of your application
        $kernel = function (RequestInterface $request) {
            // $request->getServerRequest()         returns PSR-7 server request object
            // $request->getHttpFoundationRequest() returns HTTP foundation request object
            
            // TODO 检查确认POST、COOKIE、FILES等变量的正确性
            $serverRequest = $request->getServerRequest();
            $_GET     = $serverRequest->getQueryParams();
            $_POST    = $serverRequest->getParsedBody();
            $_REQUEST = array_merge($_GET, $_POST);
            $_SERVER  = $serverRequest->getServerParams();
            $_COOKIE  = $serverRequest->getCookieParams();
            $_FILES   = $serverRequest->getUploadedFiles();
            
            if (!@is_dir($_SERVER['DOCUMENT_ROOT'])) {
                // TODO stderr, root not found
                return; // TODO 返回一个status非200的Response，表示有问题，并在message中给出问题原因
            }
            
            $doc_root = $_SERVER['DOCUMENT_ROOT'];
            $doc_file = $doc_root . $_SERVER['SCRIPT_FILENAME'];
            if (!file_exists($doc_file)) {
                // TODO stderr, file not found
                return; // TODO 返回一个status非200的Response，表示有问题，并在message中给出问题原因
            }
            
            ob_start();
            chdir($doc_root);
            require $doc_file;
            $str = ob_get_contents();
            ob_end_clean();
            
            // return new HtmlResponse('<h1>Hello, World!</h1>');
            return new HtmlResponse($str);
        };
        
        (new ConnectionHandler(new CallbackWrapper($kernel), new WorkerConnection($data, $serv, $fd)))->ready();
        
        var_dump(4);
        // TODO keepalive，而不关闭
        $serv->close($fd);
        
        var_dump($_FILES);
        
        return;
    }
}