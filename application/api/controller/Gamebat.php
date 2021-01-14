<?php 
namespace app\api\controller;
use think\Controller;
use think\Db;

class Gamebat extends Controller{
    
    function bat(){
        set_time_limit(0);
        ini_set("max_execution_time", 1800); // s 30 分钟
        ini_set("memory_limit", 1048576000); // Byte 1000 兆，即 1G
        //每天23:59更新
        $list = db('user_chicken')->select();
        foreach($list as $v){
            /**饥饿值 */
            if(date_diff(date_create(explode(' ', $v['last_feed_time'])[0]), date_create(date('Y-m-d')))->d > 1){
                if($v['hunger'] + 10 >= 100){
                     //修改饥饿值
                     db('user_chicken')->where(['id' => $v['id']])->update(['hunger' => 100]);
                }else{
                    //修改饥饿值
                    db('user_chicken')->where(['id' => $v['id']])->setInc('hunger', 10);
                }
            }
            //活跃度
            if(!$activity_list = db('user_chicken_having')->where(['c_id' => $v['id'],'class' => 3,'date'=> date('Y-m-d') ])->find()){
                if($v['activity'] -10 <= 0 ){
                    db('user_chicken')->where(['id' => $v['id']])->update(['activity' => 0]);
                }else{
                    db('user_chicken')->where(['id' => $v['id']])->setDec('activity',10);
                }
            }
            //健康值
            if(!$health_list = db('user_chicken_having')->where(['c_id' => $v['id'],'class' => 4,'date'=> date('Y-m-d') ])->find()){
                if($v['health'] -10 <= 0 ){
                    db('user_chicken')->where(['id' => $v['id']])->update(['health' => 0]);
                }else{
                    
                    db('user_chicken')->where(['id' => $v['id']])->setDec('health',10);
                }
            }
            
        }
        echo '执行完成'.date('Y-m-d H:i:s');
    }

    
}
