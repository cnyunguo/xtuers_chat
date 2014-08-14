<?php
/**
 * Created by PhpStorm.
 * User: linroid
 * Date: 14-7-20
 * Time: 下午8:54
 */

namespace Xtuers\Chat\Request;


class ErrorRequest extends BaseRequest{

    function __construct($code, $content)
    {
        $this->type = 'error';
        $this->data = [
            'code'=>$code,
            'content'=>$content
        ];
    }
}