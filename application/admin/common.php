<?php
/**
 * 根据权限生成按钮
 * @param $router
 * @param array $param
 * @param string $name
 * @param string $type
 * @param array $area frame窗口大小
 */
function access_button($router, $param=[], $name='', $type='url', $area=[]) {
    $router = count(explode('/', $router)) > 2 ? $router : "admin/{$router}";
    $button = '<a href="{url}" class="layui-btn {style}">{icon}{name}</a>';
    global $admin_uid;
    $data = cache("menu_{$admin_uid}");
    $link = url($router, $param);
    if($type == 'url') {
        $url = $link;
    }else if($type == 'confirm') {
        $url = "javascript: confirm('{$link}', '是否确认执行此操作？')";
    }else if($type == 'frame') {
        $frame_area = '';
        if(count($area) > 1) $frame_area = ", ['{$area[0]}', '{$area[1]}']";
        $url = "javascript: open_frame('{$link}', '{$data['access_menu'][$router]['name']}'{$frame_area})";
    }else {
        $url = '';
    }
    // dump( $data['access_menu']);die;
    if(in_array($router, array_keys($data['access_menu']))) {
        echo str_replace([
            '{url}',
            '{style}',
            '{icon}',
            '{name}'
        ], [
            $url,
            $data['access_menu'][$router]['style'],
            $data['access_menu'][$router]['icon'] ? '<i class="layui-icon '.$data['access_menu'][$router]['icon'].'"></i>' : '',
            empty($name) ? $data['access_menu'][$router]['name'] : $name
        ], $button);
    }
}

/**
 * 根据项目名设置session的键，避免多个项目的session冲突
 * @return string
 * @date 2020/7/7 16:13
 */
function admin_session_key() {
    $path = dirname(dirname(dirname(APP_PATH)));
    if(strpos('/', $path)) {
        $temp = explode('/', $path);
    }else {
        $temp = explode('\\', $path);
    }
    return end($temp).'_user';
}