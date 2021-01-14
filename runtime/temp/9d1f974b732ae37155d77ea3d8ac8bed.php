<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:138:"C:\Users\iFunk\Desktop\layuiAdmin\sanguo\vrupup.com\sanguo\yaodunyuan\applet\chicken\public/../application/admin\view\sigin\add_sigin.html";i:1597053995;s:126:"C:\Users\iFunk\Desktop\layuiAdmin\sanguo\vrupup.com\sanguo\yaodunyuan\applet\chicken\application\admin\view\public\header.html";i:1596526912;s:126:"C:\Users\iFunk\Desktop\layuiAdmin\sanguo\vrupup.com\sanguo\yaodunyuan\applet\chicken\application\admin\view\public\footer.html";i:1596526912;}*/ ?>
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
            <div class="layui-card-header"><?php echo $page_title; ?> <a href="javascript: back_url();" class="layui-btn layui-btn-primary layui-layout-right">返回上级</a></div>
            <div class="layui-card-body">
                <form class="layui-form padsome" action="">

                    <div class="layui-form-item">
                        <label class="layui-form-label">签到类型</label>
                        <div class="layui-input-block">
                            <input type="radio" name="class" value="1" title="普通签到" <?php if($data['class'] == 1): ?>checked<?php endif; ?>>
                            <input type="radio" name="class" value="2" title="连续签到" <?php if($data['class'] == 2): ?>checked<?php endif; ?>>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">奖励</label>
                        <div class="layui-input-block">
                            <select name="reward_id" lay-verify="required">
                                <option value=""></option>
                                <?php foreach($list as $item): ?>
                                <option value="<?php echo $item['id']; ?>" <?php if($data['reward_id'] == $item['id']): ?>selected<?php endif; ?>><?php echo $item['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">规则名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="rulename" value="<?php echo $data['rulename']; ?>" lay-verify="required" placeholder="" autocomplete="off" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">规则天数</label>
                        <div class="layui-input-block">
                            <input type="text" name="ruleday" value="<?php echo $data['ruleday']; ?>" lay-verify="required" placeholder="" autocomplete="off" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">启用状态</label>
                        <div class="layui-input-block">
                            <input type="radio" name="isenable" value="1" title="启用" <?php if($data['isenable'] == 1): ?>checked<?php endif; ?>>
                            <input type="radio" name="isenable" value="2" title="禁用" <?php if($data['isenable'] == 2): ?>checked<?php endif; ?>>
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
    layui.use(['index', 'form', 'layedit'], function(){
        var $ = layui.$
            ,form = layui.form;
        form.render(null, 'component-form-group');

        // 监听表单提交
        form.on('submit()', function(data){
            delete data.field.file;
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