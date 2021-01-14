<?php
namespace app\api\controller;

use think\Controller;
/**
 * 三期订单
 */
class Userorder extends Controller{
    
    var $openid ;   //openid
    var $user ;    //用户
    var $pagesize ;//页数
    //初始化
    function _initialize($pagesize = 10){
        if(!request()->isPost()){
            show(0,'请使用POST提交');
        }
        $openid = $this->openid  = input('openid') ?? '';
        $pagesize = $this->pagesize = 10;
        if(!$openid)  show('','请输入OPENID');
        if(!$user = $this->user = db('user')->where('openid',$openid)->find()) show(0,'用户不存在');
    }
    /**
     * 购买小鸡订单
     * @param openid
     */
    function by_chicken_list(){
        //print_r($this->user);
        $out_day = db('sys_chicken_level')->where('class',4)->field('day')->find()['day'];
        $chiname = db('applet_goods')->field('name,money')->find(1);
        $my_chicken_list = db('user_chicken')
            ->alias('c')
            ->join('applet_order o','o.order_sn = c.order_sn','left')
            ->join('user_chicken_email_order e','e.chickend_id = c.id','left')
            ->where(['c.uid' => $this->user['id']])
            ->field('o.id,c.order_sn,e.shipping_order as wldh,c.give_name,c.create_time,c.level,o.pay_money,o.number')
            ->paginate($this->pagesize)->each(function($v,$k) use ($out_day,$chiname){
                $v['out_day']       = $out_day;
                $v['pay_time']      = strtotime($v['create_time']);
                $v['name']          = $chiname['name'];
                $v['falg']          = empty($v['give_name']) ? 0: 1;
                if(!$v['wldh'])
                {
                    $v['wldh'] = '';
                }
                if($v['pay_money']){
                    $v['pay_money'] = number_format(($v['pay_money']/$v['number']),2);
                }else{
                    $v['pay_money'] = '0.00';
                }
                if($v['level'] < 10){
                    $v['status'] = 1;
                }elseif($v['level'] == 10){
                    $v['status'] = 2;
                }elseif($v['level'] == 11){
                    $v['status'] = 3;
                }
            return $v;
        });
        if(!$my_chicken_list->total()) show(0,'暂无购买小鸡订单');
        show(1,'查询成功',$my_chicken_list);
    }

   
    /**
     * 用户做任务获得积分 、鸡蛋明细
     * @param openid
     */
    function get_integral_info(){
        $user_tanks_list = db('user_tanks')
                           ->alias('ut')
                           ->join('sys_tanks t','t.id = ut.t_id','LEFT')
                           ->join('sys_reward r','r.id = ut.prize_id','LEFT')       
                           ->field('if(t.name is null ,"该任务已下线",t.name) tkname,r.name rwname,ut.create_time')
                           ->where(['ut.uid' => $this->user['id']])
                           ->paginate($this->pagesize);
        if(!$user_tanks_list->total()) show(0,'当前暂无获取积分记录');
        show(1,'查询成功',$user_tanks_list);
    }

    /**
     * 获取积分转入小程序信息
     * @param openid
     */
    function get_move_integral_info(){
        $list = db('user_third_party')->where(['uid' => $this->user['id']])->field('name,type,integral,status,create_time')->paginate($this->pagesize);
        if(!$list->total()) show(0,'当前暂无转入小程序明细',$list);
        show(1,'查询成功',$list);
    }
    
    /**
     * 我得信件列表
     */
    function my_order_list(){
        $where = [];
        $on = 'o.give_id = u.id';
        $order = [];
        if(!$class = input('class')){
            //我赠送的
            $where = ['o.uid' => $this->user['id'],'o.status' => ['in',array(2,3)]];
            $on = 'o.uid = u.id';
            $order = ["o.create_time DESC"];
        }elseif($class == 1){
            //我领取的
            $where = ['o.give_id' => $this->user['id'] ,'o.status' => ['in',array(2,3)]];
            $on = 'o.give_id = u.id';
            //$order = [" o.create_time" , "DESC"];
            $order = ["o.accept_time DESC"];
        }elseif($class == 2){
            //我购买
            $list = db('applet_order')
            ->alias('o')
            ->join('user_chicken c','c.order_sn = o.order_sn','left')
            ->field('o.id,if(o.status=1,4,0) status,(o.pay_money/o.number)pay_money,c.create_time')
            ->where(['o.game_user_id' => $this->user['id'],'o.status' => 1 ])
            ->order('id DESC')
            ->select();
            foreach($list as &$v){
                $v['pay_money'] = isset($v['pay_money']) ? sprintf("%.2f",$v['pay_money']) : '0.00';
            }
            if(!$list) show('','暂无数据');
            
            show(1,'查询成功',$list);
        }
          $list = db('user_chicken_order')
                //->fetchSql(true)
                ->alias('o')
                ->join('user u',$on,'LEFT')
                ->join('sys_freetemplate t','t.id = o.template_id','LEFT')
                ->where($where)
                ->field('o.id,t.img_front,t.img_after,o.give_id,o.content,o.status,if(u.name!="",u.name,u.nickname) name,o.create_time,o.accept_time')
                ->order($order)
                ->select();
        if(!$list) show('','暂无数据');
        show(1,'查询成功',$list);
    }
}