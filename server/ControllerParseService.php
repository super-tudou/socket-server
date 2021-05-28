<?php
/**
 * Created by PhpStorm.
 * @file   ControllerParseService.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/29 下午4:40
 * @desc   ControllerParseService.php
 */

namespace server;


use controller\MessageController;
use service\BaseService;

class ControllerParseService extends BaseService
{

    private function loadController($controller)
    {
        $controller = "\controller\\" . ucfirst($controller) . "Controller";
        return new $controller;
    }

    public function Parse($uir)
    {
        list($model, $controller, $action) = explode("/", ltrim($uir, "/"));
        $controller = $this->loadController($controller);
        list($action, $params) = explode("?", $action);
        return $controller->$action($params);
    }
}
