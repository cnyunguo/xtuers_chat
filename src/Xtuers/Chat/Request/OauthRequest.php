<?php
/**
 * Created by PhpStorm.
 * User: linroid
 * Date: 14-7-19
 * Time: 上午11:33
 */

namespace Xtuers\Chat\Request;


class OauthRequest extends BaseRequest{

    public function getToken()
    {
        return $this->data->token;
    }
}