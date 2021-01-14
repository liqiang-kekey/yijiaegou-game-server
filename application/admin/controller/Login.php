<?php


namespace app\admin\controller;


use think\Controller;
use think\Env;
use think\Request;

// 管理员登录
class Login extends Controller
{
    protected $admin_session_key; // 存储登录信息的键名

    /**
     * 构造方法
     * Login constructor.
     * @param Request|null $request
     */
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->admin_session_key = admin_session_key();
    }

    /**
     * 管理员登录
     */
    public function index() {
        try {
            if(request()->isPost()) {
                $username = param_check('username');
                $password = param_check('password');
                // 验证管理员是否存在
                $field = 'a.id, a.group_id, a.username, a.password, a.salt, a.status, b.status as group_status';
                $user = db('admin')->alias('a')->join('admin_group b', 'a.group_id=b.id', 'left')
                    ->where('a.username', $username)->field($field)->find();
                if(empty($user)) json_response(0, '用户不存在');
                // 判断密码是否正确
                if(md5($user['salt'].'_'.$password) != $user['password']) {
                    json_response(0, '密码错误');
                }
                // 判断管理员状态和管理组状态
                if($user['status'] != 1) json_response(0, '您已被禁止登录');
                //print_r($user);die;
                if($user['group_status'] != 1) json_response(0, '您所在的管理组已被禁用');
                // 登陆成功
                db('admin')
                    ->where('id', $user['id'])
                    ->update([
                        'last_login_time' => time(),
                        'last_login_ip'   => request()->ip()
                    ]);
                session($this->admin_session_key, $user);
                json_response(1, '登录成功');
            }else {
                // 资源目录
                $this->assign('resource_url', Env::get('admin_resource_url'));
                return $this->fetch('base/login');
            }
        }catch(\Exception $e) {
            json_response(0, $e->getMessage());
        }
    }

    /**
     * 退出登录
     */
    public function logout() {
        session($this->admin_session_key, null);
        $this->redirect('Login/index');
    }
}