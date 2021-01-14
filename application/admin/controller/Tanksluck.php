<?php
/**
 * Created by PhpStorm.
 * User: yaodunyuan
 * Date: 2020/8/7
 * Time: 10:46
 */

namespace app\admin\controller;


use app\common\service\AdminBase;

class Tanksluck extends AdminBase
{
    /*
     * 2020/08/07
     * 任务奖励列表
     * */
    public function index(){
        $where = [];
        $title = input('title');
        if(!empty($title)){
            $where['title'] = array('like','%'.$title.'%');
        }

        $list = db('sys_reward')->where($where)->paginate(15,false,['query'=>$_GET]);
        $this->assign('list',$list->all());
        $this->assign('page',$list->render());
        $this->assign('where',$_GET);
        return $this->fetch();
    }

    /*
     * 2020/08/07
     * 任务奖励添加
     * */
    public function add(){
        if(request()->isPost()){
            $data = $_POST;
            db('sys_reward')->insert($data)?json_response(1,'添加成功'):json_response(2,'添加失败');
        }
        return $this->fetch('edit');
    }

    /*
     * 2020/08/07
     * 任务奖励编辑
     * */
    public function edit(){
        $id = param_check('id');
        if(request()->isPost()){
            $data = $_POST;
            db('sys_reward')->where('id',$id)->update($data)?json_response(1,'修改成功'):json_response(2,'修改失败');
        }
        $data = db('sys_reward')->where('id',$id)->find();
        $this->assign('data',$data);
        return $this->fetch('edit');
    }

    /*
     * 任务奖励删除
     * 2020/08/07
     * */
    public function delete(){
        $id = param_check('id');
        db('sys_reward')->where('id',$id)->delete()?json_response(1,'删除成功'):json_response(2,'删除失败');
    }
}