<?php
namespace app\admin\controller;

use app\common\service\AdminBase;

//用户裂变数据
class Userfission extends AdminBase
{
    /**
     * 用户总数居
     */
    function index(){
        $list = db('user')->select();
        //print_r($list);
        $data = arr_tree($list,true,0,['parent_key'=>'fid']);
        $ds = $this->for_test($data);
        //print_r($ds);die;
        return view()->assign('list',json_encode($ds));
    }

    public function for_test($data){
        $res = array();
        foreach ($data as $key => $val){
            $val_arr = array();
            $val_arr['title'] = ($val['nickname'] ?? $val['name']) .'-----'.($val['province'] ?? '暂未设置省份').'-----'.(count($val['sub_list']));
            //if($val['sub_list']){
            $val_arr['children'] = $this->for_test($val['sub_list']);
            //}
            $res[] = $val_arr;
        }
        return $res;
    }
}
