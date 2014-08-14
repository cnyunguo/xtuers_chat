<?php
/**
 * Created by PhpStorm.
 * User: linroid
 * Date: 14-7-19
 * Time: 上午11:33
 */

namespace Xtuers\Chat\Request;


class MessageRequest extends UserBaseRequest{

    public function getContent()
    {
        return $this->data->content;
    }

}