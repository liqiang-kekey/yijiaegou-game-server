<?php
/**
 * Created by PhpStorm.
 * User: yaodunyuan
 * Date: 2020/8/11
 * Time: 14:54
 */

namespace app\admin\controller;

use app\common\service\AdminBase;

class Data extends AdminBase
{
    /*
     * 2020/08/11
     * 注册日志
     * */
    public function userinfo()
    {
        $where = [];
        if (!empty($_GET['time1'])) {
            $where['create_time'] = array('like',$_GET['time1'].'%');
            $this->assign('time1', $_GET['time1']);
        }
        if (!empty($_GET['time2'])) {
            $where['create_time'] = array('like',$_GET['time2'].'%');
            $this->assign('time2', $_GET['time2']);
        }
        if (!empty($_GET['time1']) and !empty($_GET['time2'])) {
            $where['create_time'] = array('between',[$_GET['time1'], date('Y-m-d H:i:s', strtotime("{$_GET['time2']} +1 day"))]);
        }
        if(!empty($_GET['name'])){
            $where['name'] = ['like',"{$_GET['name']}%"];
        }
        if(!empty($_GET['nickname'])){
            $where['nickname'] = ['like',"{$_GET['nickname']}%"];
        }
        $user = db('user')->where($where)->field('id,openid,nickname,avatar,create_time')->order('id', 'DESC')->paginate(15, false, ['query'=>$_GET]);
        //$user_count =  db('user')->count();
        $this->assign('count', $user->total());
        $this->assign('list', $user->all());
        $this->assign('page', $user->render());
        $this->assign('where', $_GET);
        return $this->fetch();
    }

    /*
     * 2020/08/19
     * 买鸡日志
     * */
    public function chickenvip()
    {
        $where = [];
        if (!empty($_GET['time1'])) {
            $where['o.create_time'] = array('>=',strtotime($_GET['time1']).'%');
            $this->assign('time1', $_GET['time1']);
        }
        if (!empty($_GET['time2'])) {
            $where['o.create_time'] = array('<=',strtotime($_GET['time2']).'%');
            $this->assign('time2', $_GET['time2']);
        }
        if (!empty($_GET['time1']) and !empty($_GET['time2'])) {
            $where['o.create_time'] = array('between',[strtotime("{$_GET['time1']}"), strtotime("{$_GET['time2']} +1 day")]);
        }
        if(!empty($_GET['nickname'])){
            $where['u.nickname'] = ['like',"{$_GET['nickname']}%"];
        }
        // $where['u.id'] = ['!=','null'];
        $user = db('applet_order')
                ->alias('o')
                ->join('user u', 'o.game_user_id=u.id', 'LEFT')
                ->where($where)
                ->field('o.id,u.openid,u.nickname,u.avatar,o.create_time')
                ->order('o.id', 'DESC')
                ->paginate(15, false, ['query'=>$_GET]);
        $user_count =  db('applet_order')->count();
        $this->assign('count', $user->total());
        $this->assign('list', $user->all());
        $this->assign('page', $user->render());
        $this->assign('where', $_GET);
        return $this->fetch();
    }

