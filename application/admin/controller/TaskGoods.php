<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/15
 * Time: 17:14
 */

namespace app\admin\controller;


use app\common\service\AdminBase;

class TaskGoods extends AdminBase
{
    /*
     * 任务商品列表
     * 2020/08/15
     * */
    public function index(){
        if($_GET['name']){
            $where['name'] = array('like','%'.$_GET['name'].'%');
        }
        $list = db('tanks_shop')
            ->where('isdelete',2)
            ->where($where)
            ->order('asc asc')
            ->paginate(15,false,['query'=>$_GET]);
        $this->assign('list',$list->all());
        $this->assign('page',$list->render());
        $this->assign('where',$_GET);
        return $this->fetch();
    }

    /*
     * 添加商品
     * 2020/08/15
     * */
    public function add_taskgoods(){
        if(request()->isPost()){
            $data = $_POST;
            db('tanks_shop')->insert($data)?json_response(1,'添加成功'):json_response(2,'添加失败');
        }
        return $this->fetch();
    }

    /*
     * 修改商品
     * 2020/08/15
     * */
    public function edit_taskgoods(){
        $id = param_check('id');
        if(request()->isPost()){
            $res = $_POST;
            db('tanks_shop')->where('id',$id)->update($res)?json_response(1,'修改成功'):json_response(2,'修改失败');
        }
        $data = db('tanks_shop')->where('id',$id)->find();
        $this->assign('data',$data);
        return $this->fetch('add_taskgoods');
    }

    /*
     * 删除商品
     * 2020/08/15
     * */
    public function del_taskgoods(){
        $id = param_check('id');
        db('tanks_shop')->where('id',$id)->update(['isdelete'=>1])?json_response(1,'删除成功'):json_response(2,'删除失败');
    }
}