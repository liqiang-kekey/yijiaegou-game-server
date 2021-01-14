<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/15
 * Time: 13:56
 */

namespace app\admin\controller;


use app\common\service\AdminBase;

class SysVideo extends AdminBase
{
    /*
     * vip鸡参数配置页面
     * 2020/08/15
     * */
    public function index(){
        if(request()->isPost()){
            $res = $_POST;
           
            db('sys_video')->where('id',1)->update($res)?json_response(1,'修改成功'):json_response(2,'修改失败');
        }
        $data = db('sys_video')->where('id',1)->find();
        $this->assign('data',$data);
        return $this->fetch();
    }
}