    /*
     * 2020/08/20
     * 小鸡数据
     * */
    public function chickdata()
    {
        $where = [];
        if (!empty($_GET['time1'])) {
            $where['c.create_time'] = array('like',$_GET['time1'].'%');
            $this->assign('time1', $_GET['time1']);
        }
        if (!empty($_GET['time2'])) {
            $where['c.create_time'] = array('like',$_GET['time2'].'%');
            $this->assign('time2', $_GET['time2']);
        }
        if (!empty($_GET['time1']) and !empty($_GET['time2'])) {
            $where['c.create_time'] = array('between',[$_GET['time1'], date('Y-m-d H:i:s', strtotime("{$_GET['time2']} +1 day"))]);
        }
        if(!empty($_GET['nickname'])){
            $where['u.nickname'] = ['like',"{$_GET['nickname']}%"];
        }
        if (!empty($_GET['grade']) or $_GET['grade'] == 0){
            $where['c.grade'] = ['like',"%{$_GET['grade']}%"];
        }
        $where['isvip'] = ['=',1];
        $user = db('user_chicken')
                ->alias('c')
                ->join('user u', 'u.id = c.uid', 'left')
                ->where($where)
                ->field('c.id,c.number,c.uid,c.level,u.openid,u.nickname,u.avatar,c.create_time,c.grade')
                ->order('c.id', 'DESC')
                ->paginate(15, false, ['query'=>$_GET]);
        $user_count =  db('user_chicken')->where('isvip', 1)->count();
        $this->assign('count', $user->total());
        $this->assign('list', $user->all());
        $this->assign('page', $user->render());
        $this->assign('where', $_GET);
        return $this->fetch();
    }

    /*
     * 2020/08/20
     * 签到列表
     * */
    public function signlist()
    {
        $where = [];
        if (!empty($_GET['time1'])) {
            $where['s.create_time'] = array('like',$_GET['time1'].'%');
            $this->assign('time1', $_GET['time1']);
        }
        if (!empty($_GET['time2'])) {
            $where['s.create_time'] = array('like',$_GET['time2'].'%');
            $this->assign('time2', $_GET['time2']);
        }
        if (!empty($_GET['time1']) and !empty($_GET['time2'])) {
            $where['s.create_time'] = array('between',[ date('Y-m-d H:i:s', strtotime("{$_GET['time1']}")), date('Y-m-d H:i:s', strtotime("{$_GET['time2']} +1 day"))]);
        }
        if(!empty($_GET['nickname'])){
            $where['u.nickname'] = ['like',"{$_GET['nickname']}%"];
        }
        
        $user = db('user_sign')
                ->alias('s')
                ->join('user u', 'u.id = s.uid', 'left')
                ->join('sys_sign sys', 'sys.id = s.sign_id','left')
                ->where($where)
                ->field('s.id,sys.rulename,s.remark,u.id as uid,u.sign,u.openid,u.nickname,u.avatar,s.create_time')
                ->order('s.id', 'DESC')
                ->paginate(15, false, ['query'=>$_GET]);
        $page = $user->render();
        $list = $user->all();
        foreach ($list as $k => $v) {
            $list[$k]['sign_day'] = db('user_sign')->where('uid', $v['uid'])->count();
            $list[$k]['sign_sum'] = db('user_sign')->where('uid', $v['uid'])->sum('remark');
        }
        $user_count = db('user_sign')->count();
        $this->assign('count', $user->total());
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('where', $_GET);
        return $this->fetch();
    }

    /**
     * 2020/08/20
     * 任务数据
     */
    public function tanklist()
    {
        $where = [];
        if (!empty($_GET['time1'])) {
            $where['t.create_time'] = array('like',$_GET['time1'].'%');
            $this->assign('time1', $_GET['time1']);
        }
        if (!empty($_GET['time2'])) {
            $where['t.create_time'] = array('like',$_GET['time2'].'%');
            $this->assign('time2', $_GET['time2']);
        }
        if (!empty($_GET['time1']) and !empty($_GET['time2'])) {
            $where['t.create_time'] = array('between',[$_GET['time1'], date('Y-m-d H:i:s', strtotime("{$_GET['time2']} +1 day"))]);
        }
        if (!empty($_GET['nickname'])){
            $where['u.nickname'] = ['like',"%{$_GET['nickname']}%"];
        }
        if (!empty($_GET['tanksid'])){
            $where['sys.id'] = ['=',"{$_GET['tanksid']}"];
        }
        
        $list = db('user_tanks')
                ->alias('t')
                ->join('user u', 'u.id = t.uid', 'left')
                ->join('sys_reward s', 't.prize_id = s.id', 'left')
                ->join('sys_tanks sys', 'sys.id = t.t_id', 'left')
                ->where($where)
                ->field('t.id,s.name as rulename,sys.name,u.openid,u.nickname,u.avatar,t.create_time')
                ->order('t.id', 'DESC')
                ->paginate(15, false, ['query'=>$_GET]);
        $page = $list->render();
        $ds = $list->all();
        $tanks_list = db('sys_tanks')->select();
        $user_count = db('user_tanks')->count();
        $this->assign('tanks',$tanks_list);
        $this->assign('count', $list->total());
        $this->assign('list', $ds);
        $this->assign('page', $page);
        $this->assign('where', $_GET);

        return $this->fetch();
    }

