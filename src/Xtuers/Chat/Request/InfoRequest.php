<?php
/**
 * Created by PhpStorm.
 * User: linroid
 * Date: 14-7-22
 * Time: 上午10:16
 */

namespace Xtuers\Chat\Request;


class InfoRequest extends BaseRequest {
    function __construct($code, $content)
    {
        $this->type = 'info';
        $this->data = [
            'content'=>$content
        ];
    }
} 