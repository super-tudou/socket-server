<?php
/**
 * Created by PhpStorm.
 * @file   TcpProtocol.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/15 下午3:36
 * @desc   TcpProtocol.php
 */

namespace protocol\impl;

use event\EventInterface;
use protocol\AbstractConnection;
use service\WorkerService;

/**
 * Class TcpConnection
 * @package protocol\impl
 */
class TcpConnection extends AbstractConnection
{
    public static $connectPools;
    public $onMessage;


    /**
     * @param $params
     */
    protected function __init($params)
    {
        if (!isset($params['socket'])) {
            $this->error("tcp connect error, socket is empty");
        }
        if (!isset($params['remote_address'])) {
            $this->error("tcp connect error, remote_address is empty");
        }
        $this->client = $params['client'];
        $this->socket = $params['socket'];
        $this->remoteAddress = $params['remote_address'];
        $this->setProcessId(getmypid());
    }

    /**
     * @return false|mixed|string
     */
    public function read()
    {
        return $buffer = @fread($this->client, self::READ_BUFFER_SIZE);
    }

    /**
     * @param mixed $buffer
     * @param bool $isEncode
     * @param string $client
     */
    public function send($buffer, $isEncode = false, $client = '')
    {
        if ($isEncode) {
            $buffer = $this->frame($buffer);
        }

        empty($client) && $client = $this->client;
        $this->sendBuffer = $buffer;
        $this->info("send message to [" . intval($client) . "] msg:" . $buffer);
        WorkerService::$globalEvent->add($client, \Event::WRITE, [$this, 'write']);
    }

    /**
     * @param string $data
     * @return mixed
     */
    public function close($data = '')
    {
        $this->info("close socket client");
        @fclose($this->client);
    }

    /**
     * @return mixed
     */
    public function getClientAddress()
    {
        return $this->remoteAddress;
    }

    /**
     * 处理连接
     * @return mixed
     */
    public function connect()
    {
        $fdKey = (int)$this->client;
        if (!isset(self::$connectPools[$fdKey])) {
            self::$connectPools[$fdKey] = [
                'handshake' => false,
                'fd' => $this->client,
            ];
        }
        $buffer = $this->read();
        if ($buffer === '' || $buffer === false) {
            $this->disconnect();
            return;
        }
        if (false === self::$connectPools[(int)$this->client]['handshake'] && $this->handshake) {
            $this->info("handshake messages send");
            self::$connectPools[(int)$this->client]['handshake'] = $this->handshake($buffer);
        } else {
            $this->info("received messages:{$buffer}");
            if ($this->onMessage) {
                try {
                    // Decode request buffer before Emitting onMessage callback.
                    \call_user_func($this->onMessage, $this, $buffer);
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                    exit(250);
                } catch (\Error $e) {
                    $this->error($e->getMessage());
                    exit(250);
                }
            }else{
                $this->info("no on message callback set");
            }
        }
    }

    /**
     * 关闭soket 链接
     * @return mixed
     */
    public function disconnect()
    {
        $fdKey = (int)$this->client;
        $this->info("disconnect socket connect[{$fdKey}]");
        if (isset(self::$connectPools[$fdKey])) {
            unset(self::$connectPools[$fdKey]);
            WorkerService::$globalEvent->del($this->client, \Event::READ);
            WorkerService::$globalEvent->del($this->client, \Event::WRITE);
            $this->close();
        }
    }
}
