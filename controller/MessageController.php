<?php
/**
 * Created by PhpStorm.
 * @file   MessageController.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/29 下午3:41
 * @desc   MessageController.php
 */

namespace controller;

use service\GatewayService;
use service\SyncService;

class MessageController extends BaseController
{
    public function send($msg)
    {
        $gatewayService = GatewayService::getInstance();
        $gatewayService->getGatewayList(function ($message) use ($gatewayService, $msg) {
            $gatewayList = json_decode($message, true);
            $gatewayService->sendMessage($gatewayList, $msg);
        });
        return "send success111";
    }


    /**
     * @throws \Exception
     */
    public function gateway()
    {

    }
}
