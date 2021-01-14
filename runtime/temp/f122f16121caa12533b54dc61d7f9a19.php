<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:136:"C:\Users\iFunk\Desktop\layuiAdmin\sanguo\vrupup.com\sanguo\yaodunyuan\applet\chicken\public/../application/admin\view\Task\add_task.html";i:1597717449;s:126:"C:\Users\iFunk\Desktop\layuiAdmin\sanguo\vrupup.com\sanguo\yaodunyuan\applet\chicken\application\admin\view\public\header.html";i:1596526912;s:126:"C:\Users\iFunk\Desktop\layuiAdmin\sanguo\vrupup.com\sanguo\yaodunyuan\applet\chicken\application\admin\view\public\footer.html";i:1596526912;}*/ ?>
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
                        <label class="layui-form-label">任务类型</label>
                        <div class="layui-input-block">
                            <select name="class" lay-verify="required" lay-filter="cls">
                                <option value=""></option>
                                <option value="1" <?php if($data['class'] == 1): ?>selected<?php endif; ?>>签到</option>
                                <option value="2" <?php if($data['class'] == 2): ?>selected<?php endif; ?>>游览商城</option>
                                <option value="3" <?php if($data['class'] == 3): ?>selected<?php endif; ?> >观看视频</option>
                                <option value="4" <?php if($data['class'] == 4): ?>selected<?php endif; ?>>做任务</option>
                                <option value="5" <?php if($data['class'] == 5): ?>selected<?php endif; ?>>答题</option>
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">任务名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="name" value="<?php echo $data['name']; ?>" lay-verify="required" placeholder="" autocomplete="off" class="layui-input">
                        </div>
                    </div>


                    <div class="layui-form-item">
                        <label class="layui-form-label">观看时长</label>
                        <div class="layui-input-block">
                            <input type="text" name="see_time" value="<?php echo $data['see_time']; ?>" lay-verify="required" placeholder="" autocomplete="off" class="layui-input">
                        </div>
                        <div class="layui-input-block layui-form-intro">
                            <div class="layui-form-mid layui-word-aux">单位秒</div>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">礼物</label>
                        <div class="layui-input-block">
                            <select name="reward_id" lay-verify="required">
                                <option value=""></option>
                                <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): if( count($list)==0 ) : echo "" ;else: foreach($list as $key=>$value): ?>
                                <option value="<?php echo $value['id']; ?>" <?php if($data['reward_id'] == $value['id']): ?>selected<?php endif; ?>><?php echo $value['name']; ?></option>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
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

        uploadInit('video', 'video', '图片尺寸 200 * 300');

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