<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:65:"/www/game/public/../application/admin/view/certificate/index.html";i:1603346411;s:51:"/www/game/application/admin/view/public/header.html";i:1603346408;s:51:"/www/game/application/admin/view/public/footer.html";i:1603346407;}*/ ?>
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
                <?php echo access_button('Certificate/add_certificate'); ?>
                <!--<form class="layui-search layui-form" action="" method="get">-->
                    <!--<div class="layui-col-md3">-->
                        <!--<label>搜索字段</label>-->
                        <!--<div class="layui-input-inline">-->
                            <!--<input type="text" class="layui-input" name="a">-->
                        <!--</div>-->
                    <!--</div>-->
                    <!--<div class="layui-col-md3 layui-search-submit">-->
                        <!--<div class="layui-input-inline">-->
                            <!--<button class="layui-btn layui-btn-normal" type="button" onclick="search(this)">搜索</button>-->
                            <!--<button class="layui-btn layui-btn-danger" type="button" onclick="export_excel(this)">导出</button>-->
                        <!--</div>-->
                    <!--</div>-->
                    <!--<div class="layui-clear"></div>-->
                <!--</form>-->
                <table class="layui-table layui-form">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>图片</th>
                        <th>启用状态</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <?php if(empty($list)): ?>
                    <tr>
                        <td colspan="4" align="center">暂无数据！</td>
                    </tr>
                    <?php endif; ?>
                    <tbody>
                    <?php foreach($list as $item): ?>
                    <tr>
                        
                        <td><?php echo $item['id']; ?></td>
                        <td><img src="<?php echo $item['image']; ?>"></td>
                        <td><?php echo $item['isenable']; ?></td>
                        <td>
                            <!--操作按钮开始-->
                            <?php echo access_button('Certificate/edit_certificate', ['id'=>$item['id']], '编辑'); ?>
                            <?php echo access_button('Certificate/del_certificate', ['id'=>$item['id']], '删除', 'confirm'); ?>
                            <!--操作按钮结束-->
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <!--分页按钮输出开始-->
                <div class="page">
                <?php echo $page; ?>
                </div>
                <!--分页按钮输出结束-->
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