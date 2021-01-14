<?php
namespace app\api\controller;

use think\Controller;
use think\Request;
use app\api\controller\Oauth;
use app\api\model\SysVideo;
use app\api\model\UserMailAddress;
use app\api\model\UserChicken;
use app\api\model\UserSign;
use app\api\model\UserCertificate;

use think\Env;
use think\Redis;
use think\ Db;

/**
 * 用户信息
 */

 //视频账号:sxyk
class Userinfo extends controller
{
    protected $user ;
    protected $UserMailAddress;
    protected $time;
    public function _initialize()
    {
        //判断提交类型
        $method_type = Request::instance()->method(true);
        if ($method_type!= 'POST') {
            show(0, '请求错误');
        }
        //获取数据
        $openid = $this->openid =  input('openid');
        $code =  $this->code =    input('code');
        //实例化模型
        $UserMailAddress = $this->UserMailAddress = new UserMailAddress();
        $user = $this->user = model('user');
        $time = $this->time = date('Y-m-d H:i:s');
    }

    /**
     * 获取用户信息更新用户宠物信息，升级，产蛋
     * @param string openid  用户编号
     * @return json arrary
     */
    public function get_userinfo($openid = '')
    {
        if (!$openid) {
            show(0, 'OPENID为空');
        }
        $system  = 5;//连续求安到5次
        $system_out_egg = [];

        if (!$user = $this->user::getByOpenid($openid)) {
            show(0, '用户不存在');
        }
         //获取模式
        $sys_game_model = db('sys_game')->where('id',1)->find();
        if($sys_game_model['is_vip'] == 1){
            if ($user['isswitch'] == 0) {
                $this->user->where('id', $user['id'])->update([
                'isswitch' => 1
              ]);
            }
        }else{
            $this->user->where('id',$user['id'])->update([
                'isswitch' => 0
            ]);
        }
        $dt = date('Y-m-d');
        
        //是周一，清理签到数据或者用户上次登录的时间和今天的时间比较是否大于 7天
        $time = date('w');
        if ($time == 1 || ($time - $user['last_sign_time']) > 86400 * 7) {
            $this->user->where('id', $user['id'])->update(['sign' => 0]);
        }
        //定义喂养
        $feed_food = false;
        //所有得鸡是否喂养
        if ($my_chicken_ids = db('user_chicken')->where(['uid' => $user->id,'level' => ['neq','10']])->column('id')) {
            $feed_food = db('user_chicken_having')->where(['c_id' => ['in',$my_chicken_ids],'class' => 1,'create_time' => ['like',$dt.'%'] ])->select();
            //如果查询所有鸡ID 等于喂养鸡得长度则完成了
            if (count($my_chicken_ids) == count($feed_food)) {
                $feed_food = true;
            } else {
                $feed_food = false;
            }
        }

        //免费蛋变成鸡
        if ($my_free_chick = db('user_chicken')->where(['isvip' =>2 ,'uid' => $user['id']])->find()) {
            //转换时间
            $res = floor((strtotime($this->time) - strtotime($my_free_chick['create_time']))/86400);
            //var_dump($my_free_chick);die;
            Db::table('raisingchickens_user_chicken')->where(['id' => $my_free_chick['id'] ])->update([
                'level' => 2,
                'cycle' => $res,
            ]);
        }

        //用户没有鸡
        if (!$my_chicken_list = db('user_chicken')->where(['uid' => $user['id'] ,'level' => ['neq',10]])->select()) {
            show(1, '查询成功', ['user'=>$user,'chicken_list'=>$my_chicken_list,'out_egg'=>$system_out_egg,'feed' => $feed_food]);
        }
        //用户是否有VIP宠物
        if (!$my_vip_cw = db('user_chicken')->where(['uid' => $user['id'] ,'isvip' => 1,'level' => ['neq',10]])->select()) {
            
            show(1, '查询成功', ['user'=>$user,'chicken_list'=>$my_chicken_list,'out_egg'=>$system_out_egg,'feed' => $feed_food]);
        }
        $user_feed = 0;

        //验证是否可以出栏
        $out_day = db('sys_chicken_level')->where('class', 4)->find();
        //echo $out_day;die;
        //等级设置
        $system_checken_level = db('sys_chicken_level')->select();
        foreach ($my_vip_cw as $item) {
           
            if($item['level'] == 10) {
                continue;
            }
            //升级 、当前日期减去宠物创建日期/86400 为0则跳过
            if (!$chaji = $user_feed = date_diff(date_create(explode(' ', $item['create_time'])[0]), date_create(date('Y-m-d')))->d) {
                continue;
            }
          
            //用户总共喂养天数
            if ($user_feed > $user->feed) {
                db('user')->where('id', $user->id)->update(['feed' => $user_feed]);
            }

            
            if ($chaji < $system_checken_level[0]['day']) {
                //孵化期
                $falg = db('user_chicken')->where('id', $item['id'])->update([
                        'cycle' => $chaji,
                        'level' => 1
                    ]);
              
            } elseif ($chaji >= $system_checken_level[0]['day']  and  $chaji < $system_checken_level[2]['day']) {
                //生长期
                $falg = db('user_chicken')->where('id', $item['id'])->update([
                        'cycle' => $chaji,
                        'level' => 2
                    ]);
                   
            } elseif ($chaji >= $system_checken_level[2]['day'] and $item['outegg'] < 500) {
                //属于产蛋期
                $falg = db('user_chicken')->where('id', $item['id'])->update([
                        'cycle' => $chaji,
                        'level' => 3
                ]);
            }
        
            if(abs(round((time() - strtotime($item['create_time']))/84600)) >= $out_day['day']){
                //孵化期
                $falg = db('user_chicken')->where('id', $item['id'])->update([
                        'level' => 4
                    ]);
            }
            //echo date_diff(date_create(explode(' ', $item['last_feed_time'])[0]), date_create(date('Y-m-d')))->d .PHP_EOL;
            //连续喂养系统设定天数则产蛋
            if (date_diff(date_create(explode(' ', $item['last_feed_time'])[0]), date_create(date('Y-m-d')))->d >= 1 
            and $item['feed_count'] >= $system_checken_level[2]['feed_count'] 
            and $chaji >= $system_checken_level[2]['day'] 
            and $item['level'] == 3
            and $item['feed_lx'] >= $system_checken_level[2]['feed_count'] ) {
                //此VIP鸡是否产蛋
                if (!db('user_chicken_having')->where(['uid' => $user['id'],'class'=> 2 ,'c_id' => $item['id'],'create_time' => ['like',"{$dt}%" ]])->find()) {
                    db('user_chicken_having')->insert([
                        'uid' => $user['id'],
                        'class' => 2, //产蛋
                        'c_id' =>$item['id'], //小鸡编号
                        'name' => '产蛋',
                        'create_time' => $this->time,
                        //'uid' => $user['id'],
                    ]);
                    //连续喂养清0
                    db('user_chicken')->where('id', $item['id'])->update([
                        'feed_lx' => 0
                    ]);
                    //产蛋+1
                    db('user_chicken')->where('id', $item['id'])->setInc('outegg');
                }
                //组装鸡蛋
            } else {
                continue;
            }
        }
        //小鸡产蛋，加入小鸡行为
        $egg_list = db('user_chicken_having')
                    ->alias('h')
                    ->join('user_chicken c', 'c.id = h.c_id', 'LEFT')
                    ->where(['h.uid' =>$user->id ,'h.class' =>2 ,'h.ispickup' => 0])->field('h.id,c.number')->select();
        if (!$egg_list) {
            $system_out_egg = [];
        }
        foreach ($egg_list as $v) {
            $system_out_egg[] = [
                'id' => $v['id'],
                'vip_number' => $v['number'],
                'egg' => 1
            ];
        }
        $my_chicken_list = Db::name('user_chicken')->where(['uid' => $user['id'] ,'level' => ['neq',10]])->select();
        
        //赠送提示
        $give_list = db('user_chicken_order')->where(['uid' => $user->id])->order('create_time', 'DESC')->select();
        $tips_windows_list =[];
        if ($give_list) {
            foreach ($give_list as $v) {
                //赠送宠物时间大于1天则，则修改成过期
                if (strtotime($this->time) - strtotime($v['create_time']) > 86400) {
                    db('user_chicken_order')->where('id', $v['id'])->update([
                        'status' => 3,//已失效
                    ]);
                }
                //如果领取了，则提示弹窗
                if ($v['status'] == 2 && $v['is_tips'] == 2) {
                    //赠送，并且未关闭的
                    $give_names  = $this->user->where(['id' => $v['give_id'] ])->field('if(name=null,name,nickname) name')->find();
                    $tips_windows_list[] = [
                        'id' => $v['id'],
                        'link' => $v['template_id'] == 1 ? '家人':$v['template_id'] == 2 ? '朋友':$v['template_id'] == 3 ?'同事': '暂无',
                        'chicken_number' => $v['number'],
                        'give_name' => $give_names['name'],
                        'content' =>  $v['content']
                    ] ;
                    unset($give_names);
                }
            }
        }
    
        //产蛋期进度
        foreach($my_chicken_list as &$v){
            $v['id'] = $v['id'];
            $v['egg_product'] = ($v['feed_lx'] / 5* 100);
        }

        //连续签到至系统设置的天数后，则产蛋
        if (!$user ['feed'] or $user['feed'] < $system) {
            show(1, '查询成功', ['user'=>$this->user::getByOpenid($openid),'chicken_list'=>$my_chicken_list,'out_egg'=>$system_out_egg,'feed' => $feed_food,'tips_windows' => $tips_windows_list]);
        }
        show(1, '查询成功', ['user'=>$this->user::getByOpenid($openid),'chicken_list'=>$my_chicken_list,'out_egg'=>$system_out_egg,'feed' => $feed_food,'tips_windows' => $tips_windows_list]);
    }

