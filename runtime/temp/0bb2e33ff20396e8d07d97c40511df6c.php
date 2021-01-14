<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:63:"/www/game/public/../application/admin/view/usersharp/index.html";i:1603954265;s:51:"/www/game/application/admin/view/public/header.html";i:1603954267;s:51:"/www/game/application/admin/view/public/footer.html";i:1603954267;}*/ ?>
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
            <!-- <div class="layui-card-header"><?php echo $page_title; ?> <a href="javascript: back_url();" class="layui-btn layui-btn-primary layui-layout-right">返回上级</a></div> -->
            <div class="layui-card-body">
                <form class="layui-form padsome" action="">
                    <div class="layui-form-item">
                        <input type="hidden" name ="id[]"  value="<?php echo $list[0]['id']; ?>">
                        <label class="layui-form-label">分享奖励</label>
                        <div class="layui-input-block">
                            <select name ="reward_id[]">
                                <option>请选择奖励类型</option>
                                <?php foreach($reward as $vo): ?>
                                    <option value ="<?php echo $vo['id']; ?>" <?php if(($list[0]['reward_id'] == $vo['id'])): ?> selected<?php endif; ?>><?php echo $vo['name']; ?></option>                                
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">分享限制</label>
                        <div class="layui-input-block">
                            <input type="text" name="limit_count" <?php if(!empty($list[0]['limit_count'])): ?> value="<?php echo $list[0]['limit_count']; ?>" <?php endif; ?> lay-verify="required" placeholder="" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <input type="hidden" name ="id[]"  value="<?php echo $list[1]['id']; ?>">
                        <label class="layui-form-label">注册奖励</label>
                        <div class="layui-input-block">
                            <select name ="reward_id[]">
                                <option>请选择奖励类型</option>
                                <?php foreach($reward as $vo): ?>
                                    <option value ="<?php echo $vo['id']; ?>" <?php if(($list[1]['reward_id'] == $vo['id'])): ?> selected<?php endif; ?>><?php echo $vo['name']; ?></option>                                
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <input type="hidden" name ="id[]"  value="<?php echo $list[2]['id']; ?>">
                        <label class="layui-form-label">购鸡奖励</label>
                        <div class="layui-input-block">
                            <select name ="reward_id[]">
                                <option>请选择奖励类型</option>
                                <?php foreach($reward as $vo): ?>
                                    <option value ="<?php echo $vo['id']; ?>" <?php if(($list[2]['reward_id'] == $vo['id'])): ?> selected<?php endif; ?>><?php echo $vo['name']; ?></option>                                
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <div class="layui-footer" style="left: 0;">
                                <button class="layui-btn" lay-submit="">提交</button>

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
            Post("<?php echo url('Usersharp/edit'); ?>", data.field, function(res) {
                if (res.code == 1) {
                    alert_success(res.msg, function() {
                        location.reload()
                    })
                } else {
                    alert_error(res.msg);
                }
            });
            return false;
        });
    });
</script>