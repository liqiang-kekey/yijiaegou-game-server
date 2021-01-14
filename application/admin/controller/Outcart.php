<?php
namespace app\admin\controller;

use app\common\service\AdminBase;

/**
 * 出栏订单
 */
class Outcart extends AdminBase
{
    function index(){
        if(!empty($_GET['name'])){
            $where = ['u.name' => ['like',"{$_GET['name']}%"]];
        }
        if(!empty($_GET['nickname'])){
            $where = ['u.nickname' => ['like',"{$_GET['nickname']}%"]];
        }
        if(!empty($_GET['bytime'])){
            $where = ['c.create_time' => ['like',"{$_GET['bytime']}%"]];
        }
        if(!empty($_GET['level'])){
            $ts = $_GET['level'] -1;
            $where = ['o.shipping_status' => ['eq',"{$ts}"]];
        }
        $list = db('user_chicken_email_order')
                ->alias('o')
                ->join('user u ','u.id = o.uid','left')
                ->join('user_chicken c','c.id = o.chickend_id','LEFT')
                ->join('user_chicken_order cor','cor.number = c.number  and cor.give_id != 0 and is_accept = 1','LEFT')
                ->join('applet_order aor','aor.order_sn = c.order_sn','LEFT')
                ->field('o.id,c.uid as chickenduserid,u.nickname,u.name,u.avatar,o.isout,c.order_sn,c.source,c.number,c.isvip,c.create_time,c.level,c.outegg,c.identifier,c.grade,o.emailname,o.emailmobile,o.province,o.city,o.area,o.address,o.pay_money,o.shipping_status,o.shipping_order,o.shipping_time,o.out_time,cor.uid as acceptuserid,cor.give_id,(select if(name!="",name,nickname) name from raisingchickens_user where id=cor.give_id) as givename,cor.create_time givetime,(select if(name!="",name,nickname) name from raisingchickens_user where id=cor.uid) as accpname,cor.accept_time,aor.pay_money as payMoney,aor.game_user_id,aor.name as payuname, FROM_UNIXTIME(aor.pay_time,"%Y-%m-%d %H:%i:%s") as pay_time')
                ->where($where)
                ->paginate(15,false,['query'=>$_GET]);
        
        return view()->assign([
            'list' => $list->all(),
            'count' => $list->total(),
            'page' => $list->render(),
            'where' => $_GET,
        ]);
    }

    /**
     * 填写订单
     */
    function deliver(){
        $id = input('id');
        $order = db('user_chicken_email_order')->where('id',$id)->find();
        if (!$order) show(0,'出栏订单不存在');
        if (request()->isGet()) {
            return view()->assign('id',$id);
        }else{
            $order_sn = input('shipping_order');
            if(!$order) show(0,'缺少配送单号');
            if($order['shipping_status'] == 1) show('0','无需重复填写配送单号');
            //修改配送单号
            db('user_chicken_email_order')->where('id',$id)->update([
                'shipping_status' => 1,
                'shipping_order' => $order_sn,
                'shipping_time' => time(),
            ]);
            //设置已发货
            db('user_chicken')->where('id',$order['chickend_id'])->update([
                'level' => 11,
            ]);
            show(1,'操作成功');
        }
    }

    /**
     * 已收货
     */
    function harvest(){
        $id = input('id');
        $order = db('user_chicken_email_order')->where(['id' => $id,'shipping_status' => 1])->find();
        if(!$order) show(0,'订单不存在');
        //修改配送单号
        db('user_chicken_email_order')->where('id',$id)->update([
            'shipping_status' => 2,
        ]);
        db('user_chicken')->where('id',$order['chickend_id'])->update([
            'level' => 12,
        ]);
        show(1,'收货成功');
    }
}