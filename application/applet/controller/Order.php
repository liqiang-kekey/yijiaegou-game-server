<?php


namespace app\applet\controller;

// 订单接口
use app\api\model\User;
use app\common\service\WeChat;
use app\applet\controller\Oauth;
use think\Db;

class Order extends Base
{
    protected $notify_url = 'https://game.yijiaegou.com/public/index.php/';


    /**
     * 换取小游戏openid
     * @date 2020/8/12 18:21
     */
    public function check_user()
    {
        try {
            $this->get_user_id(true);
            $my_user = db('user')->where('id', $this->game_user_id)->find();
            $re = db('user_certificate')->where(['uid' => $this->game_user_id , 'name' => ['neq','not null']])->find();
            //print_r($re);die;
            json_response(1, '检查成功', [
                'game_openid' => $my_user['openid'],
                'status' => $re ? 0 : 1
            ]);
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * VIP鸡信息
     * @date 2020/8/13 16:26
     */
    public function goods()
    {
        try {
            $data = db('applet_goods')
                ->where('id', 1)
                ->field('name, intro, banner, thumb, money,shipping_money')
                ->find();
            $data['banner'] = json_decode($data['banner'], true);
            json_response(1, 'ok', $data);
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }


    /**
     * 提交订单
     * @date 2020/8/12 18:01
     */
    public function submit_order()
    {
        try {
            $user_id = $this->get_user_id(true);
            // 生成订单
            $number      = (int)param_check('number');
            $order_sn    = 'C'.time().mt_rand(1000, 9999); // 订单编号
            $uname = input('name');
            $address = input('address');//地址
            $mobile = input('mobile');//手机
            //修改用户姓名
            $userModel  = new User();
            $myuser_unionid = db('applet_user')->where('id', $user_id)->field('unionid')->find()['unionid'];
            //如果用户是花字，无法保存到数据库，处理结果
            $user_game = $userModel->where(['id' => $user_id])->find();
            if($user_game['nickname'] && $user_game['name']){
                $userModel->where('unionid', $myuser_unionid)->update([
                    'nickname' => $uname,
                    'name' => $uname,
                    ]);
            }
            /***end***/
            $uid = db('user')->where('unionid', $myuser_unionid)->field('id')->find()['id'];
            //如果存在则修改
            if (!$user_certificate = Db::name('user_certificate')->where('uid', $uid)->find()) {
                if (!$myuser_unionid) {
                    show(0, '用户不在小游戏内');
                }
                $userModel->where('unionid', $myuser_unionid)->update([
                'name' => $uname,
                'mobile' => $mobile,
                'address' => $address,
                ]);
            } else {
                $uname = $userModel->getByUnionid($myuser_unionid)['name'];
            }
            
            // 验证数量
            if (empty($number)) {
                json_response(0, '请选择正确的数量');
            }

            // 订单金额计算
            $goods_info = db('applet_goods')->where('id', 1)->field('money')->find();
            $order_money = $goods_info['money'] * $number ; // 订单金额 = 数量 + 运费
            $order_id = db('applet_order')->insertGetId([
                'user_id'        => $user_id,                     // 小程序用户ID
                'name'           => $uname ?? '',
                'game_user_id'   => $this->game_user_id,          // 小游戏用户ID
                'number'         => $number,                      // 购买数量
                'order_sn'       => $order_sn,                    // 订单编号
                'money'          => $order_money,                 // 订单金额
                'status'         => 0,                            // 订单状态 0-待支付 1-已支付
                'create_time'    => time(),                       //创建时间
                'address'        => $address,
                'mobile'         => $mobile,
            ]);

            if ($order_id) {
                $WeChat     = new WeChat();
                $notify_url = $this->notify_url.'applet/order/notify';
                $pay_param  = $WeChat->pay_param($this->openid, $order_sn, $order_money*100, $notify_url, 'VIP鸡购买');
                json_response(1, '下单成功', [
                    'order_id'  => $order_id,
                    'pay_param' => $pay_param
                ]);
            } else {
                json_response(0, '下单失败');
            }
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * 支付回调
     * @date 2020/8/12 18:18
     */
    public function notify()
    {
        db('error')->insert([
            'error'       => 1,
            'desc'        => '支付回调日志',
            'text'        => file_get_contents('php://input'),
            'create_time' => time()
        ]);
        $WeChat = new WeChat();
        try {
            $WeChat->check_sign(function ($data) {
                $order_sn  = $data['out_trade_no']; // 订单号
                $pay_money = $data['total_fee'] / 100; // 支付金额
                // 订单信息查询
                $order_info = db('applet_order')->where('order_sn', $order_sn)->find();
                if (!empty($order_info) && empty($order_info['status'])) {
                    
                    // 修改订单状态
                    $res = db('applet_order')->where('id', $order_info['id'])
                        ->update([
                            'status'      => 1,
                            'pay_money'   => $pay_money,
                            'pay_param'   => json_encode($data, JSON_UNESCAPED_UNICODE),
                            'pay_time'    => time(),
                            'update_time' => time()
                        ]);
                    if ($res) {
                        // 发放VIP鸡
                        $userModel = new User();
                        $chicken = $userModel->user_adopt_vip($order_info['game_user_id'], $order_info['number'], $order_sn);
                        
                        db('applet_order')->where('id', $order_info['id'])->update([
                            'vip_chicken' => json_encode($chicken)
                        ]);
                        //分账
                        $this->separate_accounts($order_info, $pay_money);
                        //通知
                        $this->buy_notify($order_info, $pay_money);
                        $rule = db('sys_sharp')->alias('a')
                                ->join('sys_reward r', 'r.id = a.reward_id', 'left')
                                ->where(['a.class' => 3])
                                ->field('a.id,a.class,a.limit_count,r.name,r.type,r.number')
                                ->find();
                        //邀请人领取了多少次奖励
                        $flow_preiz_list = db('user_sharp')->where(['class' => 3,'uid' => $order_info['game_user_id']])->select();
                        if (!$rule['limit_count'] or count($flow_preiz_list) < $rule['limit_count']) {
                            //加入分享明细
                            db('user_sharp')->insert([
                                'uid'           => $order_info['game_user_id'],
                                'class'         => 3,
                                'name'          => '分享三阶段奖励',
                                'reward'        => $rule['name'],
                                'integral'      => '+'.$rule['number'],
                                'create_time'   => date('Y-m-d H:i:s'),
                            ]);
                            //修改用户积分
                            $this->user->where(['id' => $order_info['game_user_id']])->setInc('integral', $rule['number']);
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            db('error')->insert([
                'error' => 2,
                'desc'  => '支付回调报错',
                'text'  => json_encode([
                    'message'  => $e->getMessage(),
                    'line'     => $e->getLine(),
                    'code'     => $e->getCode(),
                    'response' => $WeChat->get_last_data()
                ], JSON_UNESCAPED_UNICODE),
                'create_time' => time()
            ]);
        }
    }
   
    /***
     * 分账
     * 如小游戏A为B上级，小程序A为B上级，则A获B收益；
     * 如小游戏A为B上级，小程序C为B上级，则C获B收益；
     * 如小游戏A为B上级，小程序B无上级（A已注册），则无人获收益；
     * 如小游戏A为B上级，小程序B无上级（A无注册），则无人获收益；
     *  */
    public function separate_accounts($order, $pay_money)
    {
        try {
            $user = db('applet_user')->where(['id' => $order['user_id']])->field('unionid')->find();
            //查询上级
            $arr_other_data = request_applet_callback($user['unionid']);
            if (isset($arr_other_data['parent'])) {
                //分成比例
                $proportion = db('applet_goods')->field('proportion')->find()['proportion'];

                //上级编号如果在注册了
                if (!$user_party = db('user_party')->where(['unionid' => $arr_other_data['parent']['unionid']])->find()) {
                    //加入第三方父级信息
                    if (isset($arr_other_data['parent'])) {
                        //上级用户是否再小游戏中注册
                        $applat_user = db('applet_user')->where('unionid', $arr_other_data['parent']['unionid'])->find();
                        $game_user = db('user')->where('unionid', $arr_other_data['parent']['unionid'])->find();
                        //加入第三方用户信息
                        db('user_party')->insert([
                    'applet_user_id'        =>  $applat_user    ? $applat_user['id'] : '',
                    'game_user_id'          =>  $game_user      ? $game_user['id'] :'',
                    'game_user_fath_id'     =>  $game_user      ? $game_user['fid'] :'',
                    'is_parent'             =>  isset($arr_other_data['parent']['member_id']) ? 1: 0,
                    'member_id'             =>  $arr_other_data['parent']['member_id'],
                    'openid'                =>  $arr_other_data['parent']['openid'],
                    'unionid'               =>  $arr_other_data['parent']['unionid'],
                    'share_id'              =>  $arr_other_data['parent']['share_id'],
                    'agentid'               =>  $arr_other_data['parent']['agentid'],
                    'username'              =>  $arr_other_data['parent']['username'],
                    'avatar'                =>  $arr_other_data['parent']['avatar'],
                    'year'                  =>  date('Y'),
                    'month'                 =>  date('m'),
                    'day'                   =>  date('d'),
                    'create_time'           =>  date('Y-m-d H:i:s'),
                ]);
                    
                        //加入分账
                        db('user_separate_accounts')->insert([
                    'buy_id'                =>  $order['game_user_id'] ?? '',
                    // 'buy_fath_id'           =>  $order['game_user_id'] ?? null,
                    'member_id'             =>  $arr_other_data['parent']['member_id'],
                    'order_sn'              =>  $order['order_sn'],
                    'proportion'            =>  $proportion,
                    'pay_money'             =>  $pay_money,
                    'settlement_money'      =>  round(($proportion * $pay_money)/100, 2),
                    'year'                  =>  date('Y'),
                    'month'                 =>  date('m'),
                    'day'                   =>  date('d'),
                    'create_time'           =>  date('Y-m-d H:i:s'),
                ]);
                    }
                } else {
                    //加入分账
                    db('user_separate_accounts')->insert([
                'buy_id'                =>  $order['game_user_id'] ?? '',
                'member_id'             =>  $arr_other_data['parent']['member_id'],
                'order_sn'              =>  $order['order_sn'],
                'proportion'            =>  $proportion,
                'pay_money'             =>  $pay_money,
                'settlement_money'      =>  round(($proportion * $pay_money)/100, 2),
                'year'                  =>  date('Y'),
                'month'                 =>  date('m'),
                'day'                   =>  date('d'),
                'create_time'           =>  date('Y-m-d H:i:s'),
                ]);
                }
            }
        } catch (\Exception $e) {
            db('error')->insert([
                'error'       => 2,
                'desc'        => '分销错误',
                'text'        =>  $e->getMessage().'错误发生在:'.$e->getLine(),
                'create_time' => time()
            ]);
        }
    }

    // function test(){
    //     $order = db('applet_order')->where(['order_sn'=>'C16013552092580'])->find();
    //     print_r($order);
    //     $this->buy_notify($order);
    // }
    /**
     * 通知第三方
     * 参考：小游戏购买土鸡同步小程序分销佣金文档
     * @param order
     */
    public function buy_notify($order, $pay_money)
    {
        //$order = db('applet_order')->find(1);
        $url = 'https://yyg.yijiaegou.com/wxapp.php?controller=MemberShare.countMemberSharePrice';
        $user = db('user')->where(['id' => $order['game_user_id']])->field('unionid,name,nickname,avatar,image')->find();
        //print_r($user);die;
        $goods_info = db('applet_goods')->field('name,thumb')->find();
        $arr['data'] = [
            'unionid'       => $user['unionid'],
            'username'      => $user['nickname'],
            'avatar'        => $user['avatar'],
            'goodsname'     => $goods_info['name'],
            'goodsimage'    => $goods_info['thumb'],
            'goodsnum'      => $order['number'],
            'goodsprice'    => $pay_money,
            'buytime'       => time(),
            'source'        => 'minigames',
        ];
        //排序
        ksort($arr['data']);
        $sign = http_build_query($arr['data']);
        $myfile = fopen(ROOT_PATH."pri.key", "r") or die("无效密钥");
        $rsa_key_path = fread($myfile, filesize(ROOT_PATH."pri.key"));
        $private_key = openssl_pkey_get_private($rsa_key_path);
        if (!$private_key) {
            show(0, '无效密钥');
        }
        openssl_sign($sign, $encrypted, $private_key, OPENSSL_ALGO_SHA256);
        $encrypted = base64_encode($encrypted);
        $arr['data']['sign'] = $encrypted;
        $arr['data'] =  json_encode($arr['data']);
        $res = Curl('POST', $url, $arr);
        $data = json_decode($res, true);
      
        if ($data['code'] == 1) {
            db('error')->insert([
                'error'       => 2,
                'desc'        => '分销成功',
                'text'        =>  json_encode($data),
                'create_time' => time()
                ]);
            return true;
        } else {
            db('error')->insert([
                'error'       => 2,
                'desc'        => '分销错误',
                'text'        =>  json_encode($data),
                'create_time' => time()
                ]);
            return false;
        }
    }
    


    /**
     * 测试分享奖励
     */
    // public function test()
    // {
    //     $game_user_id = 23;
    //     $rule = db('sys_sharp')->alias('a')
    //         ->join('sys_reward r', 'r.id = a.reward_id', 'left')
    //         ->where(['a.class' => 3])
    //         ->field('a.id,a.class,a.limit_count,r.name,r.type,r.number')
    //         ->find();
    //     //邀请人领取了多少次奖励
    //     $flow_preiz_list = db('user_sharp')->where(['class' => 3,'uid' => $game_user_id])->select();
    //     if (!$rule['limit_count'] or count($flow_preiz_list) < $rule['limit_count']) {
    //         //加入分享明细
    //         db('user_sharp')->insert([
    //         'uid'           => $game_user_id,
    //         'class'         => 3,
    //         'name'          => '分享三阶段奖励',
    //         'reward'        => $rule['name'],
    //         'integral'      => '+'.$rule['number'],
    //         'create_time'   => date('Y-m-d H:i:s'),
    //     ]);
    //         //修改用户积分
    //         $this->user->where(['id' => $id])->setInc('integral', $rule['number']);
    //     }
    // }
}