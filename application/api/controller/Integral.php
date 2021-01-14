<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/8
 * Time: 17:08
 */

namespace app\api\controller;

use think\Controller;
use app\api\model\User;
use think\Db;
use app\api\model\Shop;

class Integral extends Controller
{
    /*
     * 积分商城列表
     * 2020/08/08
     * */
    public function index()
    {
        $page = input('page') ?? 1;//页数
        $page = $page - 1;
        $goods = db('shop_goods')->where('shelves',1)->order('sort asc,id asc')->limit($page, 10)->field('id,name,thumb,sale,price,pay_type,type')->select();
        if ($goods) {
            json_response(1, '获取成功', $goods);
        } else {
            json_response(0, '暂无商品');
        }
    }

    /*
     * 生成订单
     * 2020/08/08
     * */
    public function add_order()
    {
        $openid = param_check('openid');//用户openid
        $goods_id = param_check('goods_id');//商品id
        $userid = db('user')->where('openid', $openid)->value('id');
        $score = db('user')->where('openid', $openid)->field('integral,egg')->find();
        $goods = db('shop_goods')->where('id', $goods_id)->field('name,type,stock,pay_type,price')->find();
        $user_rows = db('user')->where('openid', $openid)->find();//用户id
        $user = new User();
        $shop = new Shop();
        if (!$userid) {
            show(0, '暂无用户');
        }
        if (!$goods) {
            show('', '暂无商品');
        }

        Db::startTrans();
        try {
            //如果还有库存
            if ($goods['stock']>0) {
                //如果是优惠券 订单状态直接已完成
                if ($goods['type']==1) {
                    $data = [
                    'order_sn' =>create_number('order'),//订单号
                    'user_id' => $userid,//用户id
                    'goods_id' => $goods_id,//商品id
                    'type' => $goods['type'],//商品类型 1-优惠券 2-快递
                    'number' => $goods['stock'],//商品数量
                    'pay_type' => $goods['pay_type'],//支付类型 1-鸡蛋 2-积分
                    'price' => $goods['price'], //价格
                    'status' => 1,//状态 1-已完成 2-已取消
                    'create_time' => time() //创建时间
                ];
                } else {
                    $data = [
                    'order_sn' =>create_number('order'),//订单号
                    'user_id' => $userid,//用户id
                    'goods_id' => $goods_id,//商品id
                    'type' => $goods['type'],//商品类型 1-优惠券 2-快递
                    'number' => $goods['stock'],//商品数量
                    'pay_type' => $goods['pay_type'],//支付类型 1-鸡蛋 2-积分
                    'price' => $goods['price'], //价格
                    'status' => 0,//状态 1-已完成 2-已取消
                    'create_time' => time() //创建时间
                ];
                }
                if ($goods['pay_type']==1) {
                    //如果是鸡蛋支付
                    if ($score['integral'] < $goods['price']) {
                        show(4, '积分余额不足');
                    }
                    $user->update_integral($userid, '-', $goods['price']);//减用户的鸡蛋
                } else {
                    //如果是积分支付
                    if ($score['egg'] < $goods['price']) {
                        show(4, '鸡蛋余额不足');
                    }
                    $user->update_egg($userid, '-', $goods['price']);//减用户的鸡蛋//减用户的积分
                }
                $shop->stock_update($goods_id, '-', 1);//商品减库存
                $order = db('shop_order')->insert($data);
                $userinfo = db('user')->where('openid', $openid)->find();
                
                //转入易家商城
                if ($ret = $this->addyhq($goods, $user_rows)) {
                    if ($order) {
                        Db::commit();
                        show(1, '兑换成功', $userinfo);
                    } else {
                        Db::rollback();
                        show(0, '兑换失败:');
                    }
                } else {
                    show('-1', '请先登录易家有机小程序');
                }
            } else {
                show(0, '库存不足');
            }
        } catch (\Exception $e) {
            Db::rollback();
            show(0, '转入失败'.$e->getMessage());
        }
    }

    /*
     * 2020/08/12
     * 兑换记录
     * */
    public function record()
    {
        $data = [];
        $openid = param_check('openid');//openid
        $user_id = db('user')->where('openid', $openid)->value('id');//用户id
        
        $row = db('shop_order')->where('user_id', $user_id)->order('id', 'DESC')->select();
        foreach ($row as $k=>&$v) {
            $data[$k]['name'] = db('user')->where('id', $v['user_id'])->value('name');//用户名称
            $data[$k]['goods'] = db('shop_goods')->where('id', $v['goods_id'])->value('name');//商品名称
            if ($v['pay_type']==2) {
                $data[$k]['price'] = $v['price'].'鸡蛋';
            } else {
                $data[$k]['price'] = $v['price'].'积分';
            }
            $data[$k]['time'] = date('Y-m-d H:i:s', $v['create_time']);
        }
        json_response(1, '获取成功', $data);
    }
        
