<?php


namespace app\admin\controller;


use app\common\service\AdminBase;
// 权限菜单管理
class AdminMenu extends AdminBase
{
    /**
     * 菜单列表
     */
    public function index() {
        $list = db('admin_menu')
            ->field('id, parent_id, name, router, icon, style, status, sort')
            ->where('status', 1)
            ->order('sort ASC, id DESC')
            ->select();
        $list = arr_tree($list);
        foreach($list as &$item) {
            $item['name'] = str_repeat('&nbsp;', ($item['level'] - 1) * 8).(empty($item['icon']) ? '' : '<i class="layui-icon '.$item['icon'].'"></i> ').$item['name'];
            $item['sort'] = str_repeat('&nbsp;', ($item['level'] - 1) * 8) . $item['sort'];
        }
        $this->assign('list', $list);
        return $this->fetch('base/admin_menu/index');
    }

    /**
     * 添加菜单
     */
    public function add_menu() {
        if(request()->isPost()) {
            // 创建页面参数
            $build_page = $_POST['build_page'];
            unset($_POST['build_page']);
            // 创建页面参数
            $data = $_POST;
            if(empty($data['name'])) {
                json_response(0, '请输入菜单名称');
            }
            if($data['parent_id'] == 0 && (empty($data['controller']) && empty($data['action']))) {
                $data['router'] = '';
            }else {
                if(empty($data['module'])) json_response(0, '非一级菜单请填写模块名');
                if(empty($data['controller'])) json_response(0, '非一级菜单请填写控制器名');
                if(empty($data['action'])) json_response(0, '非一级菜单请填写方法名');
                $data['router'] = "{$data['module']}/{$data['controller']}/{$data['action']}";
            }
            $data['create_time'] = time();
            // 创建页面
            if(!empty($build_page)) $this->build_page($build_page, $data['router']);
            db('admin_menu')->insert($data) ? json_response(1, '保存成功') : json_response(0, '保存失败');
        }else {
            $parent_menu = db('admin_menu')->order('sort ASC, id DESC')->field('id, parent_id, name, module, controller')->select();
            $parent_menu = arr_tree($parent_menu);
            foreach($parent_menu as &$item) {
                $item['name'] = str_repeat('&nbsp;', ($item['level'] - 1) * 8).'|-'.$item['name'];
            }
            $this->assign('parent_menu', $parent_menu);
            return $this->fetch('base/admin_menu/add_menu');
        }
    }

    /**
     * 编辑菜单
     */
    public function edit_menu() {
        $id = input('id');
        if(request()->isPost()) {
            // 创建页面参数
            $build_page = $_POST['build_page'];
            unset($_POST['build_page']);
            // 创建页面参数
            $data = $_POST;
            if(empty($data['name'])) {
                json_response(0, '请输入菜单名称');
            }
            if($data['parent_id'] == 0 && (empty($data['controller']) && empty($data['action']))) {
                $data['router'] = '';
            }else {
                if(empty($data['module'])) json_response(0, '非一级菜单请填写模块名');
                if(empty($data['controller'])) json_response(0, '非一级菜单请填写控制器名');
                if(empty($data['action'])) json_response(0, '非一级菜单请填写方法名');
                $data['router'] = "{$data['module']}/{$data['controller']}/{$data['action']}";
            }
            $data['update_time'] = time();
            // 创建页面
            if(!empty($build_page)) $this->build_page($build_page, $data['router']);
            db('admin_menu')->where('id', $id)->update($data) ? json_response(1, '保存成功') : json_response(0, '保存失败');
        }else {
            $parent_menu = db('admin_menu')->order('sort ASC, id DESC')->field('id, parent_id, name, module, controller')->select();
            $parent_menu = arr_tree($parent_menu);
            foreach($parent_menu as &$item) {
                $item['name'] = str_repeat('&nbsp;', ($item['level'] - 1) * 8).'|-'.$item['name'];
            }
            $data = db('admin_menu')
                ->where('id', $id)
                ->field('parent_id, name, module, controller, action, icon, style, sort, status')
                ->find();
            $this->assign('data', $data);
            $this->assign('parent_menu', $parent_menu);
            return $this->fetch('base/admin_menu/add_menu');
        }
    }


    /**
     * 创建页面
     * @param string $page
     * @param string $router
     * @return boolean
     */
    public function build_page($page='', $router='') {
        $tmp_page_path = [
            'table' => APP_PATH.'admin/view/public/tpl/table.html',
            'form'  => APP_PATH.'admin/view/public/tpl/form.html',
        ];
        $router = explode('/', $router);
        if(count($router) != 3) return false;
        $router[1] = str_format($router[1]);
        $router[2] = str_format($router[2]);
        $page_path = APP_PATH."{$router[0]}/view/{$router[1]}/{$router[2]}.html";
        
        //创建控制器
        $cruucontroller =  APP_PATH."{$router[0]}/controller/".ucfirst($router[1]).'.php';
        if(!is_file($cruucontroller)){
            $router[0] = ucfirst($router[0]);
            $router[1] = ucfirst($router[1]);
            $namsp = "namespace app\\{$router[1]}\\controller;";
            $content = "<?php\n{$namsp}\n\nuse app\common\service\AdminBase;\n\nclass {$router[1]} extends AdminBase\n{\n\n}";
            file_put_contents($cruucontroller, $content);
        }
        // 创建目录
        folder_build(dirname($page_path));
        $content = file_get_contents($tmp_page_path[$page]);
        file_put_contents($page_path, $content);
    }

    /**
     * 删除菜单
     */
    public function del_menu() {
        $id = input('id');
        // 判断是否存在子菜单
        $have_children = db('admin_menu')->where('parent_id', $id)->value('id');
        if(!empty($have_children)) json_response(0, '存在子菜单不可删除');
        db('admin_menu')->where('id', $id)->delete() ? json_response(1, '删除成功') : json_response(0, '删除失败');
    }
}