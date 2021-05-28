<?php
/**
 * 事件,接口类
 * Created by Lane
 * User: lane
 * Date: 16/3/25
 * Time: 下午5:34
 * E-mail: lixuan868686@163.com
 * WebSite: http://www.lanecn.com
 */

namespace event;

/**
 * Interface EventInterface
 * @package Event
 */
interface EventInterface
{
    //读事件
    const EVENT_TYPE_READ = 2;
    //写事件
    const EVENT_TYPE_WRITE = 4;
//    //永久性定时器事件
//    const EVENT_TYPE_TIMER = 4;
//    //一次性定时器事件
//    const EVENT_TYPE_TIMER_ONCE = 8;
//    //信号事件
//    const EVENT_TYPE_SIGNAL = 16;


    /**
     * 添加事件
     * @param $resource resource|int 读写事件中表示socket资源,定时器任务中表示时间(int,秒),信号回调中表示信号(int)
     * @param $type int 类型
     * @param $callable string|array 回调函数
     * @param array $args
     * @return mixed
     */
    public function add($resource, $type, $callable, $args = array());

    /**
     * 删除指定的事件
     * @param $resource resource|int 读写事件中表示socket资源,定时器任务中表示时间(int,秒),信号回调中表示信号(int)
     * @param $type int 类型
     */
    public function del($resource, $type);


    /**
     * 循环事件
     */
    public function loop();
}
