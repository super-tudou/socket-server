<?php
/**
 * Created by PhpStorm.
 * @file   Web.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/29 下午1:41
 * @desc   Web.php
 */

namespace server;


use protocol\impl\TcpConnection;
use service\RequestService;

class WebServer extends BaseServer
{

    /**
     * 初始化服务配置
     */
    protected function __initConfig()
    {
        $this->onMessage = array($this, 'onMessage');
    }

    public function onMessage(TcpConnection $connection, $buffer)
    {
        $request = new  RequestService($buffer);
        $request->parseHeaders();
        if (strpos($request->_data['uri'], 'favicon.ico') !== false) {
            return;
        }
        $response = (new ControllerParseService)->Parse($request->_data['uri']);
        $length = mb_strlen($response);
        $msg = "HTTP/1.1 200 OK\r\nServer: workerman\r\nConnection: keep-alive\r\nContent-Type: text/html;charset=utf-8\r\nContent-Length: {$length}\r\n\r\n";
        $msg .= $response;
        $connection->send($msg);
    }

}


