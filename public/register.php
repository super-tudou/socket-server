<?php
/**
 * Created by PhpStorm.
 * @file   register.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/28 下午3:39
 * @desc   register.php
 */


include_once "../vendor/autoload.php";

use \server\RegisterServer;
use \protocol\impl\TcpConnection;
use \service\ConfigService;

$register = RegisterServer::getInstance();
$register->processCount = 1;  //一个进程
$register->handshake = false;
$register->address = ConfigService::get('server.register.host');
$register->port = ConfigService::get('server.register.port');

$register->onConnect = function (TcpConnection $connection) {
    $connection->send('success');
};

$register->start();
