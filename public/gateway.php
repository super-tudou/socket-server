<?php
/**
 * Created by PhpStorm.
 * @file   gateway.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/28 下午6:05
 * @desc   gateway.php
 */
include_once "../vendor/autoload.php";
use \server\GatewayServer;
use \protocol\impl\TcpConnection;


$socketService = new GatewayServer();
$socketService->processCount = 10;
$socketService->address = \service\ConfigService::get('server.gateway.server.host');
$socketService->port = \service\ConfigService::get('server.gateway.server.port');

//$socketService->onConnect = function (TcpConnection $connect) {
////    print_r($connect);
//};
//$socketService->onMessage = function (TcpConnection $connection, $message) {
//    $message = $connection->decode($message);
//    $connection->send('$message');
//};

$socketService->start();
