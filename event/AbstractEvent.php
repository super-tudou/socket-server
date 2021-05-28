<?php
/**
 * Created by PhpStorm.
 * @file   AbstractEvent.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/15 下午3:46
 * @desc   AbstractEvent.php
 */

namespace event;


use common\BaseClass;
use component\LogTrait;

abstract class AbstractEvent extends BaseClass implements EventInterface
{
    use LogTrait;

    /**
     * @var
     */
    protected $eventBase;

    protected $allEvents = [];

}
