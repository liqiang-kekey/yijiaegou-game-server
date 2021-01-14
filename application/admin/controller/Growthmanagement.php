<?php
namespace app\admin\controller;
use app\common\service\AdminBase;

/**
 * 生长管理
 * auth yaodunyuan
 */
class Growthmanagement extends AdminBase{

    /**
     * 生长管理列表
     */
    function index(){
        
        $list = db('sys_chicken_level')->select();
        return view()->assign([
            'list' => $list,
        ]);
    }

    /**
     * 生长管理添加
     */
    function add(){
        return view('edit');
    }

    /**
     * 生长管理编辑
     */
    function edit(){
        $id = input('id');
        if(request()->isPost()){
            $data = $_POST;
            db('sys_chicken_level')->where(['id' => $id ])->update($data) ? show(1,'操作成功') :show(0,'操作失败');
        }
        $item = db('sys_chicken_level')->where('id',$id)->find();
        return view()->assign(['item' => $item]);
    }

    /**
     * 生长管理删除
     */
    function delete(){
        $id = input('id');
        db('sys_chicken_level')->where(['id' => $id ])->delete() ? show(1,'操作成功') :show(0,'操作失败');
    }
}