    //转入优惠券
    public function addyhq($goods= [], $user = [])
    {
        $url = "https://yyg.yijiaegou.com/wxapp.php?from=wxapp&c=entry&a=wxapp&do=index&m=community&controller=Goods.getQuanfromGame";
        $data['data'] = [
                'checkcode' => md5('asdfRTYUhjkl'.date('Y-m-d')),
                'unionid' => $user['unionid'],
                'title' =>  $goods['name'],
            //  'title' =>  '3元优惠抵扣券'
        ];
        $data['header'] = [
             'content-type' => 'application/x-www-form-urlencoded'
        ];
        $row = self::curlPost($url, $data['data'], 3, $data['header'], 'array');
        $ref = json_decode($row, true);
        //print_r($ref);die;
        if ($ref['code'] == 3) {
            //加入转入日志
            db('user_third_party')->insert([
                   'uid' => $user['id'],
                   'name' => '转入优惠券成功',
                   'type' => 2,//积分
                   'integral' =>  1,
                   'status' => 1, //转入成功
                   'create_time' => date('Y-m-d H:i:s')
               ]);
            return true;
        } else {
            //加入转入日志
            db('user_third_party')->insert([
                'uid' => $user['id'],
                'name' => '优惠券转入失败'.$ref['code'],
                'type' => 2,//积分
                'integral' =>  1,
                'status' => 2, //转入成功
                'create_time' => date('Y-m-d H:i:s')
            ]);
            return false;
        }
    }

    public function test()
    {
        //print_r(self::addyhq());
    }
    /*
     * 2020/08/12
     * 从小游戏转入优惠券
     * */
    public function addcoupon()
    {
        if (!$integral = intval(input('integral'))) {
            show(0, '请输入分数');
        }
        $url = "https://yyg.yijiaegou.com/wxapp.php?from=wxapp&c=entry&a=wxapp&do=index&m=community&controller=Signinreward.getscorefromgame";
        $openid = param_check('openid');
        $user = db('user')->where('openid', $openid)->find();
        if (!$user) {
            show('', '暂无用户数据');
        }
        if (!$user['integral']) {
            show('', '用户积分为0,无法转入');
        }
        if ($user['integral'] - $integral < 0) {
            show('', '用户积分不足');
        }
        $data['data'] = [
            'checkcode' => md5('asdfRTYUhjkl'.date('Y-m-d')),
            'unionid' => $user['unionid'],
            'score' =>  $integral
        ];
        // echo 'dateTime2:'. date('Y-m-d').PHP_EOL;
        // echo 'code:asdfRTYUhjkl'.PHP_EOL;
        // echo 'checkcode:'. $data['data']['checkcode'];
    
        $data['header'] = [
          'content-type' => 'application/x-www-form-urlencoded'
        ];
        $row = self::curlPost($url, $data['data'], 3, $data['header'], 'array');
        $ref = json_decode($row, true);
        
        if ($ref['code'] == 2) {
            //加入转入日志
            db('user_third_party')->insert([
                'uid' => $user['id'],
                'name' => '积分转入',
                'type' => 1,//积分
                'integral' => $integral,
                'status' => 1, //转入成功
                'create_time' => date('Y-m-d H:i:s')
            ]);
            db('user')->where('id', $user['id'])->setDec('integral', $integral);
            show(1, '积分转入成功', db('user')->where('openid', $openid)->find());
        } else {
            show(0, '积分转入失败:您尚未登录易家有机');
        }
    }

    public function curlPost($url, $post_data = array(), $timeout = 5, $header = "", $data_type = "")
    {
        $header = empty($header) ? '' : $header;
        //支持json数据数据提交
        if ($data_type == 'json') {
            $post_string = json_encode($post_data);
        } elseif ($data_type == 'array') {
            $post_string = $post_data;
        } elseif (is_array($post_data)) {
            $post_string = http_build_query($post_data, '', '&');
        }
        
        $ch = curl_init();    // 启动一个CURL会话
        curl_setopt($ch, CURLOPT_URL, $url);     // 要访问的地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // 对认证证书来源的检查   // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($ch, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);     // Post提交的数据包
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);     // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     // 获取的信息以文件流的形式返回
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //模拟的header头
        $result = curl_exec($ch);
    
        // 打印请求的header信息
        //$a = curl_getinfo($ch);
        //var_dump($a);
    
        curl_close($ch);
        return $result;
    }
}
