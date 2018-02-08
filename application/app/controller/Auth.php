<?php
/**
 * Created by PhpStorm.
 * User: jiangchaoren
 * Date: 2018/2/8
 * Time: 下午3:43
 */

namespace app\app\controller;

use app\app\utils\AuthUtils;


class Auth extends BaseController
{

    public function get()
    {
        $method = input('get.method', null);
        $pathname = input('get.pathname', null);
        if ($method == null || $pathname == null) {
            return $this->buildErrorResult("error argument");
        }
        $auth = AuthUtils::getAuthorization($method, $pathname);
        return $auth;
    }
}