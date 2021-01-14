<?php
namespace app\api\controller;
use think\Controller;
use app\api\model\User;

class Userintegral extends Controller
{
    /**
     * 积分明细
     */
    public function user_integral()
    {
        if(!$openid = input('openid')) show(0,'缺少OPENID');
        $user_model = new User();
        if(!$userinfo = $user_model->getByOpenid($openid)) show(0,'当前用户尚未注册');
        if($seach_time = input('time'))
        $list;
        $sharp_data;
        $user_tanks_list;
        if(!$seach_time){
            //答题
            $list = db('user_do_answer')
                    ->alias('a')
                    ->join('user u', 'a.uid = u.id ', 'LEFT')
                    ->join('answer ans','a.answer_id = ans.id','LEFT')
                    ->field('a.id,u.nickname,a.integral as integral_new,u.name,u.openid,u.avatar,a.answer_title,a.answer_class_name,a.status,a.create_time')
                    ->where(['a.uid' => $userinfo->id,'a.status'=>1])
                    ->order('a.create_time','DESC')
                    ->paginate(15, false,['query'=>$_POST]);
            //任务
            $user_tanks_list = db('user_tanks')
                    ->alias('ut')
                    ->join('sys_tanks t','t.id = ut.t_id','LEFT')
                    ->join('sys_reward r','r.id = ut.prize_id','LEFT')       
                    ->field('if(t.name is null ,"该任务已下线",t.name) tkname,r.name rwname,ut.create_time')
                    ->where(['ut.uid' => $userinfo->id])
                    ->order('ut.create_time','DESC')
                    ->paginate(15, false,['query'=>$_POST]);
            //分享
            $user_sharp = db('user_sharp')
                    ->field('class,name,integral,create_time')
                    ->where('uid',$userinfo->id)
                    ->order('create_time','DESC')
                    ->paginate(15, false,['query'=>$_POST]);
        }else{
            //答题
            $list = db('user_do_answer')
                    ->alias('a')
                    ->join('user u', 'a.uid = u.id ', 'LEFT')
                    ->join('answer ans','a.answer_id = ans.id','LEFT')
                    ->field('a.id,u.nickname,a.integral as integral_new,ans.integral,u.name,u.openid,u.avatar,a.answer_title,a.answer_class_name,a.status,a.create_time')
                    ->where(['a.uid' => $userinfo->id,'a.create_time' => ['like',"{$seach_time}%"],'a.status'=>1])
                    ->order('a.create_time','DESC')
                    ->paginate(15, false,['query'=>$_POST]);
             //任务
             $user_tanks_list = db('user_tanks')
                    ->alias('ut')
                    ->join('sys_tanks t','t.id = ut.t_id','LEFT')
                    ->join('sys_reward r','r.id = ut.prize_id','LEFT')       
                    ->field('if(t.name is null ,"该任务已下线",t.name) tkname,r.name rwname,ut.create_time')
                    ->where(['ut.uid' => $userinfo->id,'ut.create_time' => ['like',"{$seach_time}%"]])
                    ->order('ut.create_time','DESC')
                    ->paginate(15, false,['query'=>$_POST]);
            //分享
            $user_sharp = db('user_sharp')
                    ->field('class,name,integral,create_time')
                    ->where(['uid'=> $userinfo->id, 'create_time' => ['like',"{$seach_time}%"],])
                    ->order('create_time','DESC')
                    ->paginate(15, false,['query'=>$_POST]);
        }
        $answer_data = array();
        foreach ($list->all() as $v){
             if (!$v['integral_new']) {
                 if(isset($v['integral'])){
                    $answer_data [] =[
                        'tkname' => '答题',
                        'rwname' => '+'.$v['integral'].'积分',
                        'create_time' => $v['create_time'],
                    ];
                 }
            }else{
                $answer_data [] =[
                    'tkname' => '答题',
                    'rwname' => '+'.$v['integral_new'].'积分',
                    'create_time' => $v['create_time'],
                ];
            }
        }
        
        $sharp_data = array();
        foreach($user_sharp->all() as $v){
            $sharp_data [] =[
                'tkname' => $v['name'],
                'rwname' => $v['integral'].'积分',
                'create_time' => $v['create_time'],
            ];
        }
        $data;
        // $data = array_merge_recursive($user_tanks_list->all(),$answer_data,$sharp_data);
        if (!$answer_data && !$sharp_data && !$user_tanks_list->all()) {
            show(0,'暂无数据');
        }elseif($answer_data && !$sharp_data && !$user_tanks_list->all()){
            show(1,'查询成功',$answer_data);
        }elseif($answer_data && $sharp_data && !$user_tanks_list->all()){
           $data = array_merge_recursive($answer_data,$sharp_data);
            //根据时间排序
            $key ;
            foreach ($data as $v){
                $key[] = $v['create_time'];
            }
            array_multisort($key, SORT_DESC ,SORT_STRING , $data);
            show(1,'查询成功',$data);
        }elseif($answer_data && !$sharp_data && $user_tanks_list->all()){
            $data = array_merge_recursive($answer_data,$user_tanks_list->all());
            //根据时间排序
            $key ;
            foreach ($data as $v){
                $key[] = $v['create_time'];
            }
            array_multisort($key, SORT_DESC ,SORT_STRING , $data);
            show(1,'查询成功',$data);
        }elseif($answer_data && $sharp_data && $user_tanks_list->all()){
            $data = array_merge_recursive($user_tanks_list->all(),$answer_data,$sharp_data);
            //根据时间排序
            $key ;
            foreach ($data as $v){
                $key[] = $v['create_time'];
            }
            array_multisort($key, SORT_DESC ,SORT_STRING , $data);
            show(1,'查询成功',$data);
        }elseif(!$answer_data && $sharp_data && !$user_tanks_list->all()){
            show(1,'查询成功',$sharp_data);
        }elseif(!$answer_data && !$sharp_data && $user_tanks_list->all()){
            show(1,'查询成功',$user_tanks_list->all());
        }elseif(!$answer_data && $sharp_data && $user_tanks_list->all()){
            $data = array_merge_recursive($user_tanks_list->all(),$sharp_data);
            //根据时间排序
            $key ;
            foreach ($data as $v){
                $key[] = $v['create_time'];
            }
            array_multisort($key, SORT_DESC ,SORT_STRING , $data);
            show(1,'查询成功',$data);
        }else{
            show(0,'暂无数据');
        }
    }

