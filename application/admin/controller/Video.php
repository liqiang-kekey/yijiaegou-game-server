<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/7
 * Time: 10:46
 */

namespace app\admin\controller;


use app\common\service\AdminBase;

class Video extends AdminBase
{
    /*
     * 2020/08/07
     * 任务视频列表
     * */
    public function index(){
        $where = [];
        $title = input('title');
        if(!empty($title)){
            $where['title'] = array('like','%'.$title.'%');
        }

        $list = db('tanks_video')->where($where)->paginate(15,false,['query'=>$_GET]);
        $this->assign('list',$list->all());
        $this->assign('page',$list->render());
        $this->assign('where',$_GET);
        return $this->fetch();
    }

    /*
     * 2020/08/07
     * 任务视频添加
     * */
    public function add_video(){
        if(request()->isPost()){
            $data = $_POST;
            db('tanks_video')->insert($data)?json_response(1,'添加成功'):json_response(2,'添加失败');
        }
        return $this->fetch();
    }

    /*
     * 2020/08/07
     * 任务视频编辑
     * */
    public function edit_video(){
        $id = param_check('id');
        if(request()->isPost()){
            $data = $_POST;
            db('tanks_video')->where('id',$id)->update($data)?json_response(1,'修改成功'):json_response(2,'修改失败');
        }
        $data = db('tanks_video')->where('id',$id)->find();
        $this->assign('data',$data);
        return $this->fetch('add_video');
    }

    /*
     * 任务视频删除
     * 2020/08/07
     * */
    public function del_video(){
        $id = param_check('id');
        db('tanks_video')->where('id',$id)->delete()?json_response(1,'删除成功'):json_response(2,'删除失败');
    }
}