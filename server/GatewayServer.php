<?php
/**
 * Created by PhpStorm.
 * @file   GatewayServer.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/28 下午5:15
 * @desc   GatewayServer.php
 */

namespace server;


use protocol\impl\TcpConnection;
use service\ClientService;
use service\ConfigService;
use service\GatewayService;
use service\SyncService;

class GatewayServer extends BaseServer
{
    private $client = null;
    /**
     * 客户端和worker关系绑定
     * @var array
     */
    private $clientWorkerMap = [];

    /**
     * 工作进程
     * @var array
     */
    private $workerProcessList = [];
    /** 端口起始
     * @var int
     */
    public $startPort = 3300;

    /**
     * 监听端口
     * @var int
     */
    private $listenPort = 0;

    /**
     * 初始化服务配置
     */
    protected function __initConfig()
    {
        $this->startPort = ConfigService::get('server.gateway.sync.start_port');
        $this->beforeFork = array($this, 'beforeFork');
        $this->onWorkerStart = array($this, 'onWorkerStart');
        $this->onMessage = array($this, 'onMessage');
    }

    public function beforeFork()
    {
        $this->listenPort = $this->startPort++;
    }

    /**
     * 发送数据到worker进程
     * @param TcpConnection $connection
     * @param $message
     * @throws \Exception
     */
    public function onMessage(TcpConnection $connection, $message)
    {
        $message = $connection->decode($message);
        $this->info("sync worker process message:{$message}");
        $this->syncToWorker($message, $connection);
    }

    public function sysWorkerResponse($socket, $buffer)
    {
        $connection = $this->clientWorkerMap[intval($socket)];
        $this->info("worker execute result:{$buffer}");
        $connection->send($buffer, true);
    }

    /**
     * @param $buffer
     * @param $connection
     * @throws \Exception
     */
    private function syncToWorker($buffer, $connection)
    {

        $connect = ClientService::getInstance();
        $host = ConfigService::get('server.worker.host');
        $port = ConfigService::get('server.worker.port');
        $connect->setRelationCallback(function ($socket) use ($connection) {
            $this->clientWorkerMap[intval($socket)] = $connection;
        })->singleConnect($host, $port, $buffer, array($this, 'sysWorkerResponse'));
    }

    public function syncMessage(TcpConnection $connection, $message)
    {
        $connection->send("1", false);
        foreach ($this->clientList as $connection) {
            $this->info("worker execute result:{$message}");
            $connection->send($message, true);
        }
    }

    /**
     * 内部通讯进程
     */
    public function onWorkerStart()
    {
        $server = new class extends BaseServer {
            /**
             * 初始化服务配置
             */
            protected function __initConfig()
            {
                // TODO: Implement __initConfig() method.
            }
        };
        $server->processCount = 1;
        $server->handshake = false;
        $server->address = ConfigService::get('server.gateway.sync.host');
        $server->port = $this->listenPort;
        $server->onConnect = function (TcpConnection $connect) {

        };
        $server->onMessage = array($this, 'syncMessage');
        $server->createServer();
        $server->listen();
        //注册网关
        SyncService::getInstance()->registerGateway($server->port);
    }
}
