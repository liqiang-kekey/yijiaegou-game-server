<?php
namespace app\admin\controller;

use app\common\service\AdminBase;

class Usersharp extends AdminBase
{
    function index(){
        $_reward_list = db('sys_reward')->where(['type' => 1])->select();
        $list = db('sys_sharp')->select();
        return view()->assign([
            'list' => $list,
            'reward' => $_reward_list
        ]);
    }

    function edit(){
        $ids = $_POST['id'];
        $reward_ids = $_POST['reward_id'];
        $limit_count = $_POST['limit_count'];
        $arr;
        foreach($ids as $k=>$v){
            if ($v == 1) {
                db('sys_sharp')->where('id', $v)->update([
                    'reward_id' => $reward_ids[$k],
                    'limit_count' => $limit_count
                ]);
            }else{
                db('sys_sharp')->where('id', $v)->update([
                    'reward_id' => $reward_ids[$k]
                ]);
            }
        }
        show(1,'操作成功',$ids);
    }
}