<?php
/**
 * Created by PhpStorm.
 * User: linroid
 * Date: 14-7-19
 * Time: 上午11:34
 */

namespace Xtuers\Chat\Request;


class BaseRequest{
    public  $type;
    public $data;
    function __construct($json)
    {
        if(!is_string($json)){
            $this->data = $json;
        }else{
            $requestObj = json_decode($json);
            $this->type = $requestObj->type;
            $this->data = $requestObj->data;
        }
    }
    public function isOAuth(){
        return $this->type == 'oauth';
    }
    public function validType()
    {
        return in_array($this->type, ['typing', 'oauth', 'message', 'view']);
    }

    public function toJson(){
        return json_encode($this, JSON_FORCE_OBJECT);
    }

    public function getType()
    {
        return $this->type;
    }
    public function getTypedRequest(){
        $request = null;
        switch($this->type){
            case 'oauth':
                $request =  new OauthRequest($this->data);
                break;
            case 'message':
                $request = new MessageRequest($this->data);
                break;
            case 'typing':
                $request = new TypingRequest($this->data);
                break;
            case 'view':
                $request = new ViewRequest($this->data);
                break;
            default :
                $request = new ErrorRequest('404', '未知的请求类型:'.$this->type);
                $this->type = 'unknown';
                break;
        }
        $request->type = $this->type;
        return $request;
//        $class = (studly_case($this->type).'Message');
//        return new $class($this->data);
    }

    function __unset($name)
    {
        return null;
    }
    protected function get($name){
        if(isset($this->data->$name)){
            return $this->data->$name;
        }
        return null;
    }

    function __get($name)
    {
        return null;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}