    /**
     * 趣味答题
     */
    public function user_answer_list()
    {
        $where = [];
        if (!empty($_GET['time1'])) {
            $where['a.create_time'] = array('like',$_GET['time1'].'%');
            $this->assign('time1', $_GET['time1']);
        }
        if (!empty($_GET['time2'])) {
            $where['a.create_time'] = array('like',$_GET['time2'].'%');
            $this->assign('time2', $_GET['time2']);
        }
        if (!empty($_GET['time1']) and !empty($_GET['time2'])) {
            $where['a.create_time'] = array('between',[$_GET['time1'], date('Y-m-d H:i:s', strtotime("{$_GET['time2']} +1 day"))]);
        }
        if (!empty($_GET['name'])){
            $where['u.name'] = ['like',"%{$_GET['name']}%"];
        }
        if (!empty($_GET['nickname'])){
            $where['u.nickname'] = ['like',"%{$_GET['nickname']}%"];
        }
        $list =  db('user_do_answer')
                ->alias('a')
                ->join('user u', 'a.uid = u.id ', 'LEFT')
                ->join('answer ans','a.answer_id = ans.id','LEFT')
                ->order('id', 'DESC')
                ->where($where)
                ->field('a.id,u.nickname,ans.integral,u.name,u.openid,u.avatar,a.answer_title,a.answer_class_name,a.status,a.create_time')
                ->paginate(15, false, ['query'=>$_GET]);
        $page = $list->render();
        $list_s = $list->all();
        $this->assign('count', $list->total());
        $this->assign('list', $list_s);
        $this->assign('page', $page);
        $this->assign('where', $_GET);
        return $this->fetch();
    }

    /**
     * 第三方转入
     */
    function integral(){
        $where = [];
        if (!empty($_GET['time1'])) {
            $where['p.create_time'] = array('like',$_GET['time1'].'%');
            $this->assign('time1', $_GET['time1']);
        }
        if (!empty($_GET['time2'])) {
            $where['p.create_time'] = array('like',$_GET['time2'].'%');
            $this->assign('time2', $_GET['time2']);
        }
        if (!empty($_GET['time1']) and !empty($_GET['time2'])) {
            $where['p.create_time'] = array('between',[$_GET['time1'], date('Y-m-d H:i:s', strtotime("{$_GET['time2']} +1 day"))]);
        }
        if($name = input('name')){
            $where['u.name'] = ['like' ,"{$name}%"];
        }
        if($status = input('type'))
        {
            $where['p.type'] = ['=',$status];
        }
        if($nickname = input('nickname')){
            $where['u.nickname'] = ['like',"{$nickname}%"];
        }
        $list = db('user_third_party')
                ->alias('p')
                ->join('user u','p.uid = u.id','LEFT')
                ->field('p.id,u.name,u.nickname,u.avatar,u.egg,u.integral,p.integral as zrintegral,p.type,p.status,p.create_time')
                ->order('p.create_time','DESC')
                ->where($where)
                ->paginate(15,false,['query' => $_GET]);
        $page = $list->render();
        $data = $list->all();
        return view()->assign([
            'count'     => $list->total(),
            'list'      => $data,
            'page'      => $page,
            'where'     => $_GET
        ]);
    }
}
