<?php
namespace app\admin\controller;

use app\common\service\AdminBase;

class SysAnswer extends AdminBase
{
    function index(){
        if(request()->isPost()){
            $data = $_POST;
            $answer_class_name = db('answer_class')->where('id',$data['answer_class_id'])->field('name')->find()['name'];
            $data['answer_class_name'] =  $answer_class_name;
            if(db('sys_answer')->where('id',1)->update($data)) show('1','操作成功');
            show(0,'操作失败');
        }else{
            $data = db('sys_answer')->where('id',1)->find();
            $class = db('answer_class')->select();
            return view()->assign([
                'class' => $class,
                'data'  => $data,
                'week'  => [
                    '周一',
                    '周二',
                    '周三',
                    '周四',
                    '周五',
                    '周六',
                    '周日',
                ]
                ]);
        }
    }
}