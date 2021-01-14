<?php


namespace app\api\controller;


class Common
{
    /**
     * 获取用户ID
     * @return mixed
     */
    protected function get_user_id() {
        $openid = param_check('openid');
        $user_id = db('user')->where('openid', $openid)->value('id');
        if(empty($user_id)) json_response(0, '用户不存在');
        return $user_id;
    }
}