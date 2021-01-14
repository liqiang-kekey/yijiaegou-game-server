<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:139:"C:\Users\iFunk\Desktop\layuiAdmin\sanguo\vrupup.com\sanguo\yaodunyuan\applet\chicken\public/../application/admin\view\base\admin\index.html";i:1596526912;s:126:"C:\Users\iFunk\Desktop\layuiAdmin\sanguo\vrupup.com\sanguo\yaodunyuan\applet\chicken\application\admin\view\public\header.html";i:1596526912;s:126:"C:\Users\iFunk\Desktop\layuiAdmin\sanguo\vrupup.com\sanguo\yaodunyuan\applet\chicken\application\admin\view\public\footer.html";i:1596526912;}*/ ?>
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
                <?php echo access_button('Admin/add_admin'); ?>
                <table class="layui-table">
                    <thead>
                    <tr>
                        <th>管理组</th>
                        <th>管理员名称</th>
                        <th>登录账号</th>
                        <th>状态</th>
                        <th>上次登录时间</th>
                        <th>上次登录IP</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($list as $item): ?>
                    <tr>
                        <td><?php echo $item['group_name']; ?></td>
                        <td><?php echo $item['nickname']; ?></td>
                        <td><?php echo $item['username']; ?></td>
                        <td><?php echo $item['status']==1?'正常' : '禁用'; ?></td>
                        <td><?php echo $item['last_login_time']; ?></td>
                        <td><?php echo $item['last_login_ip']; ?></td>
                        <td>
                            <?php echo access_button('Admin/edit_admin', ['id'=>$item['id']], '编辑'); ?>
                            <?php echo access_button('Admin/reset_pwd', ['id'=>$item['id']], '修改密码', 'frame', ['400px', '320px']); if($item['id'] != 1): ?>
                            <?php echo access_button('Admin/del_admin', ['id'=>$item['id']], '删除', 'confirm'); endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php echo $page; ?>
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