    /**
     * 消除赠送弹窗
     * @param int id数组
     * @param openid
     * @return json
     */
    public function windows_give_clean()
    {
        if (!$id = input('id')) {
            show(0, '缺少弹窗Id');
        }
        if (!$openid = input('openid')) {
            show(0, '缺少Openid');
        }
        if (!$user = $this->user->getByOpenid($openid)) {
            show(0, '用户不存在');
        }
        $where ;
        try {
            $id  = json_decode($id, true);
        } catch (\Exception $e) {
            show(0, '请传递正确的参数Id');
        }
        if (is_array($id)) {
            $where = ['id' => ['in',$id] ,'uid' => $user->id,'status' => 2,'is_tips' => 2];
        } else {
            $where = ['id' => $id ,'uid' => $user->id,'status' => 2,'is_tips' => 2];
        }
        if (!$tips = db('user_chicken_order')->where($where)->select()) {
            show(0, '数据不存在');
        }
        db('user_chicken_order')->where($where)->update([
            'is_tips' => 1,
            'tips_time' => $this->time
        ]) ? show(1, '操作成功') : show(0, '操作失败');
    }

   /**
     * 授权获取地址
     * @param province
     * @param city
     * @param region
     */
    public function edit_user_info(){
        $openid = input('openid') ;
        if (!$openid) {
            show(0, 'openid不能为空');
        }
          //获取用户
        $user = $this->user->where(['openid' => $openid])->find();
        if (!$user) {
            show(0, '用户尚未注册');
        }
        //修改用户数据
        if(!$user->address){
            $ref = $this->user->where('id', $user['id'])->update([
                'province'  => input('province'),
                'city'      =>input('city'),
                'region'    => input('region'),
                'address'   => input('province').input('city').input('region')
            ]);
        }else{
            $ref = $this->user->where('id', $user['id'])->update([
                'province' => input('province'),
                'city'     =>input('city'),
                'region'   => input('region'),
            ]);
        }
        if (!$ref) {
            show(0, '修改失败');
        }
        show(1, '修改信息成功', $this->user->where(['openid' => $openid])->find());
    }
    /**
     * 用户修改
     * @param string $openid
     * @param string  age
     * @param string  sex
     * @param string  mobile
     * @param string  image
     * @param string  name
     * @param string  sex
     * @param string  address
     */
    public function user_edit()
    {
        $openid = input('openid') ;
        // $age = input('age') ;
        $sex = input('sex') ;
        $mobile = input('mobile') ;
        $image = input('image') ;
        $name = input('name');
        $address = input('address');
        if (!$openid) {
            show(0, 'openid不能为空');
        }
        if (!$mobile || strlen($mobile) != 11) {
            show(0, '手机号不能为空,或者手机长度不够');
        }
        if (!$this->check_string_wx($name)) {
            show('', '姓名未通过微信安全检测');
        }

        //获取用户
        $user = $this->user->where(['openid' => $openid])->find();
        if (!$user) {
            show(0, '用户尚未注册');
        }
        //修改用户数据
        $ref = $this->user->where('id', $user['id'])->update([
            'mobile'    => $mobile,
            'image'     => $image,
            'name'      => $name,
            'address'   => $address,
            'sex'       => $sex ,
            'update_time' => $this->time,
        ]);
        if (!$ref) {
            show(0, '修改失败');
        }
        show(1, '修改信息成功', $this->user->where(['openid' => $openid])->find());
    }

    /**
     * 视频列表
     * cmdId 指定指令
     * ip 服务器ip，非自建服务器请留空
     * user 登录账号
     * password 登录密码(支持MD5)，默认空密码
     * dev 设备SN，默认返回账号下所有设备，指定设备则用逗号隔开SN
     */
    public function user_video_list()
    {
        $Sys_video = new SysVideo();
        $viod_config = $Sys_video->where(['isenable' => 1,'isdelete' => '2'])->find()->toArray();
        if (!$viod_config) {
            show('尚未配置视频，请联系管理员');
        }
        //组装路径
        $url = $viod_config['url']."?cmdId=100&ip=&user={$viod_config['uname']}&password=&dev";
        $res = file_get_contents($url);
        //转数组
        $res = json_decode($res, true);
        if ($res['cmdId'] == '101' && $res['result'] == 0) {
            show(1, '查询成功', $res['devlist']);
        } else {
            show(0, "查询失败：".video_code($res['result']));
        }
    }

