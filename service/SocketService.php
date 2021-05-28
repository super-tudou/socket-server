<?php
/**
 * Created by PhpStorm.
 * @file   SocketService.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/28 下午4:36
 * @desc   SocketService.php
 */

namespace service;


class SocketService extends BaseService
{
    public $socket = null;
    public $event;
    public $length = 8129;// 每次读取数据的字节数
    public $singleLink = false; //只通讯一次

    public $onMessage = null;

    /**
     * @param null $onMessage
     */
    public function setOnMessage($onMessage): void
    {
        $this->onMessage = $onMessage;
    }

    /**
     * @param $params
     * @throws \Exception
     */
    protected function __init($params)
    {
        $this->create($params['addr'] ?? '', $params['port'] ?? '');
    }

    /**
     * @param bool $singleLink
     * @return $this
     */
    public function setSingleLink($singleLink = true)
    {
        $this->singleLink = $singleLink;
        return $this;
    }

    public function create($addr, $port)
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket == false) {
            throw new \Exception(socket_strerror(socket_last_error()), socket_last_error());
        }
        $this->socket = $socket;
        // 客户端的端口号默认是随机的，也可以指定某个端口号，如果端口已被占用了会bind失败，继而会使用随机端口
        if ($addr && $port) {
            $res = socket_bind($this->socket, $addr, $port);
            if (!$res) {
                throw new \Exception(socket_strerror(socket_last_error()), socket_last_error());
            }
        }
        // 端口复用，只有当原先的连接处于TIME_WAIT时才有用，同一个端口上只能创建一个活动TCP连接
        socket_get_option($this->socket, SOL_SOCKET, SO_REUSEADDR);
    }


    public function connect($address)
    {
        try {
            list($host, $port) = explode(":", $address);
            $result = @socket_connect($this->socket, $host, $port);
            if ($result == false) {
                throw new \Exception(socket_strerror(socket_last_error()), socket_last_error());
            }
            // 设置非阻塞
            socket_set_nonblock($this->socket);
            return $this;
        }catch (\Exception $exception){
            var_dump($host, $port);
            $this->error($exception->getMessage());
        }
    }

    public function send($message)
    {
        if (is_resource($this->socket)) {
            if (socket_write($this->socket, $message, strlen($message)) !== false) {
                return true;
            }
        } else {
            $this->error("no resource!!!");
        }
        return false;
    }

    public function read($socket, $len = 65535)
    {
        $getMsg = '';
        do {
            $out = socket_read($socket, $len);
            if ($out === false) {
                return false;
            }
            $getMsg .= $out;
            if (strlen($out) < $len) {
                break;
            }
        } while (true);
        return $getMsg;
    }

    public function onRead($fd, $what, $arg)
    {
        $buffer = $this->read($fd, $this->length);
        if ($this->onMessage) {
            call_user_func($this->onMessage, $this->socket, $buffer);
        }
        /**
         * !!! 必须调用 EPOLL_CTL_DEL 来将当前fd(比如7fd)从"fd池"中移除。
         */
        $this->event->free();
        /**
         * 主动关闭连接，否则只能等到manager对象释放继而此对象被释放继而关闭连接。
         */
        $this->singleLink && $this->closeConnect();
    }

    /**
     * close connect
     */
    public function closeConnect()
    {
        @socket_close($this->socket);
    }
}
