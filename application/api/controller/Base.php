<?php
namespace app\api\controller;
use think\{Controller,Db};
use app\api\model\{SysChickend,User,SysGame,SysSign,SysTanks};

class Base extends Controller{
    var $user,
        $system,
        $openid,
        $game_model,  //游戏模式
        $game_sign,  //游戏签到配置
        $game_tanks, //游戏任务
        $redis;   //redis
    function _initialize(){
        $openid = $this->openid = input('openid');
        if(!$openid) show(0,'缺少OPENID');
        //用户
        $user = $this->user = model('user')->where(['openid' => $openid]);
       if(!$user) show(0,'用户尚未注册,请先授权');
        //reids
        $redis = $this->redis = redis_instance();
        //宠物
        $SysChickend = new SysChickend();
        $user_chickends = $SysChickend->where(['uid' => $user['id']])->select();
        //游戏基本配置
        $SysGame = new SysGame();
        $game_model = $SysGame->where(['isenable' => 1])->find();
        //游戏签到配置(普通模式和连签模式)
        $SysSign = new SysSign();
        $game_sign =  $SysSign->where(['isenable' => 1])->select();
        //任务配置
        $SysTanks = new SysTanks();
        $game_tanks = $SysTanks->where(['isenable' => 1])->select();
        $system = $this->system = [
            'user' => $user,
            'redis' => redis_instance(),
            'user_chickends' => $user_chickends,
            'game_model' => $game_model,
            'game_sign' => $game_sign,
            'game_tanks' => $game_tanks
        ];
    }
}