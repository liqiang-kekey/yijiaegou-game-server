<?php


namespace app\applet\controller;

// 公共继承模块
class Base
{
    protected $game_user_id; // 小游戏用户ID
    protected $openid; // openid

    /**
     * 获取用户ID
     * @param bool $check_user
     * @return mixed
     * @date 2020/8/12 18:06
     */
    protected function get_user_id($check_user=false) {
        $openid    = param_check('openid');
        $user_info = db('applet_user')->where('openid', $openid)->field('id, unionid')->find();
        if(empty($user_info)) json_response(0, '用户不存在');
        if($check_user) {
            $this->game_user_id = db('user')->where('unionid', $user_info['unionid'])->value('id');
            if(empty($this->game_user_id)) json_response(0, '小游戏用户不存在');
        }
        $this->openid = $openid;
        return $user_info['id'];
    }


}