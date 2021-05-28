<?php
/**
 * Created by PhpStorm.
 * @file   ClientService.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/28 下午4:16
 * @desc   ClientService.php
 */

namespace service;

use Couchbase\Cluster;
use event\impl\LibEvent;

/**
 * 客户端服务
 * Class ClientService
 * @package service
 */
class ClientService extends BaseService
{

    public $onMessage = null;

    private $clientSocketList = [];

    private $relationCallback = null;

    public static function getInstance($params = [])
    {
        return new self();
    }

    /**
     * socket 服务列表
     * @var array
     */
    private $serverList = [];

    /**
     * 只通讯一次
     * @var bool
     */
    public $singleLink = false;


    /**
     * @var LibEvent
     */
    public static $clientEvent;

    /**
     * @param $params
     */
    protected function __init($params)
    {
        self::$clientEvent = new LibEvent();
    }

    /**
     * @param $onMessage
     * @return $this
     */
    public function setOnMessage($onMessage)
    {
        $this->onMessage = $onMessage;
        return $this;
    }

    /**
     * @return $this
     */
    public function setSingleLink()
    {
        $this->singleLink = true;
        return $this;
    }

    /**
     * @param array $serverList
     * @return $this
     */
    public function setServerList(array $serverList)
    {
        $this->serverList = $serverList;
        return $this;
    }

    /**
     * @param $relationCallback
     * @return $this
     */
    public function setRelationCallback($relationCallback)
    {
        $this->relationCallback = $relationCallback;
        return $this;
    }

    /**
     * @return array
     */
    public function getClientSocketList(): array
    {
        return $this->clientSocketList;
    }


    /**
     * @return $this
     * @throws \Exception
     */
    public function connect()
    {
        foreach ($this->serverList as $item) {
            $socketServer = new SocketService();
            $socketServer->setSingleLink($this->singleLink);
            $socketServer->setOnMessage($this->onMessage);
            $socketServer->connect($item['address'])->send($item['message']);
            $socketServer->event = self::$clientEvent->add($socketServer->socket, \Event::READ | \Event::PERSIST, [$socketServer, 'onRead']);
            $this->clientSocketList[intval($socketServer->socket)] = $socketServer->socket;
            $this->relationCallback && call_user_func($this->relationCallback, $socketServer->socket);
        }
        return $this;
    }

    public function listen()
    {
        /**
         * 当"fd池"为空，就会停止loop
         */
        self::$clientEvent->loop();
    }

    /**
     * @param $host
     * @param $port
     * @param $message
     * @param null $onMessage
     * @throws \Exception
     */
    public function singleConnect($host, $port, $message, $onMessage = null)
    {
        $address = $host . ":" . $port;
        $this->serverList = [[
            'address' => $address,
            'message' => $message
        ]];
        $onMessage && $this->onMessage = $onMessage;
        $this->singleLink = true;
        $client = $this->connect();
        $this->listen();
    }
}

