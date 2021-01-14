<?php
/**
 * Created by PhpStorm.
 * User: yaodunyuan
 * Date: 2020/8/19
 * Time: 10:20
 */

namespace app\admin\controller;


use app\common\service\AdminBase;

class BcOrder extends AdminBase
{
    /*
     * 买鸡订单
     * 2020/08/19
     * */
    public function index(){
        //$where['o.name'] = ['<>',''];
        if(!empty($_GET['order_sn'])){
            $where['o.order_sn'] = array('like','%'.$_GET['order_sn'].'%');
        }
       
        if(!empty($_GET['nickname'])){
            $where['u.nickname'] = array('like','%'.$_GET['nickname'].'%');
        }
        if(!empty($_GET['status']) or $_GET['status']== 0){
            if($_GET['status'] !="请选择"){
            $where['o.status'] = array('like','%'.$_GET['status'].'%');}
        }
        if(!empty($_GET['time1'])){
            //,
            //
            $where['o.create_time'] = array('between',[strtotime($_GET['time1']),strtotime($_GET['time1'])+86400]);
        }
        //print_r($where);die;
        $data = db('applet_order')
            ->alias('o')
            ->join('user u','o.game_user_id = u.id','left')
            //->fetchSql(true)
            ->where($where)
            ->field('u.nickname,u.openid,o.id,o.pay_time,o.order_sn,o.number,o.status,o.money,o.name,o.mobile,o.province,o.city,o.area,o.address')
            ->order('o.id','DESC')
            //->select();
            ->paginate(15,false,['query'=>$_GET]);
       
        $row = $data->all();
        foreach ($row as $k=>&$v){
            if($v['status']==0){
                $v['status'] = '待支付';
            }else{
                $v['status'] = '已支付';
            }
        }
        $this->assign('list',$row);
        $this->assign('page',$data->render());
        $this->assign('where',$_GET);
        return $this->fetch();
    }

    /**
     * 导出数据
     */
    function excel_out(){
        $where['o.name'] = ['<>',''];
        if(!empty($_GET['order_sn'])){
            $where['o.order_sn'] = array('like','%'.$_GET['order_sn'].'%');
        }
       
        if(!empty($_GET['nickname'])){
            $where['u.nickname'] = array('like','%'.$_GET['nickname'].'%');
        }
        if(!empty($_GET['status']) or $_GET['status']== 0){
            if($_GET['status'] !="请选择"){
            $where['o.status'] = array('like','%'.$_GET['status'].'%');}
        }
        if(!empty($_GET['time1'])){
            $where['o.create_time'] = array('between',[strtotime($_GET['time1']),strtotime($_GET['time1'])+86400]);
        }
        $list = db('applet_order')
            ->alias('o')
            ->join('user u','o.game_user_id = u.id','left')
            ->where($where)
            ->field('o.id,u.nickname,u.openid,o.id,o.pay_time,o.order_sn,o.number,o.status,o.money,o.name,o.mobile,o.province,o.city,o.area,o.address')
            ->order('o.id','DESC')
            ->select();
        
        exportOrderExcel2(date('Y-m-d').'小鸡订单表',['编号','用户名称','昵称','OPENID','订单编号','购买数量','支付金额','支付时间','邮寄地址'],$list);
    
    }
}