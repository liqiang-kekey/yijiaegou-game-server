<?php
namespace app\api\controller;
use think\Controller;

/**
 * 赠送模板API
 */
Class Feetemplate extends Controller{
    
    /**
     * Template find查询模板
     * @param class  1 家人 2朋友 3同事
     * @return json
     */
    function template_find(){
        // $class =  input('class');
        // if(!$class) show(0,'缺少类型编号');
        $model = model('SysFreetemplate');
        $list = $model->select();
        $list ? show(1,'查询成功',$list) : show(1,'查询失败',$list);
    }
}