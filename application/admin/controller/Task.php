<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/15
 * Time: 15:02
 */

namespace app\admin\controller;


use app\common\service\AdminBase;

class Task extends AdminBase
{
    /*
     * 任务列表
     * 2020/08/15
     * */
    public function index(){
        if($_GET['name']){
            $where['t.name'] = array('like','%'.$_GET['name'].'%');
        }
        $list = db('sys_tanks')
            ->alias('t')
            ->join('sys_reward r','t.reward_id = r.id')
            ->where('t.isdelete',2)
            ->where($where)
            ->field('r.name,t.id,t.name as title,t.class,t.video,t.answer_points,t.see_time,t.reward_id,t.isenable')
            ->paginate(15,false,['query'=>$_GET]);
        $row = $list->all();
        foreach ($row as $k=>&$v){
            if($v['class']==1){
                $v['class'] = '每日签到';
            }elseif($v['class']==2){
                $v['class'] = '一键喂养';
            }elseif($v['class']==3){
                $v['class'] = '观看视频';
            }elseif($v['class']==4){
                $v['class'] = '逛商品';
            }elseif($v['class']==5){
                $v['class'] = '分享游戏';
            }elseif($v['class']==10){
                $v['class'] = '观看溯源视频';
            }
            if($v['isenable']==1){
                $v['isenable'] = '启用';
            }else{
                $v['isenable'] = '禁用';
            }
        }
        $this->assign('list',$row);
        $this->assign('page',$list->render());
        $this->assign('where',$_GET);
        return $this->fetch();
    }

    /*
     * 添加任务
     * 2020/08/15
     * */
    public function add_task(){
        if(request()->isPost()){
            $data = $_POST;
            $data['create_time'] = date('Y-m-d H:i:s',time());
            db('sys_tanks')->insert($data)?json_response(1,'添加成功'):json_response(2,'添加失败');
        }
        $row = db('sys_reward')->field('id,name')->select();
        $this->assign('list',$row);
        return $this->fetch();
    }

    /*
     * 修改任务
     * 2020/08/15
     * */
    public function edit_task(){
        $id = param_check('id');
        if(request()->isPost()){
            $res = $_POST;
            db('sys_tanks')->where('id',$id)->update($res)?json_response(1,'修改成功'):json_response(2,'修改失败');
        }
        $row = db('sys_reward')->field('id,name')->select();
        $this->assign('list',$row);
        $data = db('sys_tanks')->where('id',$id)->find();
        $this->assign('data',$data);
        return $this->fetch('add_task');
    }

    /*
     * 删除任务
     * 2020/08/15
     * */
    public function del_task(){
        $id = param_check('id');
        db('sys_tanks')->where('id',$id)->delete()?json_response(1,'修改成功'):json_response(2,'修改失败');
    }
}