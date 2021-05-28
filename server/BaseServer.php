<?php
/**
 * Created by PhpStorm.
 * @file   BaseServer.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/28 下午2:48
 * @desc   BaseServer.php
 */

namespace server;


use service\WorkerService;

/**
 * 基础服务
 * Class BaseServer
 * @package server
 */
abstract class BaseServer extends WorkerService
{
    /**
     * 初始化服务配置
     */
    abstract protected function __initConfig();
}
