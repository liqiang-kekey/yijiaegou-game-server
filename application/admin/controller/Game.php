<?php
namespace  app\admin\controller;

/**
 * autho:yaodunyuan
 */

use app\common\service\AdminBase;
use app\api\model\SysGame;
class Game extends AdminBase
{
 
    var $sysgame_model;
    var $pagesize;
    var $time;
    function _initialize($pagesize = 15){
        $sysgame_model = $this->sysgame_model = new SysGame();
        $this->name = input('a');
        if(!$this->name){
            $this->list  = $sysgame_model->where(['isdelete' => 2])->paginate($pagesize);
        }else{
            $this->list  = $sysgame_model->where(['isdelete' => 2,'name' =>['like',"%".$this->name."%"]])->paginate($pagesize);
        }
        
        $this->id = input('id');
        $this->time = date('Y-m-d H:i:s');
       
    }


    //列表
    function index(){
        return  view('index')->assign([
            'list' => $this->list ?? null,
            'page' => $this->list->render() ?? null,
            'name' => $this->name,
        ]);
    }

    function details(){
        if(!$id = $this->id){
            //新增
            return view();
        }else{
            //编辑
            $item = $this->sysgame_model->getById($id);
            //print_r($item);
            return view()->assign('item',$item);
        }
    }

    /**
     * 编辑
     */
    function edit(){
        if(!$id = $this->id) show(0,'缺少参数');
        // $arr['id'] = $id;
        $arr['name'] = input('name');
        $arr['notice'] = input('notice');
        $arr['isenable'] = input('isenable');
        $arr['update_time'] = $this->time;
        $arr['alarm_line'] = input('alarm_line');
        $arr['is_vip']  = input('is_vip');
        $arr['logostarttime']  = input('logostarttime');
        $arr['logoendtime']  = input('logoendtime');
        if($arr['isenable'] == 1){
            $ids  = $this->sysgame_model->column('id');
            $this->sysgame_model->where('id','in',$ids)->update(['isenable' => 2]);
        }
        if($arr['is_vip']){
            db('user')->where("1 = 1")->update(['isswitch' => 1]);
        }else{
            db('user')->where("1 = 1")->update(['isswitch' => 0]);
        }
        if($this->sysgame_model->where('id',$id)->update($arr) > 0) show(1,'操作成功');
        show('','操作失败');
    }

    /**
     * 启用
     */
    function delete(){
        if(!$id = $this->id){
            show(0,'缺少参数');
        }else{
           $this->sysgame_model->where('id',$id)->update(['isdelete' => 1]);
           show(1,'操作成功');
        }
    }
}