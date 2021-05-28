<?php
/**
 * Created by PhpStorm.
 * @file   WorkerServer.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/28 下午6:17
 * @desc   WorkerServer.php
 */

namespace server;


use protocol\impl\TcpConnection;
use service\SyncService;

class WorkerServer extends BaseServer
{
    /**
     * 初始化服务配置
     */
    protected function __initConfig()
    {
        $this->onMessage = array($this, 'onMessage');
        $this->onWorkerStart = array($this, 'onWorkerStart');
    }


    public function onMessage(TcpConnection $connection, $buffer)
    {
        $connection->send($buffer);
    }

    /**
     * @throws \Exception
     */
    public function onWorkerStart()
    {
        //链接Gateway
//        SyncService::getInstance()->connectGateway();
    }

}
