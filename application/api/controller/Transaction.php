<?php
namespace app\api\controller;
use app\api\controller\Base;
use app\api\model\SysChicken;
/**
 * 用户交易
 */
class Transaction extends Base{
    
    /**
    * 宠物购买
    */
    function buy_chicken(){
        //获取系统配置参数 源于BASE
        $system = $this->system;
        //获取用户
        $user = $this->system['user'];
        //验证
        if(!$this->openid) show(0,'缺少OPENID');
        if(!$user) show(0,'请先授权');
        //当前系统设置的宠物
        $sys_chicken_model = new SysChicken(); 
        $sys_chicken = $sys_chicken_model->find(1);
        $source = input('source'); //1免费，2购买，3转赠
        $isvip = input('isvip'); //是否VIP
        if($isvip){
            $number = create_number('chicken');//创建编号
        }
        $name = input('name');  //名称
        $is_save = input('is_save'); //是否放置存储箱
    }


    /**
    * 积分转入三方平台
    */
    


    /**
    * 消费券转入三方平台
    */


    /**
    * 积分转入
    */

}