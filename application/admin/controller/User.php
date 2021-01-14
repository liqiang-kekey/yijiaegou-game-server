<?php
/**
 * Created by PhpStorm.
 * User: yaodunyuan
 * Date: 2020/8/7
 * Time: 10:46
 */

namespace app\admin\controller;

use app\common\service\AdminBase;
use app\api\model\User as U;
class User extends AdminBase
{
    var $user_model;
    var $pagesize;
    var $time;
    function _initialize($pagesize = 15){
        $this->user_model = new U();
        $user_model = $this->user_model;
        $this->a = input('a');
        
        if(!$this->a){
            $this->list  = $user_model->order('id','DESC')->paginate($pagesize,false,['query'=>$_GET]);
            
        }else{
            $this->list  = $user_model->where('id',$this->a)->order('id','DESC')->paginate($pagesize,false,['query'=>$_GET]);
            
            if(!$this->list->toArray()['data']){
                //echo $this->a;die;
                $this->list  = $user_model->where(['nickname' => ['like',"%{$this->a}%"]])->order('id','DESC')->paginate($pagesize,false,['query'=>$_GET]);
            }
        }
        $this->id = input('id');
        $this->time = date('Y-m-d H:i:s');
       
    }

     //列表
     function index(){
        $list = $this->list->toArray()['data'];
        foreach($list as $k => $v){
            if($v['fid']){
                $fnickename = $this->user_model->where('id',$v['id'])->field('nickname')->find();
                $list[$k]['fnickname'] = $fnickename['nickname'];
            }
            $childs = $this->user_model->where('fid',$v['id'])->count();
            $list[$k]['childs'] = $childs;
        }
        return  view('index')->assign([
            'list' => $list ?? null,
            'page' => $this->list->render() ?? null,
            'a' => $a,
        ]);
    }

}