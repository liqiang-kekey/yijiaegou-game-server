<?php
namespace app\api\controller;

use think\Controller;
use app\api\model\User;
use app\api\model\UserChicken as UC;
use app\common\service\WeChat;
use app\api\model\UserMailAddress;

class Userchicken extends Controller
{
    protected $notify_url = 'https://game.yijiaegou.com/public/index.php/';
    protected $UserMailAddress = null;
    /**
     * 鸡蛋明细
     */
    function chicken_egg_info(){
        if(!$openid = input('openid')) show(0,'缺少信息');
        $user_model = new User();
        $cu_model = new UC();
        if(!$user = $user_model->getByOpenid($openid)) show(0,'用户尚未注册');
        $mylist = $cu_model->where(['uid' => $user->id,'isvip' => 1])->column('id');
        //print_r($mylist);
        if(!$mylist) show(0,'用户暂无小鸡数据');
        if (!$time = input('time')) {
            $egg_list = db('user_chicken_log')
                    ->where(['uid' => $user->id ,'c_id' => ['in' ,$mylist]])
                    ->order('create_time','DESC')
                    ->field('name as tkname,create_time')
                    ->paginate(15, false, ['query'=>$_POST])->each(function($v){
                        $v['rwname']  = '';
                        return $v;
                    });
        }else{
            $egg_list = db('user_chicken_log')
                    ->where(['uid' => $user->id ,'c_id' => ['in' ,$mylist],'create_time' => ['like',"{$time}%"]])
                    ->field('name as tkname,create_time')
                    ->order('create_time','DESC')
                    ->paginate(15, false, ['query'=>$_POST])->each(function($v){
                        $v['rwname']  = '';
                        return $v;
                    });
        }
        if (!$egg_list->all()) {
            show(0, '暂无记录');
        }
        show(1,'查询成功',$egg_list->all());
    }

    /**
     * 是否可以出栏
     * @param unionid string  平台编号
     * @param number  string  小鸡编号
     */
    function ischeck_chicken_out(){
        if(!$unionid    = input('unionid')) show('','缺少Unionid参数');
        if(!$number     = input('number')) show(0,'缺少小鸡编号');
        if(!$game_user  = db('user')->where('unionid',$unionid)->find())  show(0,'小游戏用户不存在');

        if(!$min_user   = db('applet_user')->where('unionid',$unionid)->find()) show(0,'小程序用户不存在');
        if(!$chickend   = db('user_chicken')->where(['uid' => $game_user['id'] ,'number' => $number])->find()) show(0,'出栏小鸡不存在');
        $out_day = db('sys_chicken_level')->where('class',4)->find();
        
        if(abs( round( (time() - strtotime($chickend['create_time'] ))/84600)) >= $out_day['day']) {
            show(1,'验证成功',['unionid' => $unionid,'number' => $number]);
        }
        show(0,'验证失败,请联系管理员');
    }
    
    /**
     * 微信检测文字
     */
    public function check_string_wx($str = '')
    {
        $url = "https://api.weixin.qq.com/wxa/msg_sec_check?access_token={$this->get_access_token()}";
        $data['data'] = json_encode([
            'content' => $str
        ], JSON_UNESCAPED_UNICODE);
        $res = json_decode(curl('POST', $url, $data), true);
        //print_r($res);
        if ($res['errcode'] == 0 and $res['errmsg'] == 'ok') {
            return true;
        } else {
            return $res;
        }
    }
    
