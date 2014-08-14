<?php
namespace Xtuers\Chat\Request;
/**
 * 面向其他用户消息的基类
 * Created by PhpStorm.
 * User: linroid
 * Date: 14-7-20
 * Time: 上午10:54
 */
class UserBaseRequest extends BaseRequest{
    public function getTo(){
        return $this->data->to;
    }
    public function setFrom($uid){
        $this->data->from = $uid;
    }
    public function getSessionId(){
        if(isset($this->data->session_id)){
            return $this->data->session_id;
        }
        return null;
    }
}