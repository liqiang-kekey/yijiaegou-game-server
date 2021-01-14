<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:147:"C:\Users\iFunk\Desktop\layuiAdmin\sanguo\vrupup.com\sanguo\yaodunyuan\applet\chicken\public/../application/admin\view\base\admin_menu\add_menu.html";i:1597303728;s:126:"C:\Users\iFunk\Desktop\layuiAdmin\sanguo\vrupup.com\sanguo\yaodunyuan\applet\chicken\application\admin\view\public\header.html";i:1596526912;s:126:"C:\Users\iFunk\Desktop\layuiAdmin\sanguo\vrupup.com\sanguo\yaodunyuan\applet\chicken\application\admin\view\public\footer.html";i:1596526912;}*/ ?>
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
                        <label class="layui-form-label">父级菜单</label>
                        <div class="layui-input-block">
                            <select name="parent_id" lay-filter="change_parent_menu">
                                <option value="0" data-module="admin">请选择父级菜单</option>
                                <?php foreach($parent_menu as $item): if($item['level'] <= 2): ?>
                                <option value="<?php echo $item['id']; ?>" data-module="<?php echo $item['module']; ?>" data-controller="<?php echo $item['controller']; ?>" data-level="<?php echo $item['level']; ?>" <?php if($data['parent_id'] == $item['id']): ?>selected<?php endif; ?>><?php echo $item['name']; ?></option>
                                <?php endif; endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!--<div class="layui-form-item">-->
                        <!--<label class="layui-form-label">图片测试</label>-->
                        <!--<div class="layui-input-block">-->
                            <!--<input type="hidden" name="img" id="img">-->
                        <!--</div>-->
                    <!--</div>-->
                    <div class="layui-form-item">

                        <label class="layui-form-label">菜单名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="name" value="<?php echo $data['name']; ?>" lay-verify="required" autocomplete="off" placeholder="请输入菜单名称" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">模块名</label>
                        <div class="layui-input-block">
                            <div class="layui-input-inline">
                                <input type="text" name="module" value="<?php echo !empty($data['module'])?$data['module'] : 'admin'; ?>" lay-verify="required" placeholder="请输入模块名" autocomplete="off" class="layui-input">
                            </div>
                            <label class="layui-form-label">控制器名</label>
                            <div class="layui-input-inline">
                                <input type="text" name="controller" value="<?php echo $data['controller']; ?>" placeholder="请输入控制器名" autocomplete="off" class="layui-input">
                            </div>
                            <label class="layui-form-label">方法名</label>
                            <div class="layui-input-inline">
                                <input type="text" name="action" value="<?php echo $data['action']; ?>" placeholder="请输入方法名" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-input-block layui-form-intro">
                            <div class="layui-form-mid layui-word-aux">例：一级菜单控制器和方法可留空</div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">菜单Icon</label>
                        <div class="layui-input-block">
                            <div class="layui-input-inline">
                                <input type="text" name="icon" value="<?php echo $data['icon']; ?>" placeholder="请输入菜单Icon" autocomplete="off" class="layui-input">
                            </div>
                            <label class="layui-form-label">按钮样式</label>
                            <div class="layui-input-inline">
                                <input type="text" name="style" value="<?php echo $data['style']; ?>" placeholder="请输入按钮样式" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-input-block layui-form-intro">
                            <div class="layui-form-mid layui-word-aux" style="float: none;">填写layui的CSS样式 例：<span class="layui-badge">layui-icon-delete</span>
                                <a href="https://www.layui.com/doc/element/icon.html" target="_blank">icon文档</a>
                                <a href="https://www.layui.com/doc/element/button.html" target="_blank">按钮文档</a>
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">排序</label>
                        <div class="layui-input-block">
                            <input type="text" name="sort" value="<?php echo !empty($data['sort'])?$data['sort'] : 99; ?>" lay-verify="required" placeholder="请输入排序" autocomplete="off" class="layui-input">
                        </div>
                        <div class="layui-input-block layui-form-intro">
                            <div class="layui-form-mid layui-word-aux">例：序号越小排序越靠前</div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">状态</label>
                        <div class="layui-input-block">
                            <input type="radio" name="status" value="1" title="启用" <?php if($data['status'] == '1' OR !isset($data['status'])): ?>checked=""<?php endif; ?>>
                            <input type="radio" name="status" value="0" title="禁用" <?php if($data['status'] == '0'): ?>checked<?php endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">BuildPage</label>
                        <div class="layui-input-block">
                            <input type="radio" name="build_page" value="" title="不创建" checked>
                            <input type="radio" name="build_page" value="table" title="Table页面">
                            <input type="radio" name="build_page" value="form" title="Form页面">
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
    layui.use(['index', 'form'], function() {
        var $ = layui.$
            ,form = layui.form;

        // 渲染表单样式
        form.render(null, 'component-form-group');

        //uploadInit('img', 'images');

        // 监听父级菜单选中
        form.on('select(change_parent_menu)', function(data) {
            var module     = $(data.elem).find('option:selected').data('module');
            var controller = $(data.elem).find('option:selected').data('controller');
            var level      = $(data.elem).find('option:selected').data('level');
            $('input[name=module]').val(module);
            $('input[name=controller]').val(controller);
            if(level == 2) $('input[name=style]').val('layui-btn-xs ');
        });

        // 监听提交
        form.on('submit()', function(data){
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