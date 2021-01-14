<?php


namespace app\admin\controller;


use app\common\service\AdminBase;
// 管理员管理
class Admin extends AdminBase
{
    /**
     * 管理员列表
     */
    public function index() {
        $data = db('admin')
            ->alias('a')
            ->join('admin_group b', 'a.group_id=b.id', 'left')
            ->field('a.id, b.group_name, a.nickname, a.username, a.status, a.last_login_time, a.last_login_ip')
            ->order('a.id DESC')
            ->paginate(15);
        $list = $data->all();
        foreach($list as &$item) {
            if($item['last_login_time'] > 0) {
                $item['last_login_time'] = date('Y-m-d H:i:s', $item['last_login_time']);
            }else {
                $item['last_login_time'] = $item['last_login_ip'] = '-';
            }
        }
        $this->assign('list', $list);
        $this->assign('page', $data->render());
        return $this->fetch('base/admin/index');
    }

    /**
     * 添加管理员
     */
    public function add_admin() {
        if(request()->isPost()) {
            $data = $_POST;
            if(empty($data['group_id'])) json_response(0, '请选择管理组');
            $data['create_time'] = time();
            $data['update_time'] = time();
            $data['salt'] = str_random();
            $data['password'] = md5($data['salt'].'_'.$data['password']);
            db('admin')->insert($data) ? json_response(1,'保存成功') : json_response(0, '保存失败');
        }else {
            $group_list = db('admin_group')->field('id, parent_id, group_name')->select();
            $group_list = arr_tree($group_list);
            foreach ($group_list as &$item) {
                $item['group_name']   = str_repeat('&nbsp;', ($item['level'] - 1) * 8) . '|-' . $item['group_name'];
            }
            $this->assign('group_list', $group_list);
            return $this->fetch('base/admin/add_admin');
        }
    }

    /**
     * 编辑管理员
     */
    public function edit_admin() {
        $id = input('id');
        if(request()->isPost()) {
            $data = $_POST;
            if(empty($data['group_id'])) json_response(0, '请选择管理组');
            $data['update_time'] = time();
            db('admin')->where('id', $id)->update($data) ? json_response(1,'保存成功') : json_response(0, '保存失败');
        }else {
            $group_list = db('admin_group')->field('id, parent_id, group_name')->select();
            $group_list = arr_tree($group_list);
            foreach ($group_list as &$item) {
                $item['group_name']   = str_repeat('&nbsp;', ($item['level'] - 1) * 8) . '|-' . $item['group_name'];
            }
            $data = db('admin')->where('id', $id)->field('group_id, nickname, username, status')->find();
            $this->assign('data', $data);
            $this->assign('group_list', $group_list);
            return $this->fetch('base/admin/add_admin');
        }
    }

    /**
     * 重置密码
     */
    public function reset_pwd() {
        $id = input('id');
        if(request()->isPost()) {
            if(strlen($_POST['password']) > 32 || strlen($_POST['password']) < 6) json_response(0, '密码长度6-32位');
            if($_POST['password'] != $_POST['re_password']) json_response(0, '两次密码输入不一致');
            $data = [];
            $data['salt'] = str_random();
            $data['password'] = md5($data['salt'].'_'.$_POST['password']);
            $data['update_time'] = time();
            db('admin')->where('id', $id)->update($data) ? json_response(1, '重置密码成功') : json_response(0, '重置密码失败');
        }else {
            return $this->fetch('base/admin/reset_pwd');
        }
    }

    /**
     * 修改密码
     * @date 2020/7/6 18:30
     */
    public function edit_pwd() {
        if(request()->isPost()) {
            $old_pwd     = param_check('old_password');
            $password    = param_check('password');
            $re_password = param_check('re_password');
            $user = db('admin')
                ->where('id', $this->user['id'])
                ->field('password, salt')
                ->find();
            if(md5($user['salt'].'_'.$old_pwd) != $user['password']) {
                json_response(0, '原密码错误');
            }
            if($password != $re_password) {
                json_response(0, '两次新密码输入不一致');
            }
            $salt = str_random(6);
            $res = db('admin')
                ->where('id', $this->user['id'])
                ->update([
                    'salt'        => $salt,
                    'password'    => md5($salt.'_'.$password),
                    'update_time' => time()
                ]);
            if($res) {
                session($this->admin_session_key, null);
                json_response(1, '修改成功');
            }else {
                json_response(0, '修改失败');
            }
        }else {
            return $this->fetch('base/admin/edit_pwd');
        }
    }

    /**
     * 删除管理员
     */
    public function del_admin() {
        $id = input('id');
        if(empty($id)) json_response(0, '缺少ID参数');
        if($id == 1) json_response(0, '系统管理员不可删除');
        db('admin')->where('id', $id)->delete() ? json_response(1, '删除成功') : json_response(0, '删除失败');
    }
}