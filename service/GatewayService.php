<?php
/**
 * Created by PhpStorm.
 * @file   GatewayService.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/5/7 上午10:40
 * @desc   GatewayService.php
 */

namespace service;

/**
 * 网关服务
 * Class GatewayService
 * @package service
 */
class GatewayService extends BaseService
{
    /**
     * @var array
     */
    private $gatewayList = [];


    public function send($message)
    {
        if (empty($this->gatewayList)) {
            $this->getGatewayList(function ($buffer) use ($message) {
                $this->gatewayList = json_decode($buffer, true);
                $this->sendMessage($this->gatewayList, $message);
            });
        } else {
            $this->sendMessage($this->gatewayList, $message);
        }
    }

    /**
     * @param \Closure $callback
     * @throws \Exception
     */
    public function getGatewayList(\Closure $callback)
    {
        $message = "get_gateway";
        $connect = ClientService::getInstance();
        $host = ConfigService::get('server.register.host');
        $port = ConfigService::get('server.register.port');
        $connect->singleConnect($host, $port, $message, function ($socket, $buffer) use ($callback) {
            $this->info("get gateway list result:" . print_r(array_values(json_decode($buffer, true)), true));
            call_user_func($callback, $buffer);
        });
    }

    public function sendMessage($gatewayList, $message)
    {
        $params = array_map(function ($address) use ($message) {
            return [
                'address' => $address,
                'message' => $message
            ];
        }, $gatewayList);
        $client = ClientService::getInstance();
        $client->setSingleLink()->setServerList($params)->connect();
        $client->listen();
    }
}
