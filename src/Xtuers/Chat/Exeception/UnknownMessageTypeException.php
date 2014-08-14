<?php
/**
 * Created by PhpStorm.
 * User: linroid
 * Date: 14-7-19
 * Time: 上午11:22
 */

namespace Xtuers\Chat\Exception;


class UnknownMessageTypeException extends \Exception{

    function __construct($msg)
    {
        $this->message = $msg;
    }
}