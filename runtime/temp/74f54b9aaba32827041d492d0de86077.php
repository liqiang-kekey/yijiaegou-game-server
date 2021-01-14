<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:137:"C:\Users\iFunk\Desktop\layuiAdmin\sanguo\vrupup.com\sanguo\yaodunyuan\applet\chicken\public/../application/admin\view\data\chickdata.html";i:1597914636;s:126:"C:\Users\iFunk\Desktop\layuiAdmin\sanguo\vrupup.com\sanguo\yaodunyuan\applet\chicken\application\admin\view\public\header.html";i:1596526912;s:126:"C:\Users\iFunk\Desktop\layuiAdmin\sanguo\vrupup.com\sanguo\yaodunyuan\applet\chicken\application\admin\view\public\footer.html";i:1596526912;}*/ ?>
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
                <form class="layui-search layui-form" action="" method="get">
                    <div class="layui-col-md3">
                        <label>开始时间</label>
                        <div class="layui-input-inline">
                            <input type="text" class="layui-input" if(!empty($time1)) value="<?php echo $time1; ?>" {/if} id="time1" name="time1">
                        </div>
                    </div>
                    <div class="layui-col-md3">
                        <label>结束时间</label>
                        <div class="layui-input-inline">
                            <input type="text" class="layui-input" if(!empty($time2)) value="<?php echo $time2; ?>" {/if} id="time2" name="time2">
                        </div>
                    </div>
                    <div class="layui-col-md3 layui-search-submit">
                        <div class="layui-input-inline">
                            <button class="layui-btn layui-btn-normal" type="button" onclick="search(this)">搜索</button>

                        </div>
                    </div>
                    <div class="layui-col-md3">
                        <label style="color:red">总计<?php echo $count; ?>条</label>
                    </div>
                    <div class="layui-clear"></div>
                </form>
                <table class="layui-table layui-form">
                    <thead>
                    <tr>
                        <!-- <th style="width: 15px; text-align: center"><input type="checkbox" lay-filter="checkAll" lay-skin="primary"></th> -->
                        <th>序号</th>
                        <th>小鸡编号</th>
                        <th>领养人编号</th>
                        <th>领养人微信昵称</th>
                        <th>领养人微信头像</th>
                        <th>领养时间</th>
                        <th>等级</th>
                        <th>生长天数</th>
                    </tr>
                    </thead>
                    <?php if(empty($list)): ?>
                    <tr>
                        <td colspan="8" align="center">暂无数据！</td>
                    </tr>
                    <?php endif; ?>
                    <tbody>
                    <?php foreach($list as $item): ?>
                    <tr>
                        <!-- <td><input type="checkbox" name="ids[]" value="<?php echo $item['id']; ?>" lay-skin="primary"></td> -->
                        <td><?php echo $item['id']; ?></td>
                        <td><?php echo $item['number']; ?></td>
                        <td><?php echo $item['uid']; ?></td>
                        <td><?php echo $item['nickname']; ?></td>
                        <td><img src="<?php echo $item['avatar']; ?>"/></td>
                        <td><?php echo $item['create_time']; ?></td>
                        <td><?php echo $item['level']; ?></td>
                        <td><?php echo ceil(abs(strtotime($item['create_time']) - time()) /86400) - 1;?></td>
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
<script>
    layui.use(['index', 'form', 'layedit','laydate'], function(){
        var $ = layui.$
            ,form = layui.form;
        var laydate = layui.laydate;
            laydate.render({
                elem: '#time1' //指定元素

            });

            laydate.render({
                elem: '#time2' //指定元素
            })
        form.render(null, 'component-form-group');
        uploadInit('thumb', 'image', '图片尺寸 200 * 300');
        // // 监听表单提交
        // form.on('submit()', function(data){
        //     delete data.field.file;
        //     Post('', data.field, function(res) {
        //         if (res.code == 1) {
        //             alert_success(res.msg, function() {
        //                 back_url();
        //             })
        //         } else {
        //             alert_error(res.msg);
        //         }
        //     });
        //     return false;
        // });
    });
</script>