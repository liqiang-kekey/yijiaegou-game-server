<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/10
 * Time: 10:11
 */

namespace app\admin\controller;


use app\common\service\AdminBase;

class Integral extends AdminBase
{
    /*
     * 2020/08/10
     * 积分商城订单
     * */
    public function index(){
        if($_GET['number']){
            $where['o.order_sn'] =  array('like','%'.$_GET['number'].'%');
        }
        //$where['a.default'] = 1;
        $list = db('shop_order')
            //->fetchSql(false)
            ->alias('o')
            ->join('user_mail_address a','o.user_id = a.uid','left')
            ->join('user u','o.user_id = u.id','left')
            ->join('shop_goods g','o.goods_id = g.id','left')
            ->where($where)
            ->group('o.id')
            ->order('o.id','DESC')
            ->field('o.id,u.name,o.pay_type,u.openid,g.name as title,o.price,o.order_sn,o.number,o.create_time,o.type,o.logistics,o.status,a.name as aname,a.mobile,a.province,a.city,a.area,a.address')
            ->paginate(15,false,['query'=>$_GET]);
            //->select();
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        $this->assign('where',$_GET);
        return $this->fetch();
    }

    function data(){
        $list = db('shop_order')
        ->alias('o')
        ->join('user_mail_address a','o.user_id = a.uid','left')
        ->join('user u','o.user_id = u.id','left')
        ->join('shop_goods g','o.goods_id = g.id','left')
        ->where($where)
        ->order('o.id','DESC')
        ->field('o.id,u.name,u.openid,g.name as title,o.price,o.order_sn,o.number,o.create_time,o.type,o.logistics,o.status,a.name as aname,a.mobile,a.province,a.city,a.area,a.address')
        ->paginate(15,false,['query'=>$_GET]);
        show(1,'',$data);
    }
    /*
     * 2020/08/10
     * 积分订单发货
     */
    public function deliver(){
        $id = input('id');
        if(request()->isPost()){
            $integ = input('integ');
            db('shop_order')->where('id',$id)->update(['logistics'=>$integ,'status'=>1])?json_response(1,'发货成功'):json_response(2,'发货失败');
        }
        return $this->fetch();
    }
}