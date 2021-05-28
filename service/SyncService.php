<?php
/**
 * Created by PhpStorm.
 * @file   SyncService.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/28 下午5:41
 * @desc   SyncService.php
 */

namespace service;

/**
 * 同步服务
 * Class SyncService
 * @package service
 */
class SyncService extends BaseService
{
    public function registerGateway($port, $host = '127.0.0.1')
    {
        $address = ConfigService::get('server.register.host') . ":" . ConfigService::get('server.register.port');
        $message = "register_gateway@{$host}:{$port}";
        $params = [[
            'address' => $address,
            'message' => $message
        ]];
        $client = ClientService::getInstance();
        $client->setSingleLink()->setServerList($params)->connect();
        $client->listen();
    }

    /**
     * @throws \Exception
     */
    public function connectGateway()
    {
        $gatewayList = $this->getGatewayList();

        $gatewayList = array_map(function ($item) {
            return [
                'address' => $item,
                'message' => 'worker['.getmypid().']'
            ];
        }, $gatewayList);

//        $connect = ClientService::getInstance();
//        $connect->setServerList($gatewayList);
//        $connect->setOnMessage(function ($socket, $buffer) {
//            $this->info("connect to gateway[" . intval($socket) . "] {$buffer}");
//        });
//        $connect->connect();
//        $connect->listen();
    }

    public function getGatewayList()
    {
        $message = "get_gateway";
        $connect = ClientService::getInstance();
        $host = ConfigService::get('server.register.host');
        $port = ConfigService::get('server.register.port');
        $connect->singleConnect($host, $port, $message, function ($socket, $buffer) {
            $this->info("worker execute result:{$buffer}");
        });
    }

    public function sendMessage()
    {

    }

}
