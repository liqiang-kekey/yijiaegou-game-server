<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/11
 * Time: 14:54
 */

namespace app\admin\controller;


use app\common\service\AdminBase;

class Goods extends AdminBase
{
    /*
     * 2020/08/11
     * 商城列表
     * */
    public function index(){
        $where = [];
        if(!empty($_GET['name'])){
            $where['name'] = array('like','%'.$_GET['name'].'%');
        }
        if(!empty($_GET['type'])){
            $where['type'] = $_GET['type'];
        }
        $goods = db('shop_goods')->where($where)->field('id,pay_type,type,sort,name,thumb,stock,price,shelves')->paginate(15,false,['query'=>$_GET]);
        $this->assign('list',$goods->all());
        $this->assign('page',$goods->render());
        $this->assign('where',$_GET);
        return $this->fetch();
    }

    /*
     * 2020/08/11
     * 添加商品
     * */
    public function add_goods(){
        if(request()->isPost()){
            $data = $_POST;
            db('shop_goods')->insert($data)?json_response(1,'添加成功'):json_response(2,'添加失败');
        }
        return $this->fetch();
    }

    /*
     * 2020/08/11
     * 编辑商品
     * */
    public function edit_goods(){
        $id = param_check('id');
        if(request()->isPost()){
            $data = $_POST;
            db('shop_goods')->where('id',$id)->update($data)?json_response(1,'编辑成功'):json_response(2,'编辑失败');
        }
        $data = db('shop_goods')->where('id',$id)->field('id,pay_type,type,sort,name,thumb,stock,price,shelves')->find();
        $this->assign('data',$data);
        return $this->fetch('add_goods');
    }

    /*
     * 2020/08/11
     * 删除商品
     * */
    public function del_goods(){
        $id = input('id');
        db('shop_goods')->where('id',$id)->delete()?json_response(1,'删除成功'):json_response(2,'删除失败');
    }
}
