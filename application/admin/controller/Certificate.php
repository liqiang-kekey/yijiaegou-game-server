<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/7
 * Time: 15:28
 */

namespace app\admin\controller;


use app\common\service\AdminBase;

class Certificate extends AdminBase
{
    /*
     * 证书列表
     * 2020/08/07
     */
    public function index(){
        $where = [];
        $title = input('title');
        if(!empty($title)){
            $where['title'] = array('like','%'.$title.'%');
        }
        $list = db('sys_certificate')->where($where)->paginate(15,false,['query'=>$_GET]);
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
        return $this->fetch();
    }

    /*
     * 添加证书
     * 2020/08/07
     * */
    public function add_certificate(){
        if(request()->isPost()){
            $data = $_POST;
            db('sys_certificate')->insert($data)?json_response(1,'添加成功'):json_response(2,'添加失败');
        }
        return $this->fetch();
    }

    /*
     * 修改证书
     * */
    public function edit_certificate(){
        $id = param_check('id');
        if(request()->isPost()){
            $data = $_POST;
            db('sys_certificate')->where('id',$id)->update($data)?json_response(1,'修改成功'):json_response(2,'修改失败');
        }
        $data = db('sys_certificate')->where('id',$id)->find();
        $this->assign('data',$data);
        return $this->fetch('add_certificate');
    }

    /*
     * 删除证书
     * */
    public function del_certificate(){
        $id = param_check('id');
        db('sys_certificate')->where('id',$id)->delete()?json_response(1,'删除成功'):json_response(2,'删除失败');
    }
}