    /**
     * 优惠券转入列表
     */
    function coupon_list(){
        if(!$openid = input('openid')) show(0,'缺少OPENID');
        $user_model = new User();
        if(!$userinfo = $user_model->getByOpenid($openid)) show(0,'当前用户尚未注册');
        if(!$seach_time = input('time')){
            $list = db('shop_order')
                    ->alias('o')
                    ->join('shop_goods g','g.id = o.goods_id','left')
                    ->where(['o.user_id' => $userinfo->id,'status' => 1])
                    ->field('g.name as tkname,o.create_time')
                    ->paginate(15,false,['query'=>$_POST])->each(function($v,$k){
                        $v['rwname'] ='';
                        $v['tkname'] = $v['tkname'] ?? '优惠券已下架';
                        $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
                        return $v;
                    });
        }else{

            //转换时间 
            //$start =  strtotime($seach_time);
            $BeginDate=date('Y-m-01', strtotime($seach_time.'-01'));
            $end = date('Y-m-d', strtotime("$BeginDate +1 month -1 day"));

            $list = db('shop_order')
                    ->alias('o')
                    ->join('shop_goods g','g.id = o.goods_id','left')
                    ->where(['o.user_id' => $userinfo->id,
                                'status' => 1,
                                'o.create_time' => ['between',[strtotime($BeginDate),strtotime($end)]
                            ]])
                    ->field('g.name as tkname,o.create_time')
                    // ->fetchSql(true)
                    // ->select();
                    ->paginate(15,false,['query'=>$_POST])->each(function($v,$k){
                        $v['rwname'] ='';
                        $v['tkname'] = $v['tkname'] ?? '优惠券已下架';
                        $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
                        return $v;
                    });
        }
        if($list->all()) show(1,'查询成功',$list->all());
        show(0,'暂无数据');      
     }
}
