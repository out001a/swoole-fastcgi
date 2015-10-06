<?php
namespace Swoole\Fastcgi\Connection;

use PHPFastCGI\FastCGIDaemon\Connection\ConnectionInterface;

class WorkerConnection implements ConnectionInterface {
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
