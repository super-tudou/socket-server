<?php
/**
 * Created by PhpStorm.
 * @file   WorkerService.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/28 下午1:55
 * @desc   WorkerService.php
 */

namespace service;

use component\ProcessTrait;
use event\EventInterface;
use event\impl\LibEvent;
use protocol\impl\TcpConnection;

/**
 * socket 基础服务
 * Class WorkerService
 * @package service
 */
class WorkerService extends BaseService
{
    use ProcessTrait;

    /**
     * 只通讯一次
     * @var bool
     */
    public $singleLink = false;

    /**
     * 客户端列表
     * @var  TcpConnection[]
     */
    public $clientList = [];

    /**
     * 是否重用端口
     * @var bool
     */
    public $reusePort = false;

    /**
     * 是否需要握手
     * @var bool
     */
    public $handshake = true;
    /**
     * @var
     */
    protected $mainSocket;
    /**
     * @var
     */
    protected $context;
    /**
     * socket 地址
     * @var
     */
    protected $socketAddress;

    /**
     * @var LibEvent
     */
    public static $globalEvent;

    /**
     * Emitted when a socket connection is successfully established.
     *
     * @var callable
     */
    public $onConnect = null;

    /**
     * Emitted when a socket messages received.
     *
     * @var callable
     */
    public $onMessage = null;

    /**
     * Emitted when worker processes start.
     *
     * @var callable
     */
    public $onWorkerStart = null;

    /**
     * @var string
     */
    public $port;
    /**
     * @var string
     */
    public $address;

    /**
     * @param $params
     */
    protected function __init($params)
    {
        $this->socketAddress = $params['socket_address'] ?? '';
        $this->context = stream_context_create($params['context'] ?? []);
        $this->onMessage = $this->onConnect = null;
        if (method_exists($this, '__initConfig')) {
            $this->__initConfig();
        }
    }


    public function start()
    {
        //初始化服务配置
        $this->initConfig();
        //创建基础socket服务
        $this->createServer();
        //启动多进程
        $this->run();
    }

    /**
     *初始化服务配置
     */
    private function initConfig()
    {
        $this->executeCallback = array($this, 'startWorker');
    }

    /**
     * 创建服务
     */
    public function createServer()
    {
        if (empty($this->socketName)) {
            $this->socketAddress = "tcp://{$this->address}:{$this->port}";
        }
        if ($this->reusePort) {
            stream_context_set_option($this->context, 'socket', 'so_reuseport', 1);
        }
        $this->info("server start: {$this->socketAddress}");
        $this->mainSocket = stream_socket_server($this->socketAddress, $errorNo, $errorMsg, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $this->context);
        if (function_exists('socket_import_stream')) {
            $socket = socket_import_stream($this->mainSocket);
            @socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1);
            @socket_set_option($socket, SOL_TCP, TCP_NODELAY, 1);
        }
        stream_set_blocking($this->mainSocket, 0);
        if (function_exists('stream_set_read_buffer')) {
            stream_set_read_buffer($this->mainSocket, 0);
        }
    }

    /**
     * 链接监听
     */
    public function listen()
    {
        $flags = \Event::READ;
        self::$globalEvent->add($this->mainSocket, $flags, [$this, 'acceptTcpConnect']);
    }

    /**
     * 开始工作
     */
    private function startWorker()
    {
        $this->setProcessId(getmypid());
        $this->info("start accept client connect!");
        self::$globalEvent = new LibEvent();
        //监听连接
        $this->listen();
//        self::$globalEvent->add($this->mainSocket, \Event::READ, [$this, 'acceptTcpConnect']);
        if ($this->onWorkerStart) {
            try {
                \call_user_func($this->onWorkerStart, $this);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                exit(250);
            }
        }

        self::$globalEvent->loop();
    }

    /**
     * 获取连接请求
     */
    public function acceptTcpConnect()
    {
        $this->info("start accept client connect!");
        $client = @stream_socket_accept($this->mainSocket, 0, $remoteAddress);
        if (!$client) {
            return;
        }
        $this->info("accept connect[" . intval($client) . "]");
        stream_set_blocking($client, 0);
        if (function_exists('stream_set_read_buffer')) {
            stream_set_read_buffer($client, 0);
        }
        $connection = new TcpConnection(['socket' => $this->mainSocket, 'client' => $client, 'remote_address' => $remoteAddress]);
        $connection->onMessage = $this->onMessage;
        $connection->handshake = $this->handshake;
        $this->clientList[intval($client)] = $connection;
        self::$globalEvent->add($client, \Event::READ, [$connection, 'connect']);

        // Try to emit onConnect callback.
        if ($this->onConnect) {
            try {
                \call_user_func($this->onConnect, $connection);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                exit(250);
            }
        }
    }


}
