<?php
namespace app\admin\controller;
use app\common\service\AdminBase;

/**
 * 小鸡产蛋数据
 */
class Outegg extends AdminBase
{
    function index(){
        $where = [];
        if (!empty($_GET['time1'])) {
            $where['h.create_time'] = array('like',$_GET['time1'].'%');
            $this->assign('time1', $_GET['time1']);
        }
        if (!empty($_GET['time2'])) {
            $where['h.create_time'] = array('like',$_GET['time2'].'%');
            $this->assign('time2', $_GET['time2']);
        }
        if (!empty($_GET['time1']) and !empty($_GET['time2'])) {
            $where['h.create_time'] = array('between',[$_GET['time1'], date('Y-m-d H:i:s', strtotime("{$_GET['time2']} +1 day"))]);
        }
        if (!empty($_GET['name'])){
            $where['u.name'] = ['like',"%{$_GET['name']}%"];
        }
        if (!empty($_GET['nickname'])){
            $where['u.nickname'] = ['like',"%{$_GET['nickname']}%"];
        }
        $where['h.class'] = ['=',2]; //having 类型 2产蛋，1喂养 
        $list= db('user_chicken_having')
                ->alias('h')
                ->join('user_chicken c','c.id = h.c_id','LEFT')
                ->join('user u','u.id = h.uid','LEFT')
                ->where($where)
                ->field("h.id,h.create_time,if(u.name='',u.nickname,u.name) uname,u.avatar,c.outegg,c.number,u.id uid,h.ispickup")
                ->order('h.id', 'DESC')
                ->paginate(15,false,['query'=>$_GET]);
        $this->assign('list', $list->all());
        $this->assign('page', $list->render());
        $this->assign('where', $_GET);
        return $this->fetch();
    }
}