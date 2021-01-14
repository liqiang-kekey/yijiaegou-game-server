<?php


namespace app\common\service;


use think\Controller;
use think\Env;

// 后台模块公共继承类
class AdminBase extends Controller
{
    protected $user; // 后台登陆用户
    protected $access = []; // 当前用户菜单权限
    protected $current_router;
    protected $allow_router = ['admin/Index/index', 'admin/Index/welcome', 'admin/Admin/edit_pwd']; // 允许访问的路由
    protected $admin_session_key; // 存储登录信息的键名
    public function __construct()
    {
        parent::__construct();
        $this->admin_session_key = admin_session_key();
        // 异常错误报错级别, 禁止未定义变量错误的提示
        error_reporting(E_ERROR | E_PARSE );
        // 当前访问路由
        $this->current_router = request()->module().'/'.request()->controller().'/'.request()->action();
        // 检查用户登录状态
        $this->check_login();
        // 检查用户访问权限
        $this->check_access();
        // 默认访问URL
        $this->assign('home_page', url('Index/welcome'));
        // 资源目录
        $this->assign('resource_url', Env::get('admin_resource_url'));
    }

    /**
     * 检查用户登录
     */
    public function check_login() {
        $admin_user = session($this->admin_session_key);
        if(empty($admin_user)) {
            echo
'<script>
var url = "'.url('Login/index').'";
if(window.parent) {
    window.parent.location.href = url;
}else {
    location.href = url;
}
</script>';exit();
        }else {
            $this->user = $admin_user;
        }
        global $admin_uid;
        $admin_uid = $this->user['id'];
    }

    /**
     * 检查用户访问权限
     */
    public function check_access() {
        $access_info = cache("menu_{$this->user['id']}");
        if(empty($access_info) || $access_info['expire_time'] < time()) {
            $access = db('admin_group')->where('id', $this->user['group_id'])->value('access');
            $where = ['status' => 1];
            if($access != '*')$where['id'] = ['in', explode(',', $access)];
            $access_list = db('admin_menu')
                ->where($where)
                ->field('id, parent_id, icon, name, router, style')
                ->order('sort ASC, id DESC')
                ->select();
            foreach($access_list as $item) {
                if(!empty($item['router'])) $this->access[$item['router']] = ['name'=>$item['name'], 'icon'=>$item['icon'], 'style'=>$item['style']];
            }
            cache("menu_{$this->user['id']}", [
                'expire_time' => time() + 5,
                'access_list' => $access_list,
                'access_menu' => $this->access
            ]);
        }else {
            $access_list  = $access_info['access_list'];
            $this->access = $access_info['access_menu'];
        }
        // 首页渲染左侧菜单
        if($this->current_router == 'admin/Index/index') {
            // 无限级分类排序菜单
            $access_list = arr_tree($access_list, true);
            // 渲染左侧菜单
            $this->assign('access_list', $access_list);
        }else {
            // 判断访问权限
            if(!in_array($this->current_router, $this->allow_router)) {
                if(!in_array($this->current_router, array_keys($this->access))) {
                    if(request()->isAjax()) {
                        json_response(0, '没有访问权限');
                    }else {
                        $this->error('没有访问权限', url('index/welcome'));
                        exit();
                    }
                }else {
                    // 普通页面
                    $this->assign('page_title', $this->access[$this->current_router]['name']);
                }
            }

        }

    }
}