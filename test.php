<?php
/**
 * Created by PhpStorm.
 * @file   test.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/28 下午3:44
 * @desc   test.php
 */

use service\GatewayService;

include_once "vendor/autoload.php";

while (true) {
    sleep(1);
    $gatewayService = GatewayService::getInstance()->send(date("Y-m-d H:i:s"));
}
