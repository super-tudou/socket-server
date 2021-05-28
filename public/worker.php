<?php
/**
 * Created by PhpStorm.
 * @file   worker.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/28 下午7:30
 * @desc   worker.php
 */
include_once "../vendor/autoload.php";

use \server\WorkerServer;
use \protocol\impl\TcpConnection;

$socketService = new WorkerServer();
$socketService->processCount = 10;
$socketService->handshake = false;
$socketService->singleLink = true;
$socketService->address = \service\ConfigService::get('server.worker.host');
$socketService->port = \service\ConfigService::get('server.worker.port');

//$socketService->onConnect = function (TcpConnection $connect) {
////    print_r($connect);
//};
//$socketService->onMessage = function (TcpConnection $connection, $message) {
//    $message = $connection->decode($message);
//    $connection->send($message);
//};

$socketService->start();