    /**
     * 获取ACCESS_TOKEN
     */
    public function get_access_token()
    {
        $redis = redis_instance();
        if (!$redis->exists('Wx_access_token')) {
            $appid = Env::get('applet_game_app_id');
            $secret = Env::get('applet_game_app_secret');
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}";
            $res = json_decode(curl('GET', $url, []), true);
            //dump($res);die;
            if (isset($res['access_token'])) {
                $redis->set('Wx_access_token', $res['access_token'], ['ex'=> 7200]);
                return $redis ->get('Wx_access_token');
            } else {
                return null;
            }
        }
        return $redis->get('Wx_access_token');
    }

    /**
     *  出栏留资
     *  @param unionid      string  平台编号
     *  @param number       string  小鸡编号
     *  @param province     string  省分
     *  @param city         string  城市
     *  @param area         string  地区
     *  @param address      string  详细地址
     *  @param mobile       string  联系人电话
     *  @param name         string  联系人姓名
     */
    function out_basket(){
        try {
            //验证参数
            if (!$unionid    = input('unionid')) {
                show('', '缺少Unionid参数');
            }
            if (!$number     = input('number')) {
                show(0, '缺少小鸡编号');
            }
            if (!$province   = input('province')) {
                show(0, '缺少省份');
            }
            if (!$city       = input('city')) {
                show(0, '缺少市');
            }
            if (!$area       = input('area')) {
                show(0, '缺少地区');
            }
            if (!$address    = input('address')) {
                show(0, '缺少详细地址');
            }
            if (!$mobile     = input('mobile')) {
                show(0, '缺少联系人手机');
            }
            if (!$name       = input('name')) {
                show(0, '缺少联系人姓名');
            }
            //验证用户数据
            if (!$game_user  = db('user')->where('unionid', $unionid)->find()) {
                show(0, '小游戏用户不存在');
            }
            if (!$min_user   = db('applet_user')->where('unionid', $unionid)->find()) {
                show(0, '小程序用户不存在');
            }
            if (!$chickend   = db('user_chicken')->where(['uid' => $game_user['id'] ,'number' => $number,'level' => 4])->find()) {
                show(0, '出栏小鸡不存在');
            }
            /**
             * 验证用户是否已经配置地址，如果没有配置地址，则将出栏地址保存为默认地址
             */
            if (null == $this->UserMailAddress) {
                $this->UserMailAddress = new UserMailAddress();
            }
            $my_address = $this->UserMailAddress->where(['uid' => $game_user['id']])->find();
            if(!$my_address && $this->check_string_wx($name) && $this->check_string_wx($address)){//为空               
                $addressData = [
                    'uid' => $game_user['id'],                    
                    'name' => $name,
                    'mobile' => $mobile,
                    'city' => $city ,
                    'province' => $province,
                    'area' => $area ,
                    'address' => $address ,
                    'default' => 1,
                    'create_time' => date('Y-m-d H:i:s')
                ];                
                $this->UserMailAddress->where(['uid' => $game_user['id']])->insert($addressData);
            }
            
            //验证是否可以出栏
            $out_day = db('sys_chicken_level')->where('class', 4)->find();
            if (abs(round((time() - strtotime($chickend['create_time']))/84600)) >= $out_day['day']) {
                //如果地址在免邮费中
                $xzcity = db('applet_goods')->where('id', 1)->find();
                // 生成订单
                $order_sn    = 'E'.time().mt_rand(1000, 9999); // 订单编号
                if (strstr($province, $xzcity['freight_city']) || $xzcity['is_freight'] == 0) {
                    $flag = db('user_chicken_email_order')->where('id', $chickend['id'])->insert([
                        'order_sn'      => $order_sn ,
                        'uid'           => $game_user['id'] ,
                        'applet_uid'    => $min_user['id'] ,
                        'chickend_id'   => $chickend['id'] ,
                        'is_pay'        => 0 ,
                        'pay_money'     => 0 ,
                        'pay_param'     => null ,
                        'status'        => 1 ,
                        'sys_city'      => $xzcity['freight_city'] ,
                        'isout'         => 1 ,
                        'emailname'     => $name ,
                        'emailmobile'   => $mobile ,
                        'province'      => $province ,
                        'city'          => $city ,
                        'area'          => $area ,
                        'address'       => $address,
                        'out_time'      => date('Y-m-d') ,
                    ]);
                      //修改小鸡状态
                
                    if ($flag) {
                        db('user_chicken')->where('id', $chickend['id'] )->update([
                            'level'  => 10 ,//已出栏
                            ]);
                        show(1, '出栏成功');
                    }
                } else {
                    //统一下单
                    $back_backurl = '';
                    $wechat = new Wechat();
                    $param_data = [
                        'order_sn'      => $order_sn ,
                        'uid'           => $game_user['id'] ,
                        'applet_uid'    => $min_user['id'] ,
                        'chickend_id'   => $chickend['id'] ,
                        'is_pay'        => 1 ,
                        'pay_money'     => $xzcity['shipping_money'],//邮费
                        'pay_param'     => null ,
                        'status'        => 0,
                        'sys_city'      => $xzcity['freight_city'] ,
                        'isout'         => 0 ,
                        'emailname'     => $name ,
                        'emailmobile'   => $mobile ,
                        'province'      => $province ,
                        'city'          => $city ,
                        'area'          => $area ,
                        'address'       => $name ,
                        'out_time'      => date('Y-m-d') ,
                    ];
                    if ($flag = db('user_chicken_email_order')->where('id', $chickend['id'])->insertGetId($param_data)) {
                        $notify_url = $this->notify_url.'api/Userchicken/notify';
                        $pay_param  = $wechat->pay_param($min_user['openid'], $order_sn, $xzcity['shipping_money']*100, $notify_url, '出栏邮费');
                        show(2, '下单成功', [
                            'order_id'  => $flag,
                            'pay_param' => $pay_param
                        ]);
                    }
                }
            }
        }catch(\Exception $e ){
            show(0,'接口错误',[
                'msg'   => $e->getMessage(),
                'line'  => $e->getLine()
            ]);
        }
    }


    /**
     * 邮费下单回调
     */
    function notify(){
        db('error')->insert([
            'error'       => 1,
            'desc'        => '运费回调日志',
            'text'        => file_get_contents('php://input'),
            'create_time' => time()
        ]);

        try {
            $wechat = new Wechat();
            $data = $wechat->check_sign(function($data){
                $order_sn = $data['out_trade_no']; //订单号
                $pay_money = $data['total_fee'] / 100; // 支付金额
                $email_order = db('user_chicken_email_order')->where('order_sn', $order_sn)->find();
                //修改订单状态
               
                if ($email_order) {
                    db('user_chicken_email_order')->where('id', $email_order['id'])->update([
                        'pay_time' => date('Y-m-d H:i:s'),
                        'status' => 1,//支付
                        'pay_param' => json_encode($data, JSON_UNESCAPED_UNICODE)
                    ]);
                
                    //修改小鸡状态
                    db('user_chicken')->where('id', $email_order['chickend_id'])->update([
                        'level'  => 10 ,//已出栏

                    ]);
                }
            });
        }catch(\Exception $e){
            db('error')->insert([
                'error' => 2,
                'desc'  => '支付运费回调错误',
                'text'  => json_encode([
                    'message'  => $e->getMessage(),
                    'line'     => $e->getLine(),
                    'code'     => $e->getCode(),
                    'response' => $wechat->get_last_data()
                ], JSON_UNESCAPED_UNICODE),
                'create_time' => time()
            ]);
        }
    }
}
