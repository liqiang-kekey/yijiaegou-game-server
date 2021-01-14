<?php

namespace app\admin\controller;

use app\api\model\SysCommissionsharp;
use app\common\service\AdminBase;

class Commissionsharp extends AdminBase
{

    
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        if (request()->isPost()) {
            $model = new Syscommissionsharp();
            $data = $model->limit(1)->find();
            return view()->assign('data', $data);
        }else{
            
        }
    }
}
