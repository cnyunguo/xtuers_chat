<?php
/**
 * Created by PhpStorm.
 * User: linroid
 * Date: 14-7-20
 * Time: 上午11:01
 */

namespace Xtuers\Chat\Request;


class AccountRequest extends BaseRequest{

    function __construct()
    {
        $this->type = 'account';
    }

    public function setAccount($user){
        $this->data = $user;
    }

} 