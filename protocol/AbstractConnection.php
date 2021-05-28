<?php
/**
 * Created by PhpStorm.
 * @file   AbstraceProtocol.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/15 下午3:39
 * @desc   AbstraceProtocol.php
 */

namespace protocol;

use common\BaseClass;
use service\WorkerService;

abstract class AbstractConnection extends BaseClass implements ConnectionInterface
{
    protected $socket;
    protected $client;
    protected $remoteAddress;

    /**
     * 只通讯一次
     * @var bool
     */
    public $singleLink = false;


    /**
     * 是否需要握手
     * @var bool
     */
    public $handshake = true;

    /**
     * @var mixed|string
     */
    protected $sendBuffer;

    const READ_BUFFER_SIZE = 65535;


    /**
     * @param mixed|string $sendBuffer
     */
    public function setSendBuffer($sendBuffer): void
    {
        $this->sendBuffer = $sendBuffer;
    }


    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return mixed
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * 打包函数 返回帧处理
     * @param $buffer
     * @return string
     */
    protected function frame($buffer)
    {
        $len = strlen($buffer);
        if ($len <= 125) {
            return "\x81" . chr($len) . $buffer;
        } else if ($len <= 65535) {
            return "\x81" . chr(126) . pack("n", $len) . $buffer;
        } else {
            return "\x81" . char(127) . pack("xxxxN", $len) . $buffer;
        }
    }

    /**
     * 解码 解析数据帧
     * @param $buffer
     * @return string|null
     */
    public function decode($buffer)
    {
        $masks = $data = $decoded = null;
        $len = ord($buffer[1]) & 127;
        if ($len === 126) {
            $masks = substr($buffer, 4, 4);
            $data = substr($buffer, 8);
        } else if ($len === 127) {
            $masks = substr($buffer, 10, 4);
            $data = substr($buffer, 14);
        } else {
            $masks = substr($buffer, 2, 4);
            $data = substr($buffer, 6);
        }
        for ($index = 0; $index < strlen($data); $index++) {
            $decoded .= $data[$index] ^ $masks[$index % 4];
        }
        return $decoded;
    }


    /**
     * 获取请求头
     * @param $req
     * @return array|null[]
     */
    protected function getHeaders($req)
    {
        $r = $h = $o = $key = null;
        if (preg_match("/GET (.*) HTTP/", $req, $match)) {
            $r = $match[1];
        }
        if (preg_match("/Host: (.*)\r\n/", $req, $match)) {
            $h = $match[1];
        }
        if (preg_match("/Origin: (.*)\r\n/", $req, $match)) {
            $o = $match[1];
        }
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $req, $match)) {
            $key = $match[1];
        }
        return [$r, $h, $o, $key];
    }

    /**
     * 请求加密key
     * @param $key
     * @return string
     */
    protected function calcKey($key)
    {
        //基于websocket version 13
        $accept = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        return $accept;
    }


    public function handshake($buffer)
    {
        list($resource, $host, $origin, $key) = $this->getHeaders($buffer);
        $upgrade = "HTTP/1.1 101 Switching Protocol\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "Sec-WebSocket-Accept: " . $this->calcKey($key) . "\r\n\r\n";  //必须以两个回车结尾
        $this->send($upgrade, false);
        return true;
    }


    /**
     * 数据输出
     */
    public function write()
    {
        $len = @fwrite($this->client, $this->sendBuffer, 8192);
        if ($len === strlen($this->sendBuffer)) {
            WorkerService::$globalEvent->del($this->client, \Event::WRITE);
        }
        $this->singleLink && $this->disconnect();
    }

    /**
     * 数据输出
     * @param null $client
     * @param string $buffer
     */
    public function redirectSend($client = null, $buffer = '')
    {
        $buffer = $this->frame($buffer);
        empty($client) && $client = $this->client;
        empty($buffer) && $buffer = $this->sendBuffer;
        $len = fwrite($client, $buffer, 8192);
        if ($len === strlen($buffer)) {
            WorkerService::$globalEvent->del($client, \Event::WRITE);
        }
    }


    /**
     * @param $buffer
     * @return int|string|null
     */
    public function inputCheck($buffer)
    {
        // Receive length.
        $recv_len = \strlen($buffer);
        // We need more data.
        if ($recv_len < 6) {
            return 0;
        }
        // Buffer websocket frame data.
        $firstbyte = \ord($buffer[0]);

        $opcode = $firstbyte & 0xf;
        switch ($opcode) {
            case 0x0: //表示一个延续帧。当Opcode为0时，表示本次数据传输采用了数据分片，当前收到的数据帧为其中一个数据分片。
                $this->info("延续帧");
                return 0;
                break;
            // Blob type.
            case 0x1:  //表示这是一个文本帧（frame）
                $this->info("文本帧");
                return 1;
                break;
            // Arraybuffer type.
            case 0x2: //表示这是一个二进制帧（frame）
                $this->info("二进制帧");
                return 2;
                break;
            // Close package.
            case 0x8: //表示连接断开。
                $this->info("连接断开");
                return 8;
            // Ping package.
            case 0x9: //表示这是一个ping操作。
                $this->info("ping");
                return 9;
                break;
            // Pong package.
            case 0xa: //表示这是一个pong操作。
                $this->info("pong");
                return 'a';
                break;
            // Wrong opcode.
            default :
                $this->info("default");
                return null;
        }
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


}
