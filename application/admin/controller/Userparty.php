<?php
namespace app\admin\controller;

use app\common\service\AdminBase;
use think\Db;

/**
 * 分账
 */
class Userparty extends AdminBase
{
    public function index()
    {
        $where = [];
        $whereor = [];
        if(!empty($_GET['a'])){
            $where['u.name'] = ['like',"{$_GET['a']}%"];
            $whereor['u.nickname'] = ['like',"{$_GET['a']}%"];
        }
        $list = db('user_separate_accounts')
                ->alias('b')
                ->join('user u', 'b.buy_id = u.id', 'LEFT')
                ->join('user_party p', 'p.member_id = b.member_id', 'LEFT')
                ->field('b.id,u.name,p.openid,p.unionid,p.username,p.avatar as favatar,b.member_id,u.nickname,u.avatar,b.order_sn,b.proportion,b.pay_money,b.settlement_money,b.create_time')
                ->where($where)
                ->whereOr($whereor)
                ->order('b.id', 'DESC')
                ->group('b.id')
                ->paginate(15, false, ['query'=>$_GET]);
        
        return view()->assign([
            'list' => $list,
            'page' => $list->render(),
            'where' => $_GET,
        ]);
    }

    /**
     * 数据导出
     */
    function excel_out(){
        $where = [];
        $whereor = [];
        if(!empty($_GET['a'])){
            $where['u.name'] = ['like',"{$_GET['a']}%"];
            $whereor['u.nickname'] = ['like',"{$_GET['a']}%"];
        }
      
        $list =  db('user_separate_accounts')
        ->fetchSql(false)
        ->alias('b')
        ->join('user u', 'b.buy_id = u.id', 'LEFT')
        ->join('user_party p', 'p.member_id = b.member_id', 'LEFT')
        ->join('applet_order o','o.order_sn = b.order_sn','LEFT')
        ->join('applet_goods g','g.id = o.goods_id','LEFT')
        ->join('user by','o.game_user_id = by.id','LEFT')
        ->field("b.id,if(by.name is null,by.nickname,by.name) by_name,o.create_time o_create_time,g.shipping_money,by.unionid by_unionid,by.openid by_openid,by.avatar by_avatar,g.name goods_name,o.number,u.name,p.openid,p.unionid,p.username,p.avatar as favatar,b.member_id,u.nickname,u.avatar,b.order_sn,b.proportion,b.pay_money,b.settlement_money,b.create_time")
        ->where($where)
        ->whereOr($whereor)
        ->order('b.id', 'DESC')
        ->group('b.id')
        ->select();
        if(!$list) show(0,'暂无数据');
        $data = [
            'id'                    => '编号',
            'order_sn'              => '订单编号',
            'goods_name'            => '商品名称',
            'shipping_money'        => '商品单价',
            'number'                => '购买数量',
            'pay_money'             => '消费总价',
            'by_name'               => '购买人姓名',
            'by_openid'             => '购买人Openid',
            'by_unionid'            => '购买平台编号',
            'by_avatar'             => '购买人头像',
            'o_create_time'         => '购买时间',
            'member_id'             => '上级用户编号',
            'unionid'               => '上级平台编号',
            'username'              => '上级姓名',
            'favatar'               => '上级头像',
            'proportion'            => '分成比例',
            'settlement_money'      => '分成金额',
            'create_time'           => '结算时间',
        ];
        array_to_csv($list,date('Y-m-d His').'分销导出结果',$data);
    }
}
