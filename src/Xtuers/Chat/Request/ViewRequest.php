<?php
/**
 * Created by PhpStorm.
 * User: linroid
 * Date: 14-7-23
 * Time: 上午10:14
 */

namespace Xtuers\Chat\Request;


class ViewRequest extends BaseRequest{

    public function getSessionId(){
        return $this->get('session_id');
    }
}