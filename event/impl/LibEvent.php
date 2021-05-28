<?php
/**
 * Created by PhpStorm.
 * @file   Event.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/15 下午3:44
 * @desc   Event.php
 */

namespace event\impl;

use event\AbstractEvent;

/**
 * Class LibEvent
 * @package Event
 */
class LibEvent extends AbstractEvent
{
    /**
     * @param $params
     */
    public function __init($params)
    {
        if (!extension_loaded("event")) {
            $this->error("event extension is require!");
        }
        $this->eventBase = new \EventBase();
    }

    /**
     * @param int|resource $resource
     * @param int $type
     * @param array|string $callable
     * @param array $args
     * @return mixed
     */
    public function add($resource, $type, $callable, $args = array())
    {
        $event = @ new \Event($this->eventBase, $resource, $type | \Event::PERSIST, $callable, $resource);
        if (!$event || !$event->add()) {
            $this->info("add event fail!");
            return false;
        }
        $fdKey = intval($resource);
        $this->allEvents[$fdKey][$type] = $event;
        return $event;
    }


    /**
     * @param int|resource $resource
     * @param int $type
     * @return mixed
     */
    public function del($resource, $type)
    {
        $fdKey = intval($resource);
        if (isset($this->allEvents[$fdKey][$type])) {
            $this->allEvents[$fdKey][$type]->del();
            unset($this->allEvents[$fdKey][$type]);
        }
    }

    /**
     * @return mixed
     */
    public function loop()
    {
        $this->eventBase->loop();
    }
}
