<?php
/**
 * Created by PhpStorm.
 * @file   ProtocolInterface.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/15 下午3:36
 * @desc   ProtocolInterface.php
 */

namespace protocol;


interface  ConnectionInterface
{
//    /**
//     * 构造函数
//     * @param $socket resource 由stream_socket_accept()返回
//     * @param $clientAddress string 由stream_socket_accept()的第三个参数$peerName
//     * @param $applicationProtocol string 应用层协议, 默认为空
//     */
//    public function __construct($socket, $clientAddress, $applicationProtocol = '');

    /**
     * 读取数据
     * @return mixed
     */
    public function read();

    /**
     * 发送数据
     * @param $buffer mixed 待发送的数据
     * @param $isEncode bool 发送前是否根据应用层协议转码
     */
    public function send($buffer, $isEncode = true);

    /**
     * 关闭客户端链接
     * @param $data string 关闭链接前发送的消息
     */
    public function close($data = '');

    /**
     * 获取客户端地址
     * @return array|int 成功返回array[0]是ip,array[1]是端口. 失败返回false
     */
    public function getClientAddress();

    /**
     * 握手处理
     * @param $buffer
     * @return mixed
     */
    public function handshake($buffer);

    /**
     * 处理连接
     * @return mixed
     */
    public function connect();

    /**
     * 关闭soket 链接
     * @return mixed
     */
    public function disconnect();

}
