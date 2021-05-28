<?php
/**
 * Created by PhpStorm.
 * @file   RegisterServer.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/28 下午2:42
 * @desc   RegisterServer.php
 */

namespace server;

use protocol\impl\TcpConnection;

/**
 * 注册服务
 * Class RegisterServer
 * @package server
 */
class RegisterServer extends BaseServer
{
    /**
     * 网管地址注册
     * @var array
     */
    private $gatewayList = [];

    /**
     * 初始化服务配置
     */
    protected function __initConfig()
    {
        $this->onMessage = array($this, 'onMessage');
    }

    public function onMessage(TcpConnection $connection, $buffer)
    {
        $params = explode("@", $buffer);
        if ($params[0] == 'get_gateway') {  //获取网管列表
            $gatewayList = json_encode($this->gatewayList);
            $connection->send($gatewayList);
        } elseif ($params[0] == 'register_gateway') {
            $this->saveGateway($params[1]);
            $connection->send('success');
        }
    }

    /**
     * @param TcpConnection $connection
     * @param $buffer
     */
    public function saveRegister(TcpConnection $connection, $buffer)
    {
        $params = explode("-", $buffer);
        if (trim($params[0]) == 'gateway') {
            $this->saveGateway($params[1]);
        }
        print_r($this->gatewayList);
    }

    /**
     * @param $host
     */
    private function saveGateway($host)
    {
        $params = explode(":", $host);
        $this->gatewayList[md5($host)] = $host;
    }
}
