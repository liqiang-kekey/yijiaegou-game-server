<?php
namespace app\api\controller;
use app\api\model\SysTanks;
use think\Controller;
use think\Db;
use app\api\model\User;
use app\api\model\UserTanks;
/**
 * 任务
 * 
 */
class Tanks extends Controller
{
    //定义内部变量
    protected $SysTanks,$openid,$user,$id,$time;
    function _initialize(){
        $SysTanks = $this->SysTanks = new SysTanks();
        $openid = $this->openid = input('openid');
        //任务编号
        $time = $this->time = date('Y-m-d H:i:s');
        $id = $this->id = input('id');
        $user = $this->user = new User();
        if(!$openid ) show(0,'用户未授权');
    }

    /**
     * 获取系统设置任务列表
     * @return json
     */
    public function get_tanks_list()
    {   
        $dt =  date('Y-m-d');
        if(!$user = $this->user->getByOpenid($this->openid)) show(0,'用户不存在');
        //读取任务配置列表
        $list = Db::query(" select t.id,t.class,t.name,t.see_time,r.name as rname from raisingchickens_sys_tanks  as t left join raisingchickens_sys_reward r on r.id = t.reward_id where t.isenable =1  order  by t.id");
        if(!$list) show(0,'尚未配置任务');
        foreach($list as $k => $v){
            $my_tank =  db('user_tanks')->where(['uid' => $user['id'],'t_id' => $v['id'],'create_time' => ['like',"{$dt}%"] ])->find();
            
            if(!$my_tank){
                $list[$k]['isdo'] = 0; //已做
            }else{
                $list[$k]['isdo'] = 1; //未做过
            }
        }
        return show(1,'查询成功',$list);
    }

    /**
     * 用户做任务
     * @param id        任务编号
     * @param openid    openid
     * @param longtime  观看时长
     */
    function user_do_tanks(){
        if(!$this->openid) show('缺少OPENID');
        if(!$this->id) show('缺少任务参数');
        if(!$user = $this->user->getByOpenid($this->openid)) show('用户不存在');
        if(!$tanks = $this->SysTanks->getById($this->id)) show('任务未开启');
        $userTanks = new UserTanks();
        $today = date('Y-m-d');
        $exites_do_tanks = $userTanks->where(['uid' => $user->id ,'t_id'=>$this->id,'create_time' => ['like' ,"{$today}%"] ])->find();
        $reward = Db::table('raisingchickens_sys_reward')->where(['id' =>$tanks['reward_id']])->find();
        if($this->id  == 3 || $this->id == 2 || $this->id ==10){
            $see_time_long = input('longtime');
            if(!$see_time_long) show(0,'缺少观看时长');
            if($tanks['see_time'] > $see_time_long) show(0,'观看时长不够');
        }
        if(!$exites_do_tanks){
            //完成任务
            $reward = Db::table('raisingchickens_sys_reward')->where(['id' =>$tanks['reward_id']])->find();
            if(!$reward) show(0,'该任务暂未设置奖励,无法完成');
            $userTanks->uid = $user->id;
            $userTanks->t_id = $tanks->id;
            $userTanks->prize_id = $reward['id'];
            $userTanks->create_time = $this->time;
            $userTanks->status = 4;//已完成
            $userTanks->save();
            $ref = 0;
            //增加积分奖励
            if($reward['type'] == 1){
                $ref = $this->user->where(['id' => $user['id']]) ->setInc('integral',$reward['number']);
            }
            //增加鸡蛋奖励
            if($reward['type'] == 2){
                $ref  = $this->user->where(['id' => $user['id']]) ->setInc('egg',$reward['number']);
            }
            //返回任务列表
            $sys_tanks = Db::name('sys_tanks')
                    ->alias('s')
                    ->join('sys_reward r','s.reward_id = r.id','LEFT')
                    ->field('s.id,s.class,s.name,s.video,s.see_time,r.name as reward_name')
                    ->where('s.isenable = 1 and s.isdelete != 1')
                    ->select();
            if(!$sys_tanks) show(0,'系统暂未设置任务');
            //查询自己今天任务是否完成
            $ts = date('Y-m-d');
            foreach($sys_tanks as $k =>$v){
                $query = Db::query("SELECT id  FROM raisingchickens_user_tanks  WHERE  uid = {$user['id']} AND create_time LIKE '{$ts}%' AND t_id= {$v['id']} ");
                if(!$query){
                    //没做过任务
                    $sys_tanks[$k]['status'] = 0;
                }else{
                    //做过任务
                    $sys_tanks[$k]['status'] = 1;
                }
            }
            $ref > 0 ?show(1,'操作成功',['user'=>$this->user->getById($user->id) ,'systemtanks'=> $sys_tanks]) : show('','操作失败');
        }   
        show('','您已经完成过了，请不要重复提交');
    }

    /**
     * 任务商城
     * @param openid
     * 
     */
    function tanks_shop(){
        if(!$this->user) show('','用户不存在') ;
        if(!$this->openid) show('','缺少参数');
        if($page = input('page')) $page = 1;
        $column = 'id,name,thumb,price,moth_price,appid,minipage';
        if(!$hot = input('hot')) {
            $list = db('tanks_shop')->where(['isdelete' => 2])->field($column)->order('create_time','DESC')->paginate($page);
        }else{
            $list = db('tanks_shop')->field($column)->order('asc','asc')->paginate($page);
        }
        show(1,'查询成功',$list);
    }

 
    /**
     * 任务视频
     * @param openid
     */
    function tanks_vidoe(){
        if(!$this->user) show('','用户不存在') ;
        if(!$this->openid) show('','缺少参数');
        if($page = input('page')) $page = 1;
        $column = 'id,title,image,price,vurl,appid,minipage';
        $list = db('tanks_video')->field($column)->order('create_time','DESC')->paginate($page);
        show(1,'查询成功',$list);
    }
}