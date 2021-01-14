<?php
namespace app\admin\controller;

use app\common\service\AdminBase;
use app\api\model\SysFreetemplate;

/**
 * 赠送模板设置
 */
class Freetemplate extends AdminBase
{
   var $model;
   var $item;
   var $id;
   
   function _initialize($page = 15){
       $id = $this->id = input('id');
       $model = $this->model = new SysFreetemplate();
       $item = $this->item = $model->paginate($page);
   }

   /**
    * 加载模板设置
    */
    function index(){
        if(request()->isPost()){
            $data = $_POST;
            if(isset($data['id'])){
                //修改
                $this->model->where('id',$data['id'])->update($data) ? show(1,'操作成功') : show(0,'操作失败');
            }
            //增加
            $this->model->save($data) ? show(1,'操作成功') : show(0,'操作失败');
        }
        //print_r($this->item);
        return view()->assign('list',$this->item);
    }

    //新增
    function add(){
        return view('edit');
    }

    //编辑
    function edit(){
        $arr = $_POST;
        if (request()->isPost()) {
            if (!$this->id) {
                if(strlen($arr['content']) > 100){
                    show(0,'超过限制字符');
                }
                //新增
                $this->model->save($arr) ? show(1, '操作成功'): show(0, '操作失败');
            } else {
                //修改
                $this->model->where('id', $this->id)->update($arr) ? show(1, '操作成功'): show(0, '操作失败');
            }
        }
        $data = $this->model->where('id',$this->id)->find();
        return view()->assign('data',$data);
    }

    //删除
    function delete(){
        if(!$this->id) show(0,'编号不能为空');
        $this->model->where('id',$this->id)->delete() ? show(1,'操作成功'):show(0,'操作失败');
    }

    
}
