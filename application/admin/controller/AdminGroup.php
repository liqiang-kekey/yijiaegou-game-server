<?php


namespace app\admin\controller;


use app\common\service\AdminBase;

// 管理组
class AdminGroup extends AdminBase
{
    /**
     * 管理组列表
     */
    public function index() {
        $list = db('admin_group')->field('id, parent_id, group_name, status, update_time')->select();
        $list = arr_tree($list);
        foreach ($list as &$item) {
            $item['group_name']   = str_repeat('&nbsp;', ($item['level'] - 1) * 8) . '|-' . $item['group_name'];
            $item['group_people'] = db('admin')
                ->where('group_id', $item['id'])
                ->count();
        }
        $this->assign('list', $list);
        return $this->fetch('base/admin_group/index');
    }

    /**
     * 添加管理组
     */
    public function add_group() {
        if(request()->post()) {
            $data = $_POST;
            $data['access'] = implode(',', $data['access']);
            $data['create_time'] = time();
            $data['update_time'] = time();
            db('admin_group')->insert($data) ? json_response(1, '保存成功') : json_response(0, '保存失败');
        }else {
            $group_list = db('admin_group')->field('id, parent_id, group_name')->select();
            $group_list = arr_tree($group_list);
            foreach ($group_list as &$item) {
                $item['group_name']   = str_repeat('&nbsp;', ($item['level'] - 1) * 8) . '|-' . $item['group_name'];
            }
            $access_list = db('admin_menu')->field('id, parent_id, name')->order('sort ASC, id DESC')->select();
            $access_list = arr_tree($access_list, true);
            $this->assign('group_list', $group_list);
            $this->assign('access_list', $access_list);
            return $this->fetch('base/admin_group/add_group');
        }
    }

    /**
     * 编辑管理组
     */
    public function edit_group() {
        $id = input('id');
        if(request()->isPost()) {
            $data = $_POST;
            $data['access'] = implode(',', $data['access']);
            $data['update_time'] = time();
            db('admin_group')->where('id', $id)->update($data) ? json_response(1, '保存成功') : json_response(0, '保存失败');
        }else {
            $group_list = db('admin_group')->field('id, parent_id, group_name')->select();
            $group_list = arr_tree($group_list);
            foreach ($group_list as &$item) {
                $item['group_name']   = str_repeat('&nbsp;', ($item['level'] - 1) * 8) . '|-' . $item['group_name'];
            }
            $access_list = db('admin_menu')->field('id, parent_id, name')->order('sort ASC, id DESC')->select();
            $access_list = arr_tree($access_list, true);
            $data = db('admin_group')->where('id', $id)->field('parent_id, group_name, access, status')->find();
            $data['access'] = explode(',', $data['access']);
            $this->assign('group_list', $group_list);
            $this->assign('access_list', $access_list);
            $this->assign('data', $data);
            return $this->fetch('base/admin_group/add_group');
        }
    }

    /**
     * 删除管理组
     */
    public function del_group() {
        $id = input('id');
        if($id == 1) json_response(0, '系统管理组不可删除');
        $have_children = db('admin')->where('group_id', $id)->value('id');
        if($have_children) json_response(0, '管理组存在管理员不可删除');
        db('admin_group')->where('id', $id)->delete() ? json_response(1, '删除成功') : json_response(0,'删除失败');
    }
}