    /**
     * 视频监看请求
     * @param dev //设备号
     * @return json
     */
    public function user_see_video()
    {
        $dev = input('dev');
        //$dev = 'DS-2DC7223IW-DS-2DC7223IW-A20181026AACHC63453057W'; DS-2DC7223IW-A20181026AACHC63453057W
        if (!$dev) {
            show('设备不能为空');
        }
        $Sys_video = new SysVideo();
        $viod_config = $Sys_video->where(['isenable' => 1,'isdelete' => '2'])->find()->toArray();
        if (!$viod_config) {
            show('尚未配置视频，请联系管理员');
        }
        //刷新列表
        $url = $viod_config['url']."?cmdId=100&ip=&user={$viod_config['uname']}&password=&dev";
        $res = file_get_contents($url);
        //组装路径
        $url = $viod_config['url']."?cmdId=210&ip=&user={$viod_config['uname']}&password=&dev=".$dev;
        $res = file_get_contents($url);
        //转数组
        $res = json_decode($res, true);
        file_get_contents("https:".$res['hlsurl']);
       
        if ($res['cmdId'] == '211' && $res['result'] == 0) {
            show(1, '查询成功', ['hlsurl' => 'https:'.$res['hlsurl'] ,'imgsrc' => 'https:'.$res['imgsrc'],'footimg' => $viod_config['image'] ]);
        } else {
            show(0, "查询失败：".video_code($res['result']));
        }
    }

    /**
     * 获取邮寄
     * @param openid
     * @return json
     */
    public function get_user_address()
    {
        $openid = input('openid');
        if (!$openid) {
            show(0, 'openid不能为空');
        }
        $user = $this->user->where(['openid' => $openid])->find();
        if (!$user) {
            show(0, '用户数据不能为空');
        }
    
        $my_address = $this->UserMailAddress->where(['uid' => $user['id']])->order('default', 'ASC')->select();
        if (!$my_address) {
            show(0, '当前用户未设置邮寄地址');
        } else {
            //返回用户所有的收件地址
            show(1, '查询成功', $my_address);
        }
    }

    /**
     * 邮寄地址编辑和添加
     * @param openid
     * @param id
     * @param province
     * @param city
     * @param area
     * @param address
     * @param default
     * @return json
     */
    public function edit_user_address()
    {
        $openid = input('openid');
        $id = input('id');
        $province = input('province');
        $city = input('city');
        $area = input('area');
        $name = input('name');
        $mobile = input('mobile');
        $address = input('address');
        $default = input('default');
        
        if (!$openid) {
            show(0, '缺少openid参数');
        }
        if (!$province) {
            show(0, '缺少省份');
        }
        if (!$city) {
            show(0, '缺少市');
        }
        if (!$area) {
            show(0, '缺少区');
        }
        if (!$address) {
            show(0, '缺少详细地址');
        }
        if (!$name) {
            show(0, '缺少收件人姓名');
        }
        if (!$mobile) {
            show(0, '缺少收件人电话');
        }
        if (!$this->check_string_wx($name)) {
            show(0, '姓名未通过微信安全检测');
        }
        if (!$this->check_string_wx($address)) {
            show(0, '详细地址未通过微信安全检测');
        }
        //用户数据
        $user = $this->user->where(['openid' => $openid])->find();
        if (!$user) {
            show(0, '用户不存在');
        }
        $address_model = $this->UserMailAddress;
        //数据集
        $data = [
            'uid' => $user['id'],
            
            'name' => $name,
            'mobile' => $mobile,
            'city' => $city ,
            'province' => $province,
            'area' => $area ,
            'address' => $address ,
            'default' => $default,
        ];
        //获取所有的邮寄地址，并将地址转换成非默认状态
        if ($default == 1) {
            $ids = $this->UserMailAddress::where('uid', $user->id)->update([ 'default' => 2]);
        }
        
        //修改
        if ($id > 0) {
            $res = $address_model->where(['uid' => $user['id'] ,'id' => $id])->find();
            if (!$res) {
                show(0, '用户当前地址不存在或者被删除');
            }
            //修改当前地址为默认
            $ref = $address_model->where(['uid' => $user['id'] ,'id' => $id])->update($data);
            $ref > 0 ? show(1, '操作成功', $address_model->where(['uid' => $user['id'] ])->order('default', 'ASC')->select()) : show(0, '操作失败');
        } else {
            //新增insert
            $data['create_time'] = $this->time;
            $ref = $address_model->where(['uid' => $user['id']])->insert($data);
            $ref > 0 ? show(1, '操作成功', $this->UserMailAddress->where(['uid' => $user['id']])->order('default', 'ASC')->select()) : show(0, '操作失败');
        }
    }

