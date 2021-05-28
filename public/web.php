<?php
/**
 * Created by PhpStorm.
 * @file   web.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/29 下午1:47
 * @desc   web.php
 */

include_once "../vendor/autoload.php";

use \server\WebServer;


$webService = new WebServer();
$webService->processCount = 1;
$webService->handshake = false;
$webService->singleLink = true;
$webService->address = \service\ConfigService::get('server.web.host');
$webService->port = \service\ConfigService::get('server.web.port');

$webService->start();
