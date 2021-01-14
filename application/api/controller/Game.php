<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
use app\api\model\Shop;
use app\api\model\SysSign;
use app\api\controller\Userinfo;
//系统基础配置
class Game extends  Controller{
   
    /**
     * 系统签到配置
     */
    function system_sign(){
        $openid = input('openid');
        if(!$openid ) show('','缺少参数');
        if(!$user = Db::name('user')->where('openid',$openid)->find()) show('','用户不存在');
        $sys_sign = Db::name('sys_sign')
                    ->alias('s')
                    ->join('sys_reward r','s.reward_id = r.id','LEFT')
                    ->field('s.class,r.name,s.rulename,s.ruleday')
                    ->where('s.isenable = 1')
                    ->select();
        if(!$sys_sign) show(0,'系统暂未设置签到奖励');
        //查询自己本周签到情况
        $ts = date('Y-m-d');
        $week = get_week();
        $week_list = [];
        foreach ($week as $v){
            $query = Db::query("SELECT id,remark  FROM raisingchickens_user_sign  WHERE uid = {$user['id']} AND create_time LIKE '{$v}%'");
            if(!$query){
                array_push($week_list,['status'=>false ,'reward' => null]);
            }else{
                array_push($week_list,['status'=>true ,'reward' => $query[0]['remark']]);
            }
        }
        show(1,'查询成功',[
            'systemsign' => $sys_sign,
            'week_list'  => $week_list
        ]);
    }

    /**
     * 系统任务配置
     * @param openid
     */
    function system_tanks(){
        $openid = input('openid');
        if(!$openid ) show('','缺少参数');
        if(!$user = Db::name('user')->where('openid',$openid)->find()) show('','用户不存在');
        //查看当前任务情况
        $sys_tanks = Db::name('sys_tanks')
                    ->alias('s')
                    ->join('sys_reward r','s.reward_id = r.id','LEFT')
                    ->field('s.id,s.class,s.name,s.video,s.see_time,r.name as reward_name')
                    ->where('s.isenable = 1 and s.isdelete != 1')
                    ->select();
        if(!$sys_tanks) show(0,'系统暂未设置任务');
         //查询自己今天任务是否完成
         $ts = date('Y-m-d');
         foreach($sys_tanks as $k => $v){
            $query = Db::query("SELECT id  FROM raisingchickens_user_tanks  WHERE  uid = {$user['id']} AND create_time LIKE '{$ts}%' AND t_id= {$v['id']} ");
            if(!$query){
                //没做过任务
                $sys_tanks['status'] = 0;
            }else{
                //做过任务
                $sys_tanks['status'] = 1;
            }
         }
        show(1,'查询成功',$sys_tanks);
    }

    /**
     * 系统商城配置
     */
    function system_shop(){
        $Shop_model = new Shop();
        $list = $Shop_model ->all();
        if(!$list) show(0,'积分商城暂无兑换商品');
        show(1,'查询成功',$list);
    }

    /**
     * 系统观看视频列表配置
     */
    function system_video(){
        
    }

    /**
     * 图片上传
     * @param image base64图片资源
     */
    function upload_image(){
        $base64_img = input('image');
        if(!$base64_img) show('','缺少图片资源');
        //检测
        $Userinfo_c = new Userinfo();
        $accesstoken = $Userinfo_c->get_access_token();
        $url = "https://api.weixin.qq.com/wxa/img_sec_check?access_token={$accesstoken}";
        $base64 = str_replace(" ", '+', $base64_img);
        $base64 = str_replace('data:image/jpeg;base64,', '', $base64);
        $base64 = str_replace('data:image/jpg;base64,', '', $base64);
        $base64 = str_replace('data:image/png;base64,', '', $base64);
        $base64 = base64_decode($base64);
        file_put_contents('temp', $base64);
        $data['data']['media'] = new \CURLFile('temp','image/jpeg','file');
        $res =json_decode(curl('POST', $url, $data),true) ;
        if ($res['errcode'] == 0 and $res['errmsg'] == 'ok') {
            $oss = new \app\admin\controller\Oss;
            $ahref = $oss->base64_upload($base64_img, '.png') ;
            //显示获得的数据
            show(1, '上传成功', ['url' => $ahref]);
        }
        show(0,'图片未通过微信安全检测');
    }

    /**
     * 出栏查询
     */
    function select_out_egg(){
        $row = db('sys_chicken_level')->where('class',4)->field('day')->find();
        if($row) show(1,'查询成功',$row);
        show(0,'查询失败，暂未设置出栏查询');
    }

    /**
     * 查询邮费
     */
    function freight(){
        $row = db('applet_goods')->where('id',1)->find();
        if($row) show(1,'查询成功',
        [
            'is_freight' => $row['is_freight'],
            'freight' => $row['shipping_money'],
            "freight_city" => $row['freight_city'],
        ]);
        show(0,'请联系管理员');
    }
    
    function cs(){
        phpinfo();
    }
}