    /**
     * 删除邮寄地址
     */
    public function delete_user_address()
    {
        if (!$id = input('id')) {
            show(0, '缺少参数');
        }
        if (!$user = $this->user->getByOpenid($this->openid)) {
            show(0, '用户不存在');
        }
        if (!$user_adder = $this->UserMailAddress->where(['id' => $id ,'uid' => $user->id])) {
            show(0, '邮寄地址已删除');
        }
        $this->UserMailAddress->delete($id) > 0 ? show(1, '删除成功', $this->UserMailAddress->where(['uid' => $user['id']])->select()) :show(0, '删除失败');
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
                show(0, '获取ACCESS_TOKEN错误：'.$res['errcode'].",msg:".$res['errmsg']);
            }
        }
        return $redis->get('Wx_access_token');
    }


    /**
     * 用户获取小游戏二维码
     * @param isvpi
     */
    public function get_user_qrcode()
    {
        $openid = input('openid');
        if (!$openid) {
            show(0, 'openid不能为空');
        }
        $user = $this->user->where(['openid' => $openid])->find();
        if (!$user['qrcode']) {
            $user_qrcode = function ($user = [], $access_token = '') {
                try {
                    $data['scene'] = ('uid='.$user['id']);
                    //$data['page'] = ;
                    $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$access_token}";
                    $data = json_encode($data);
                    $ref = curl('POST', $url, ['data'=>$data]);
                    if (json_decode($ref, true)['errcode']) {
                        $errcode = json_decode($ref, true)['errcode'];
                        show(0, '错误信息'.$errcode == 45009 ? '调用分钟频率受限' :
                            $errcode == 41030 ? '所传page页面不存在，或者小程序没有发布':$errcode, $ref);
                    }
                    $oss = new \app\admin\controller\Oss;
                    $base64_img = 'data:image/jpeg;base64,' . chunk_split(base64_encode($ref));
                    $ahref = $oss->base64_upload($base64_img, '.png') ;
                    $this->user->where('id', $user['id'])->update(['qrcode' => $ahref]);
                    return $ahref;
                } catch (Exception $e) {
                    show(0, '错误：', $e->getMessage);
                }
            };
            $ahref = $user_qrcode($user, self::get_access_token());
            show(1, '操作成功', ['qrcode'=> $ahref]);
        } else {
            show(1, '操作成功', ['qrcode'=> $user->qrcode]);
        }
    }

    /**
     * 用户分享
     */
    function user_sharp(){
        if (!$openid = $this->openid) {
            show(0, '缺少参数');
        }
        $user = $this->user->getByOpenid($openid);
        if (!$user) {
            show(0, '用户尚未注册');
        }
        $rule = db('sys_sharp')->alias('a')
                ->join('sys_reward r','r.id = a.reward_id','left')
                ->where(['a.class' => 1])
                ->field('a.id,a.class,a.limit_count,r.name,r.type,r.number')
                ->find();
        //邀请人邀请了多少人加入
        $flow_list = db('user')->where(['fid' => $user->id])->select();
        //邀请人领取了多少次奖励
        $flow_preiz_list = db('user_sharp')->where(['class' => 1,'uid' => $user->id])->select();
        if(!$rule['limit_count'] && count($flow_list) < $ruel['limit_count']){
            //加入分享明细
            db('user_sharp')->insert([
                'uid'           => $user->id,
                'class'         => 1,
                'name'          => '分享一阶段奖励',
                'reward'        => $rule['name'],
                'integral'      => '+'.$ruel['number'],
                'create_time'   => $this->time,
            ]);
            //修改用户积分
            $this->user->where(['id' => $id])->setInc('integral',$ruel['number']);
            show(1,'操作成功',['user' => $this->user->getByOpenid($openid)]);
        }else{
            show(0,'操作失败');
        }

        
    }
    /**
     * 用户领养免费小鸡
     * @param openid
     * @param source
     * @param isvpi
     */
    public function user_adopt_free()
    {
        if (!$openid = $this->openid) {
            show(0, '缺少参数');
        }
        $user = $this->user->getByOpenid($openid);
        if (!$user) {
            show(0, '用户尚未注册');
        }
        $UserChicken_model = new UserChicken();
        $my_chicken = $UserChicken_model->where(['uid' => $user->id ,'source' => 1])->count();
        //获取数据
        $source = input('source'); //1(免费,2购买,3转赠)
        if (!$source) {
            show(0, '尚未选择购买方式');
        }
        $isvpi = input('isvip');
        if ($isvpi !=2) {
            show(0, '选择宠物类型错误');
        }
        //领养免费得
        $UserChicken_model->uid = $user->id;
        $UserChicken_model->is_save = $isvpi;//不在封存状态
        $UserChicken_model->number = '';// 宠物编号
        $UserChicken_model->source = $source;//1(免费,2购买3转赠)
        $UserChicken_model->activity = 100;//活跃值
        $UserChicken_model->health = 100;//健康值
        $UserChicken_model->hunger = 100;//饥饿值
        $UserChicken_model->outegg = 0;//免费产量为0
        $UserChicken_model->cycle  = 1;//一阶段
        $UserChicken_model->level =  1;//等级
        $UserChicken_model->create_time =  $this->time;//创建时间
        if (!$my_chicken) {
            //免费领养
            $source == 1 ? $UserChicken_model->save() : '';
            //更新用户小鸡数量
            $this->user->where(['id' => $user->id])->setInc('chicken', 1);
            show(1, '领取成功', $UserChicken_model->where(['id' => $user->id ,'id'=>$UserChicken_model->id])->find());
        } else {
            //获取系统设置得个数
            if ($my_chicken >= Db::table('raisingchickens_sys_chicken')->field('free_number')->find()['free_number']) {
                show(0, '最多只可领养一只');
            }
            //免费领养
            $source == 1 ? $UserChicken_model->save() : '';
            //更新用户小鸡数量
            $this->user->where(['id' => $user->id])->setInc('chicken', 1);
            show(1, '领取成功', $UserChicken_model->where(['id' => $user->id ,'id'=>$UserChicken_model->id])->find());
        }
    }

   


    /**
     * 用户邀请列表
     * @param openid
     */
    public function get_user_invitation()
    {
        if (!$openid = $this->openid) {
            show('缺少openid参数');
        }
        if (!$user = $this->user->getByOpenid($openid)) {
            show('', '用户数据不存在');
        }
        $user_list = $this->user->where(['fid' => $user->id])->field('id,name,image,openid')->select();
        if (!$user_list) {
            show('', '暂无邀请');
        }
        show(1, '查询成功', $user_list);
    }


    /**
     * 用户邀请
     * @param openid
     * @param inviopenid
     */
    public function user_invitation()
    {
        if (!$openid = $this->openid) {
            show('缺少openid参数');
        }
        if (!$id = input('uid')) {
            show('缺少邀请人openid参数');
        }
        if (!$user = $this->user->getByOpenid($openid)) {
            show('', '用户数据不存在');
        }
        if (!$inviuser = $this->user->where('id', $id)->find()) {
            show('', '被邀请人不存在');
        }
        $res = $this->user->where('id', $inviuser['id'])->update([
            'fid' => $user['id']
        ]);
        if (!$res) {
            show('', '操作失败');
        }
        $rule = db('sys_sharp')->alias('a')
                ->join('sys_reward r','r.id = a.reward_id','left')
                ->where(['a.class' => 2])
                ->field('a.id,a.class,a.limit_count,r.name,r.type,r.number')
                ->find();
        //邀请人邀请了多少人加入
        $flow_list = db('user')->where(['fid' => $id])->select();
        //邀请人领取了多少次奖励
        $flow_preiz_list = db('user_sharp')->where(['class' => 2,'uid' => $id])->select();
        if(count($flow_list) <= $rule['limit_count'] && count($flow_list) < $ruel['limit_count']){
            //加入分享明细
            db('user_sharp')->insert([
                'uid'           => $id,
                'class'         => 2,
                'name'          => '分享二阶段奖励',
                'reward'        => $rule['name'],
                'integral'      => '+'.$ruel['number'],
                'create_time'   => $this->time,
            ]);
            //修改用户积分
            $this->user->where(['id' => $id])->setInc('integral',$ruel['number']);
        }
        show(1, '操作成功', [
            'myinfo' => $user,
            'inviuser' => $inviuser
        ]);
    }


    /**
     * 用户签到
     * @param openid
     */
    public function user_sign()
    {
        if (!$openid = $this->openid) {
            show('缺少openid参数');
        }
        if (!$user = $this->user->getByOpenid($openid)) {
            show('', '用户数据不存在');
        }
        try {
            //用户是否当前签到过
            $ts = date('Y-m-d');
            $UserSign_model  =  new UserSign();
            $today_for_user_sign = Db::query(" SELECT id  FROM raisingchickens_user_sign  WHERE uid = {$user->id} AND create_time LIKE '{$ts}%' ");
            //查询系统签到设置
            $sys_sing = Db::table('raisingchickens_sys_sign')->alias('sys')
                        ->join('raisingchickens_sys_reward rew', 'sys.reward_id = rew.id', 'LEFT')
                        ->field('sys.*,rew.name,rew.type,rew.number')
                        ->where(['isenable' => 1])->select();
            //今天未签到
            $week = get_week();
            $week_list = [];
            if (!$today_for_user_sign) {
                //普通签到
                foreach ($sys_sing as $v) {
                    if ($v['class'] == 1) {
                        //完成每日签到表
                        $user_sing_data = [
                            'uid' => $user['id'],
                            'issign' => 1,
                            'sign_id' => 1,
                            'create_time' => $this->time,
                            'remark' => $v['name'],//奖励备注
                        ];
                        $UserSign_model->save($user_sing_data);
                        //完成每日签到任务表
                        db('user_tanks')->insert([
                            'uid' => $user->id,
                            't_id' => 1,
                            'details_id' => 0,
                            'prize_id' => db('sys_tanks')->where('id', 1)->value('reward_id'),
                            'status' => 4,
                            'create_time' => $this->time,
                        ]);
                        //修改用户积分
                        if ($v['class'] == 1) {
                            //增加积分
                            $this->user->where(['id' => $user['id']])->setInc('integral', $v['number']);
                        } elseif ($v['class']  ==2) {
                            //增加鸡蛋
                            $this->user->where(['id' => $user['id']])->setInc('egg', $v['number']);
                        }
                        //判断是否连续签到
                        if (abs(time()-$user['last_sign_time']) >= 86400) {
                            $this->user->where(['id' => $user['id'] ])->update([
                                'sign' => 1,
                                'last_sign_time' => time()
                            ]);
                            foreach ($week as $w) {
                                $res = Db::query(" SELECT id,remark  FROM raisingchickens_user_sign  WHERE uid = {$user->id} AND create_time LIKE '{$w}%' ");
                                if (!$res) {
                                    array_push($week_list, ['status'=>false ,'reward' => '']);
                                } else {
                                    array_push($week_list, ['status'=>true ,'reward' => $res[0]['remark']]);
                                }
                            }
                            show(1, '签到成功,暂无连续签到,奖励：'.$v['name'], ['userinfo'=> $this->user->getByOpenid($openid) ,'week_list' => $week_list]);
                        } else {
                            //持续登录天数
                            $this->user->where(['id' => $user['id'] ])->setInc('sign');
                            //获取当前系统连续签到奖励
                            foreach ($sys_sing as $vs) {
                                //是否存在连续签到奖励
                                if ($vs['ruleday'] == $user['sign']+ 1 && $vs['class'] == 2) {
                                    //存在则加分
                                    $order_data = [
                                        'uid' => $user['id'],
                                        'issign' => 1,
                                        'sign_id' => $vs['id'],//签到奖励编号
                                        'create_time' => $this->time,
                                        'remark' => $vs['name'],//奖励备注
                                    ];
                                    //加入用户签到
                                    $UserSign_model->save($order_data);

                                    //完成每日签到任务表
                                    db('user_tanks')->insert([
                                        'uid' => $user->id,
                                        't_id' => 1,
                                        'details_id' => 0,
                                        'prize_id' => db('sys_tanks')->where('id', $vs['id'])->value('reward_id'),
                                        'status' => 4,
                                        'create_time' => $this->time,
                                    ]);
                                    
                                    //修改用户积分
                                    if ($vs['class'] == 1) {
                                        //增加积分
                                        foreach ($week as $w) {
                                            $res = Db::query(" SELECT id,remark  FROM raisingchickens_user_sign  WHERE uid = {$user->id} AND create_time LIKE '{$w}%' ");
                                            if (!$res) {
                                                array_push($week_list, ['status'=>false ,'reward' => '']);
                                            } else {
                                                array_push($week_list, ['status'=>true ,'reward' => $res[0]['remark']]);
                                            }
                                        }
                                        $this->user->where(['id' => $user['id']])->setInc('integral', $vs['number']);
                                        show(1, '签到成功,连续签到'.($user['sign']+1).'天', [
                                            'week_list' => $week_list,
                                            'user_sign' => $user['sign'] + 1, //签到天数
                                            'reward' =>  $vs['name'],//奖励
                                            'userinfo'=> $this->user->getByOpenid($openid)
                                        ]);
                                    } elseif ($vs['class']  ==2) {
                                        //增加鸡蛋
                                        foreach ($week as $w) {
                                            $res = Db::query(" SELECT id,remark  FROM raisingchickens_user_sign  WHERE uid = {$user->id} AND create_time LIKE '{$w}%' ");
                                            if (!$res) {
                                                array_push($week_list, ['status'=>false ,'reward' => '']);
                                            } else {
                                                array_push($week_list, ['status'=>true ,'reward' => $res[0]['remark']]);
                                            }
                                        }
                                        $this->user->where(['id' => $user['id']])->setInc('egg', $vs['number']);
                                        show(1, '签到成功,连续签到'.($user['sign']+1).'天', [
                                            'week_list' => $week_list,
                                            'user_sign' => $user['sign'] ?? 0 + 1, //签到天数
                                            'reward' =>  $vs['name'],//奖励
                                            'userinfo'=> $this->user->getByOpenid($openid)
                                        ]);
                                    } 
                                }
                                continue;
                            }

                            foreach ($week as $w) {
                                $res = Db::query(" SELECT id,remark  FROM raisingchickens_user_sign  WHERE uid = {$user->id} AND create_time LIKE '{$w}%' ");
                                if (!$res) {
                                    array_push($week_list, ['status'=>false ,'reward' => '']);
                                } else {
                                    array_push($week_list, ['status'=>true ,'reward' => $res[0]['remark']]);
                                }
                            }

                            //不在系统设定内时间，直接return
                            show(1, '签到成功,连续签到'.($user['sign']+1).'天', [
                                'week_list' => $week_list,
                                'user_sign' => $user['sign'] + 1, //签到天数
                                'reward' => $v['name'],//奖励
                                'userinfo'=> $this->user->getByOpenid($openid)
                            ]);
                        }
                        break ;//结束
                    }
                    continue;
                }
            } else {
                show(0, '当前用户已签到，不能重复签到');
            }
        } catch (\Exception $e) {
            show(
                '',
                "数据异常:",
                [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]
            );
        }
    }

    /**
     * 用户宠物列表
     * @param openid
     */
    public function user_chicken_list()
    {
        if (!$openid = $this->openid) {
            show('缺少参数');
        }
        if (!$user = $this->user->getByOpenid($this->openid)) {
            show(0, '用户不存在');
        }
        $list = Db::name('user_chicken')->where(['uid' => $user['id']])->select();
        if (!$list) {
            show(0, "当前用户没有数据");
        }
        show(1, '查询成功', $list);
    }

    /**
     * 用户证书
     * @param openid
     * @param page
     */
    public function user_certificate_list()
    {
        $page = $_POST['pagesize'] ?? 15 ;
        if (!$this->openid) {
            show(0, '缺少OPENID');
        }
        if (!$user = $this->user->getByOpenid($this->openid)) {
            show(0, '用户不存在');
        }
       
        $UserCertificate = new UserCertificate();
        if (!$UserCertificate->getByUid($user->id)) {
            show(0, '用户暂无证书');
        }
        $list = $UserCertificate->where(['uid' => $user->id])->paginate($page);
        if (!$list) {
            show(0, '暂无证书');
        }
        show(1, '查询成功', $list);
    }

    /**
     * 用户证书查询
     * @param opend
     * @param id 证书编号
     */
    public function user_certificate_seach()
    {
        if (!$id = input('id')) {
            show('', '缺少证书编号');
        }
        if (!$this->openid) {
            show(0, '缺少OPENID');
        }
        if (!$user = $this->user->getByOpenid($this->openid)) {
            show(0, '用户不存在');
        }
        $UserCertificate = new UserCertificate();
        if (!$item = $UserCertificate->where(['uid'=> $user->id,'id' => $id])->find()) {
            show('', '用户证书不存在');
        }
        show(1, '查询成功', $item);
    }


    /**
    * 一键喂养
    * @param openid
    *
    */
    public function one_click_feeding()
    {
        if (!$openid = $this->openid) {
            show('', '缺少OPENID');
        }
        if (!$user = $this->user->getByOpenid($this->openid)) {
            show(0, '用户不存在');
        }
        //小鸡模型
        $UserChicken = new  UserChicken;
        
        //读取用户小鸡
        $user_chicken_list = $UserChicken->where(['uid' => $user->id ,'level' => ['neq',10]])->select();
        if (!$user_chicken_list) {
            show(0, '当前用户没有宠物');
        }
        //循环每次小鸡喂养
        $falg_feed = false;
        foreach ($user_chicken_list as $c) {
           
            if ($ds = date_diff(date_create(explode(' ', $c['last_feed_time'])[0]), date_create(date('Y-m-d')))->d > 0) {
                $res = $save_chek_having = Db::name('user_chicken_having')->insert(
                [   'uid' => $user->id,
                    'c_id' => $c['id'],
                    'name' => '一键喂养',
                    'class'=> 1,
                    'create_time' => $this->time,
                    'date' => date('Y-m-d')
                ]);
                Db::name('user_chicken_having')->insert(
                [
                    'uid'           => $user->id,
                    'c_id'          => $c['id'],
                    'class'         => 4, //增加活跃度
                    'name'          => '减少饥饿值',
                    'hunger'        => 5,
                    'date'          => date('Y-m-d'),
                    'create_time'   => $this->time,
                ]);
                Db::name('user_chicken_having')->insert([
                    'uid'           => $user->id,
                    'c_id'          => $c['id'],
                    'class'         => 5, //增加活跃度
                    'name'          => '增加健康值',
                    'health'        => 5,
                    'date'          => date('Y-m-d'),
                    'create_time'   => $this->time,
                ]);
                
                $falg_feed = true;

                //连续喂养
                if ($ds == 1) {
                    $UserChicken->where('id', $c['id'])->setInc('feed_lx');
                } else {
                    $UserChicken->where('id', $c['id'])->update(['feed_lx'=> 0]);
                }
                //修改上次喂养时间和喂养次数
                $UserChicken->where('id', $c['id'])->setInc('feed_count');
                //修改等级
                if($c['feed_count']-30 > 0){
                    $grade = intval(($c['feed_count'] - 30)/5) + 10;
                    $grade_ratio = floor(($c['feed_count'] - 30)%5/5 * 100);//百分比
                }else{
                    $grade = intval($c['feed_count']/3);
                    $grade_ratio = floor($c['feed_count']%3/3 *100);//百分比
                }
                $UserChicken->where('id', $c['id'])->update(['last_feed_time' => $this->time,'grade' => $grade,'grade_ratio' => $grade_ratio]);
                //修改小鸡状态值hunger
                if ($c['hunger'] - 5 < 0) {
                    db('user_chicken')->where(['id' => $c['id']])->update(['hunger' => 0]);              
                }else{
                    db('user_chicken')->where(['id' => $c['id']])->setDec('hunger', 5);
                }
                if ($c['health'] + 5 >= 100) {
                    db('user_chicken')->where(['id' => $c['id']])->update(['health' => 100]);
                }else{
                    db('user_chicken')->where(['id' => $c['id']])->setInc('health', 5);
                   
                }
            }
        }

        $dt = date('Y-m-d');
        //做任务   喂养
        $log = db('user_tanks')->where(['uid' => $user->id,'create_time' => ['like',"{$dt}%"] ,'t_id' => 8 ])->find();
        if (!$log) {
            //加入记录
            db('user_tanks')->insert([
                'uid' => $user->id,
                't_id' => 8,
                'details_id' => 0,
                'prize_id' => db('sys_tanks')->where('id', 8)->value('reward_id'),
                'status' => 4,
                'create_time' => $this->time,
            ]);
            //查询奖励
            $prize = db('sys_reward')->where('id', db('sys_tanks')->where('id', 2)->value('reward_id'))->find();
            if (!$prize) {
                //不操作
            } else {
                if ($prize['type'] == 1) {
                    //增加用户积分
                    $this->user->where('id', $user->id)->setInc('integral', $prize['number']);
                }
                if ($prize['type'] == 2) {
                    //增加鸡蛋
                    $this->user->where('id', $user->id)->setInc('egg', $prize['number']);
                }
            }
        }

        //用户喂养天数
        if (date_diff(date_create(explode(' ', $user['last_feed_time'])[0]), date_create(date('Y-m-d')))->d > 0) {
            $this->user->where('id', $user->id)->setInc('feed');
            db('user')->where('id', $user->id)->update([
                'last_feed_time' => $this->time,
            ]);
        }

        if ($falg_feed) {
            show(1, '操作成功', $this->user->getByOpenid($openid));
        } else {
            show(0, '您已经喂食过了');
        }
    }


    /**
     * 用户赠送
     * @param openid
     * @param template_id 模板编号
     * @param chicken_id
     */
    public function user_give_friend()
    {
        //校验
        if (!$this->openid) {
            show(0, '缺少OPENID');
        }
        if (!$user = $this->user->getByOpenid($this->openid)) {
            show(0, '用户不存在');
        }
        //echo db('user_chicken')->where(['uid' =>  $user->id ,'isvip' => 1])->count('id'); die;
        if (db('user_chicken')->where(['uid' =>  $user->id ,'isvip' => 1])->count('id') < 2) {
            show(0, '证书已生成，无法赠送');
        }
        if (!$number = input('number')) {
            show('', '数量不能为0');
        }
        if (!$template_id = input('template_id')) {
            show(0, '缺少模板Id');
        }
        if (!$template_content = input('content')) {
            show(0, '缺少文案');
        }
        if (!self::check_string_wx($template_content)) {
            show(0, '文字内容无法通过微信安全检测，请重新输入');
        }
        
        //用户全部小鸡,并且证书未生成过的
        $my_chicken_list = db('user_chicken')
                ->alias('c')
                ->join('user_certificate uc', 'c.identifier = uc.identifier', 'LEFT')
                ->where(['c.uid' => $user->id ,'c.isvip' => 1 ,'uc.name' => ''])
                ->column("c.number");
        
        //未生成证书的小鸡，小于要赠送的数量，则返回
        if (count($my_chicken_list) < $number) {
            show('', '当前暂无可赠送宠物');
        }
       
        //交易中的不能再赠送
        $str_arr = implode("','", $my_chicken_list);
        $str_arr = "'".$str_arr."'";

        //正在交易中的宠物数据,status = 1交易中 2 已领取，3已过期
        $trading_list = db('user_chicken_order')->where("uid = {$user->id} and status = 1 and  number in ({$str_arr}) and ABS(unix_timestamp(create_time) - unix_timestamp(now())) < 86400 ")->select();
        
        $my_chicken_list = db('user_chicken')
        ->alias('c')
        ->join('user_certificate uc', 'c.identifier = uc.identifier', 'LEFT')
        ->where(['c.uid' =>  $user->id ,'c.isvip' => 1,'uc.name' => '','c.give_name' => '' ])
        ->field('c.id,c.number,c.name')
        ->select();
        
        if ($my_chicken_list < $number) {
            show(0, '当前正在交易的有'.count($trading_list).'只');
        }
        
        //剔除掉小鸡列表,如果不足选择条数则返回
        if ($trading_list) {
            foreach ($my_chicken_list as $k => $v) {
                //存在交易中
                if($trading_list){
                    foreach ($trading_list as $t) {
                        if ($v['number'] == $t['number']) {
                            unset($my_chicken_list[$k]);
                        }
                    }
                }
            }
        }
        if (!$my_chicken_list) {
            show('', '当前暂无可赠送宠物',$my_chicken_list);
        }
        //截取number条
        $give_list = array_splice($my_chicken_list, 0, $number);
    
        $user_chicken_order ;
        $mychickend;

        foreach ($give_list as $d) {
            $user_chicken_order[] = [
                'uid' => $user['id'],
                'number'=> $d['number'],
                'name' =>  $d['name'],
                'status' => 1,//交易状态  1赠送 2接受 3失效
                'template_id' => $template_id, //模板编号
                'content' => $template_content,//模板文案
                'isvip' => 1,
                'source' => 3,//转赠
                'create_time' => $this->time,
            ];
            $mychickend[] = $d['number'];
        }
        $mychickend  = base64_encode(serialize($mychickend));
        //生成链接地址
        if ($user_chicken_order) {
            $falg = Db::name('user_chicken_order')->insertAll($user_chicken_order);
            $url = urlencode(config('game_url')."?uid={$user['id']}&chickend_sn={$mychickend}");
          
            show(1, '用户赠送宠物成功', ['url'=>$url]);
        } else {
            show(0, '生成赠送订单失败');
        }
    }

    /**
     * 用户能否赠送
     * @param openid 
     */
    function user_give_friend_status(){
        //校验
        if (!$this->openid) {
            show(0, '缺少OPENID');
        }
        if (!$user = $this->user->getByOpenid($this->openid)) {
            show(0, '用户不存在');
        }
        //echo db('user_chicken')->where(['uid' =>  $user->id ,'isvip' => 1])->count('id'); die;
        if (db('user_chicken')->where(['uid' =>  $user->id ,'isvip' => 1])->count('id') < 2) {
            show(0, '证书已生成，无法赠送');
        }
        
        //用户全部小鸡,并且证书未生成过的
        $my_chicken_list = db('user_chicken')
                ->alias('c')
                ->join('user_certificate uc', 'c.identifier = uc.identifier', 'LEFT')
                ->where(['c.uid' => $user->id ,'c.isvip' => 1 ,'uc.name' => ''])
                ->column("c.number");
        
        //未生成证书的小鸡，小于要赠送的数量，则返回
        if (count($my_chicken_list) < 1) {
            show('', '当前暂无可赠送宠物');
        }
       
        //交易中的不能再赠送
        $str_arr = implode("','", $my_chicken_list);
        $str_arr = "'".$str_arr."'";

        //正在交易中的宠物数据,status = 1交易中 2 已领取，3已过期
        $trading_list = db('user_chicken_order')->where("uid = {$user->id} and status = 1 and  number in ({$str_arr}) and ABS(unix_timestamp(create_time) - unix_timestamp(now())) < 86400 ")->select();
        
        $my_chicken_list = db('user_chicken')
        ->alias('c')
        ->join('user_certificate uc', 'c.identifier = uc.identifier', 'LEFT')
        ->where(['c.uid' =>  $user->id ,'c.isvip' => 1,'uc.name' => '','c.give_name' => '' ])
        ->field('c.id,c.number,c.name')
        ->select();
        
        if ($my_chicken_list < 1) {
            show(0, '当前正在交易的有'.count($trading_list).'只');
        }
        
        //剔除掉小鸡列表,如果不足选择条数则返回
        if ($trading_list) {
            foreach ($my_chicken_list as $k => $v) {
                //存在交易中
                if($trading_list){
                    foreach ($trading_list as $t) {
                        if ($v['number'] == $t['number']) {
                            unset($my_chicken_list[$k]);
                        }
                    }
                }
            }
        }
        if (!$my_chicken_list) {
            show('', '当前暂无可赠送宠物',$my_chicken_list);
        }
        //随机截取1条，如果有则校验成功,否则失败
        $give_list = array_splice($my_chicken_list, 0, 1);
        if($give_list){
            show(1,'可以赠送');
        }else{
            show(0,'暂无可赠送');
        }
    }
    
    /**
     * @param chickend_sn 编号
     */
    function get_agree_text(){
        if(!$chickend_sn = input('chickend_sn')) show('','缺少chickend_sn参数');
        $data = unserialize(base64_decode($chickend_sn));
        $text = db('user_chicken_order')->where(['number' => $data[0] ,'status' => 1])->field('content')->find()['content'];
        if(!$text) show(0,'不存在');
        show(1,'查询成功',$text);
    }
    /**
     * 用户接受宠物
     * @param openid  string
     * @param chickend_sn string
     * @param uid   int
     * @param agree int
     */
    public function user_agree_friend()
    {
        //获取参数、校验参数
        if (!$agree = input('agree')) {
            show(0, '用户不同意');
        }
        if (!$this->openid) {
            show(0, '缺少OPENID');
        }
        if (!$user = $this->user->getByOpenid($this->openid)) {
            show(0, '当前用户未授权');
        }
        if (!$uid = input('uid')) {
            show(0, '缺少赠送人编号');
        }
        if (!$guser = $this->user->where('id', $uid)->find()) {
            show(0, '赠送人不存在');
        }
        if (!$chickend_sn = input('chickend_sn')) {
            show(0, '缺少参数');
        }
        if ($uid == $user->id) {
            show(0, '自己不能赠送自己');
        }
        if (!$name = input('name')) {
            show(0, '未输入名字');
        }
        if (!$mobile = input('mobile')) {
            show(0, '未输入手机号');
        }
        if (!$address = input('address')) {
            show(0, '未输入地址');
        }
        if (!$number = input('chickend_sn')) {
            show('', '缺少宠物信息');
        }
        if(!$this->check_string_wx($name) or !$this->check_string_wx($address))
        {
            show('','姓名或地址未通过微信安全检测,请重新填写');
        }
        // if(!$template = input('class')) show(0,'缺少模板编号');
        //反序列化
        $numbers = unserialize(base64_decode($number));
        $fail  = 0;
        $success = 0;
        Db::startTrans();
        try {
            //证书编号,证书图片地址
            //$std_number = create_certificate_number();
            $images = Db::name('sys_certificate')->where(['isenable' => 1,'isdelete' => 2])->field('image')->find();
           
            foreach ($numbers as $key => $v) {
                //校验是否再1天内赠送，以及是否被其他用户领取
                $check_give_data = db('user_chicken_order')->where("uid = {$uid} and number ='{$v}' and status = 1 and ABS(unix_timestamp(create_time) - unix_timestamp(now())) < 86400 ")->find();
                if (!$check_give_data) {
                    $fail++;
                    continue;
                }

                //接受宠物
                Db::name('user_chicken_order')->where('number', $v)->update([
                    'status' => 2,
                    'give_id' => $user->id,
                    'is_accept' => 1,//同意
                    'accept_time' => $this->time,
                ]);
            
                //并只生成一张证书
              
                Db::table('raisingchickens_user_certificate')->where(['number' => $v])->update([
                    'uid' => $user->id ,
                    'name' => $name ,
                    'imageurl' => $images['image'],
                ]);
               

                //查询模板编号
                // $template = db('sys_freetemplate')->where(['id'=> $v['template_id']])->find();
                //转入自己名下,小鸡头顶XX赠送标识
                $give_name;
                switch($check_give_data['template_id']){
                    case 1 :
                        $give_name = '家人';
                    break;
                    case 2 :
                        $give_name = '朋友';
                    break;
                    case 3:
                        $give_name = '同事';
                    break;
                    default:
                        $give_name = '暂无关系';
                }
                $give_name .= $guser['name'] ?? $guser['nickname'];
                //$give_name = $check_give_data['template_id'] == 1 ? "家人" :$check_give_data['template_id'] == 2 ?"朋友": $check_give_data['template_id'] == 3 ? "同事":"暂未选择关系".($guser['name'] ?? $guser['nickname']);
                
                Db::name('user_chicken')->where(['number' => $v])->update([
                    // 'tips' => 2,
                    'uid' => $user['id'],
                    'give_name' => '您的'.$give_name.'赠送的小鸡',
                ]);

                //修改自己地址，手机号码，姓名,增加宠物数量
            $my_info = $this->user->where('id', $user['id'])->setInc('chicken');//默认给自己+1
            // $my_info = $this->user->where('id', $user['id'])->update([
            //     'name' => $name,
            //     'address' => $address,
            //     'mobile' => $mobile
            // ]);
          
                //给赠送用户-1
                $guser_info = $this->user->where('id', $guser->id)->setDec('chicken');
                $success ++;
                unset($check_give_data);
                unset($give_name);

            }
            Db::commit();
            if ($fail == count($numbers)) {
                show(0, '操作失败,分享宠物已被领取');
            }
            $info = db('user_certificate')->where(['number' => $numbers[0]])->find();
            show(1, '操作成功', ['identifier'=> $info['identifier'],'name' => $name]);
        } catch (\Exception $e) {
            Db::rollback();
            show(0, '操作失败'.$e->getMessage().',出现行'.$e->getLine());
        }
    }

    /**
     * 用户关闭提示
     * @param openid
     */
    public function user_off_tips()
    {
        if (!$this->openid) {
            show('缺少OPENID');
        }
        if (!$user= $this->user->getByOpenid($this->openid)) {
            show(0, '用户数据不存在');
        }
        //默认关闭购买小鸡成功弹窗
        if (!$class = input('class')) {
            if (!$my_chicken = Db::name('user_chicken')->where(['uid' =>$user['id'] ])->find()) {
                show(0, '小鸡不存在');
            }
            $ref = Db::name('user_chicken')->where(['uid' =>$user->id])->update([
                'tips' => 1,
            ]);
            $list = Db::name('user_chicken')->where(['uid' =>$user['id']])->select();
            $ref > 0 ? show(1,'成功', $list): show(0, '失败');
        }elseif($class == 1){
            $ids = input('id');
            if(!$ids) show(0,'缺少弹窗编号');
            if(!$window_tips = db('user_chicken_order')->where(['id' => $ids ,'status' => 2, 'is_tips' => 2])) show('','此窗口已关闭');
            db('user_chicken_order')->where('id',$ids)->update([
                'is_tips' => 1,
                'tips_time' => $this->time,
            ]) ? show(1,'操作成功') : show(0,'操作失败');
        }else{
            show('0','参数错误');
        }
    }

    /**
     * 用户拣蛋
     * @param openid
     * @param id
     */
    public function user_pickup_egg()
    {
        if (!$id = input('id')) {
            show(0, '缺少参数');
        }
        if (!$openid = $this->openid) {
            show(0, '缺少OPNEID');
        }
        if (!$user = $this->user->getByOpenid($openid)) {
            show(0, '用户不存在', $user);
        }
        //验证此宠物是否存在 ,并且是否有下蛋。
        try {
            if (
                !$chick_en_egg = db('user_chicken_having')->where(['id'=>$id ,'uid' => $user->id ,'class' => 2 ,'ispickup' => 0])->find()
            ) {
                show('', '宠物不存在或者小鸡没有下蛋,无法拾取');
            }
            $system_out_egg = db('sys_chicken')->where('id', 1)->field('out_egg')->find();
            $user_egg_count = db('user')->sum('egg');
            if ($user_egg_count >= $system_out_egg) {
                show(0, '拣取失败,超出系统最大值');
            }
            //加入日志
            db('user_chicken_log')->insert([
                'uid' => $user->id,
                'name' => '用户拾蛋',
                'c_id' => $chick_en_egg['c_id'],
                'create_time' => $this->time,
            ]);
            //修改小鸡行为
            db('user_chicken_having')->where('id', $id)->setInc('ispickup');
            //修改用户鸡蛋数量
            db('user')->where('id', $user->id)->setInc('egg') > 0 ? show(1, '拣取成功', $this->user->getByOpenid($openid)): show(0, '拣取失败');
        } catch (\Exception $e) {
            show(0, '失败', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }

    // function test(){
    //     $str = '相传古时候，在古印度和中国之间的海岛上，有一个萨桑王国，国王名叫山努亚。山努亚国王每天要娶一个女子来，在王宫过夜，但每到第二天雄鸡高唱的时候，便残酷地杀掉这个女子。
    //     这样年复一年，持续了三个年头，整整杀掉了一千多个女子。
    //     百姓在这种威胁下感到恐怖，纷纷带着女儿逃命他乡，但国王仍然只顾威逼宰相，每天替他寻找女子，供他取乐、虐杀。整个国家的妇女，有的死于国王的虐杀，有的逃之夭夭，城里十室九空，以至于宰相找遍整个城市，也找不到一个女子。他怀着恐惧、忧愁的心情回到相府。
    //     宰相有两个女儿，长女叫桑鲁卓，二女儿名叫多亚德。桑鲁卓知书达礼，仪容高贵，读过许多历史书籍，有丰富的民族历史知识。她收藏有上千册的文学、历史书籍。见到宰相忧郁地回到家中，桑鲁卓便对他说：
    //     “爸爸！您为了何事愁眉不展，为什么忧愁烦恼呢？”';
    //     if(!$this->check_string_wx($str))
    //     {
    //         show('','姓名或地址未通过微信安全检测,请重新填写');
    //     }
    // }
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
     * 修改证书
     * @param id 
     * @param name ;
     */
    public function edit_certificate(){
        if(!$id = input('id')) show(0,'缺少证书编号');
        if(!$name = input('name')) show(0,'缺少证书名称');
        if(!$this->check_string_wx($name)) show(0,'证书名称未通过微信安全检测,请重新输入');
        if (!$this->openid) {
            show('缺少OPENID');
        }
        if (!$user= $this->user->getByOpenid($this->openid)) {
            show(0, '用户数据不存在');
        }
        
        if($user_certificate = db('user_certificate')->where(['id' => $id, 'name'=>'', 'uid' => $user->id ])->find()){
            //修改
            if($this->check_string_wx($name)){
                $ref = db('user_certificate')->where(['id' => $id])->update([
                    'name'  => $name,
                ]);
                if($ref > 0){
                    show(1,'操作成功',$list = db('user_certificate')->where('uid',$user->id)->select());
                }else{
                    show(0,'操作失败');
                }
            }
        }else{
            show(0,'证书以生成');
        }

    }
}
