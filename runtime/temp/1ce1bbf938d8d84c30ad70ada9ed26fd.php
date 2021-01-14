<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:74:"/www/game/public/../application/admin/view/base/admin_group/add_group.html";i:1603954272;s:51:"/www/game/application/admin/view/public/header.html";i:1603954267;s:51:"/www/game/application/admin/view/public/footer.html";i:1603954267;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>后台管理</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="<?php echo $resource_url; ?>layuiadmin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="<?php echo $resource_url; ?>layuiadmin/style/admin.css" media="all">
    <link rel="stylesheet" href="<?php echo $resource_url; ?>layuiadmin/style/login.css" media="all">
    <style>
        td img {
            width: 40px;
            height: 40px;
            display: block;
        }
        .layui-search {
            display: block;
            padding: 10px 10px 0 10px;
            border: 1px solid #e6e6e6;
            background-color: #f2f2f2;
            margin-top: 10px;
        }
        .layui-search .layui-col-md3 {
            margin-bottom: 10px;
        }
        .layui-search .layui-col-md3 label {
            float: left;
            display: block;
            width: 60px;
            font-weight: 400;
            line-height: 20px;
            text-align: right;
            padding: 9px 9px 9px 0;
            text-align-last: justify;
        }
        .layui-search .layui-input-inline {
            width: calc(100% - 80px);
        }
        .layui-form-mid {
            float: none !important;
        }
    </style>
    <script>
        var UPLOAD_URL   = '<?php echo url("Oss/upload_file"); ?>';
        var RESOURCE_URL = '<?php echo $resource_url; ?>';
    </script>
</head>
<body>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-card">
            <div class="layui-card-header"><?php echo $page_title; ?></div>
            <div class="layui-card-body">
                <form class="layui-form padsome" action="">
                    <div class="layui-form-item">
                        <label class="layui-form-label">父级管理组</label>
                        <div class="layui-input-block">
                            <select name="parent_id">
                                <option value="0">请选择父级管理组</option>
                                <?php foreach($group_list as $item): ?>
                                <option value="<?php echo $item['id']; ?>" <?php if($data['parent_id'] == $item['id']): ?>selected<?php endif; ?>><?php echo $item['group_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">管理组名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="group_name" value="<?php echo $data['group_name']; ?>" lay-verify="required" autocomplete="off" placeholder="请输入菜单名称" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">管理组权限</label>
                        <?php foreach($access_list as $item): ?>
                        <div class="layui-input-block">
                            <input type="checkbox" name="access" data-level="<?php echo $item['level']; ?>" lay-filter="checkAuth" value="<?php echo $item['id']; ?>" title="<?php echo $item['name']; ?>" <?php if(in_array($item['id'], $data['access'])): ?>checked<?php endif; ?>>
                        </div>
                        <?php foreach($item['sub_list'] as $sub_item): ?>
                        <div class="layui-input-block">
                            <input type="checkbox" name="access" data-level="<?php echo $sub_item['level']; ?>" lay-filter="checkAuth" lay-skin="primary" data-pid="<?php echo $sub_item['parent_id']; ?>" value="<?php echo $sub_item['id']; ?>" title="<?php echo $sub_item['name']; ?>" <?php if(in_array($sub_item['id'], $data['access'])): ?>checked<?php endif; ?>>
                            <?php foreach($sub_item['sub_list'] as $sub_item2): ?>
                            <input type="checkbox" name="access" data-level="<?php echo $sub_item2['level']; ?>" lay-filter="checkAuth" lay-skin="primary" data-pid="<?php echo $sub_item2['parent_id']; ?>" value="<?php echo $sub_item2['id']; ?>" title="<?php echo $sub_item2['name']; ?>"<?php if(in_array($sub_item2['id'], $data['access'])): ?>checked<?php endif; ?>>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; endforeach; ?>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">状态</label>
                        <div class="layui-input-block">
                            <input type="radio" name="status" value="1" title="启用" <?php if($data['status'] == '1' OR !isset($data['status'])): ?>checked=""<?php endif; ?>>
                            <input type="radio" name="status" value="0" title="关闭" <?php if($data['status'] == '0'): ?>checked<?php endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <div class="layui-footer" style="left: 0;">
                                <button class="layui-btn" lay-submit="">提交</button>
                                <button class="layui-btn layui-btn-primary" type="button" onclick="back_url()">返回</button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
<script src="<?php echo $resource_url; ?>layuiadmin/layui/layui.js"></script>
<script src="<?php echo $resource_url; ?>admin/js/common.js"></script>
<script>
    layui.config({
        base: '<?php echo $resource_url; ?>/layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['layer', 'form'], function() {
        var $ = layui.$, form = layui.form;
        form.render();
        form.on('checkbox(checkAll)', function(data) {
            if($(data.elem).prop('checked')) {
                $('[name="ids[]"]').prop("checked", true);
                form.render();
            }else {
                $('[name="ids[]"]').prop("checked", false);
                form.render();
            }
        });
    });
</script>
</body>
</html>
<script>
    layui.use(['index', 'form'], function(){
        var $ = layui.$
            ,layer = layui.layer
            ,form = layui.form;

        form.render(null, 'component-form-group');
        // 监听选中事件
        form.on('checkbox(checkAuth)', function(data) {
            let parent_id = $(data.elem).data('pid');
            let level = $(data.elem).data('level');
            let isChecked = $(this).prop('checked');
            if(level == 1) { // 全选所有子列表
                $('[name=access][data-pid='+$(this).val()+']').each(function() {
                    $('[name=access][data-pid='+$(this).val()+']').prop('checked', isChecked);
                    $(this).prop('checked', isChecked);
                });
            }else if(level == 2) { // 判断父级状态以及全选子级
                $('[name=access][data-pid='+data.value+']').prop('checked', isChecked);
                let parentChecked = false;
                $('[name=access][data-pid='+parent_id+']').each(function() {
                    if($(this).prop('checked')) {
                        parentChecked = true;
                        return false;
                    }
                });
                $('[name=access][value='+parent_id+']').prop('checked', parentChecked);
            }else if(level == 3) { // 判断二级菜单和一级菜单选中状态
                let secondChecked = false;
                $('[name=access][data-pid='+parent_id+']').each(function() {
                    if($(this).prop('checked')) {
                        secondChecked = true;
                        return false;
                    }
                });
                $('[name=access][value='+parent_id+']').prop('checked', secondChecked);
                let firstChecked = false;
                if(secondChecked) {
                    $('[name=access][value='+$('[value='+parent_id+']').data('pid')+']').prop('checked', true);
                }else {
                    $('[name=access][data-pid='+$('[value='+parent_id+']').data('pid')+']').each(function() {
                        if($(this).prop('checked')) {
                            firstChecked = true;
                            return false;
                        }
                    });
                    $('[name=access][value='+$('[value='+parent_id+']').data('pid')+']').prop('checked', firstChecked);
                }

            }
            form.render();
        });
        /* 监听提交 */
        form.on('submit()', function(data){
            data.field.access = [];
            $('[name=access]:checked').each(function() {
                data.field.access.push($(this).val());
            });
            if(data.field.access.length == 0) {
                alert_error('请至少选择一个权限菜单');
                return false;
            }
            Post('', data.field, function(res) {
                if (res.code == 1) {
                    alert_success(res.msg, function() {
                        back_url();
                    })
                } else {
                    alert_error(res.msg);
                }
            });
            return false;
        });
    });
</script>