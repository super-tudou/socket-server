<?php
/**
 * Created by PhpStorm.
 * @file   AbstractEvent.php
 * @author ζι¦ <jin.li@vhall.com>
 * @date   2021/4/15 δΈε3:46
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
