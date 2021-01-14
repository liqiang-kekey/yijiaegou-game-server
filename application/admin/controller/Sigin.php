<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/8
 * Time: 15:09
 */

namespace app\admin\controller;


use app\common\service\AdminBase;

class Sigin extends AdminBase
{
    /*
     * 签到配置
     * 2020/08/08
     * */
    public function index(){
        if($_GET['name']){
            $where['s.rulename'] = array('like','%'.$_GET['name'].'%');
        }
        $list = db('sys_sign')
            ->alias('s')
            ->join('sys_reward r','s.reward_id = r.id')
            ->where($where)
            ->field('s.id,s.rulename,s.ruleday,s.isenable,r.name')
            ->paginate(15,false,['query'=>$_GET]);
        $data = $list->all();
        foreach ($data as $k=>&$v){
            if($v['isenable']==1){
                $v['isenable'] = '启用';
            }else{
                $v['isenable'] = '禁用';
            }
        }
        $this->assign('list',$data);
        $this->assign('page',$list->render());
        $this->assign('where',$_GET);
        return $this->fetch();
    }

    /*
     * 添加签到配置
     * 2020/08/08
     * */
    public function add_sigin(){
        if(request()->isPost()){
            $data = $_POST;
            $data['create_time'] = date('Y-m-d H:i:s',time());
            db('sys_sign')->insert($data)?json_response(1,'添加成功'):json_response(2,'添加失败');
        }
        $list = db('sys_reward')->field('name,id')->select();
        $this->assign('list',$list);
        return $this->fetch();
    }

    /*
     * 修改签到配置
     * 2020/08/08
     * */
    public function edit_sigin(){
        $id = param_check('id');
        if(request()->isPost()){
            $data = $_POST;
            $data['update_time'] = date('Y-m-d H:i:s',time());
            db('sys_sign')->where('id',$id)->update($data)?json_response(1,'编辑成功'):json_response(2,'编辑失败');
        }
        $list = db('sys_reward')->field('name,id')->select();
        $data = db('sys_sign')->where('id',$id)->find();
        $this->assign('list',$list);
        $this->assign('data',$data);
        return $this->fetch('add_sigin');
    }

    /*
     * 删除签到配置
     * 2020/08/08
     * */
    public function del_sigin(){
        $id = param_check('id');
        db('sys_sign')->where('id',$id)->delete()?json_response(1,'删除成功'):json_response(2,'删除失败');
    }
}