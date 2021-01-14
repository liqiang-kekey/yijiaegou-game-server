<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:61:"/www/game/public/../application/admin/view/index/welcome.html";i:1603954268;s:51:"/www/game/application/admin/view/public/header.html";i:1603954267;s:51:"/www/game/application/admin/view/public/footer.html";i:1603954267;}*/ ?>
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
<body class="layui-view-body">
    <div class="layui-content">
        <div class="layui-row layui-col-space20">
            <div class="layui-col-sm6 layui-col-md3">
                <div class="layui-card">
                    <div class="layui-card-body chart-card">
                        <div class="chart-header">
                            <div class="metawrap">
                                <div class="meta">
                                    <span>总用户数</span>
                                </div>
                                <div class="total"><?php echo $pcount; ?></div>
                            </div>
                        </div>
                        <div class="chart-body">
                            <div class="contentwrap">
                                <?php echo $todaycount; ?>/<?php echo $pcount; ?>
                            </div>
                        </div>
                        <div class="chart-footer">
                            <div class="field">
                                <span>今日注册量</span>
                                <span><?php echo $todaycount; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-col-sm6 layui-col-md3">
                <div class="layui-card">
                    <div class="layui-card-body chart-card">
                        <div class="chart-header">
                            <div class="metawrap">
                                <div class="meta">
                                    <span>小鸡总量</span>
                                </div>
                                <div class="total"><?php echo $ccount; ?></div>
                            </div>
                        </div>
                        <div class="chart-body">
                            <div class="contentwrap">
                                <?php echo $todayccount; ?>/<?php echo $ccount; ?>
                            </div>
                        </div>
                        <div class="chart-footer">
                            <div class="field">
                                <span>今日购鸡量</span>
                                <span><?php echo $todayccount; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-col-sm6 layui-col-md3">
                <div class="layui-card">
                    <div class="layui-card-body chart-card">
                        <div class="chart-header">
                            <div class="metawrap">
                                <div class="meta">
                                    <span>总金额</span>
                                </div>
                                <div class="total"><?php echo $countmoney; ?></div>
                            </div>
                        </div>
                        <div class="chart-body">
                            <div class="contentwrap">
                                <?php echo $todaymoney; ?>/<?php echo $countmoney; ?>
                            </div>
                        </div>
                        <div class="chart-footer">
                            <div class="field">
                                <span>今日金额</span>
                                <span><?php echo $todaymoney; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-col-sm6 layui-col-md3">
                <div class="layui-card">
                    <div class="layui-card-body chart-card">
                        <div class="chart-header">
                            <div class="metawrap">
                                <div class="meta">
                                    <span>产蛋总量</span>
                                </div>
                                <div class="total"><?php echo $outeggcount; ?></div>
                            </div>
                        </div>
                        <div class="chart-body">
                            <div class="contentwrap">
                                <?php echo $todayouteggcount; ?>/<?php echo $outeggcount; ?>
                            </div>
                        </div>
                        <div class="chart-footer">
                            <div class="field">
                                <span>今日产蛋量</span>
                                <span><?php echo $todayouteggcount; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

</body>
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
<style>
    .chart-card .chart-header {
    position: relative;
    width: 100%;
    overflow: hidden;
}
.chart-card .metawrap .total {
    overflow: hidden;
    text-overflow: ellipsis;
    word-break: break-all;
    white-space: nowrap;
    color: rgba(0,0,0,.85);
    margin-top: 4px;
    margin-bottom: 0;
    font-size: 30px;
    line-height: 38px;
    height: 38px;
}
.chart-card .chart-footer {
    padding-top: 9px;
    margin-top: 8px;
    border-top: 1px solid #e8e8e8;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
}
.layui-view-body{
    margin-top: 10px;
}

.chart-card .field span {
    font-size: 14px;
    line-height: 22px;
}

.chart-card .field span:last-child {
    margin-left: 8px;
    color: rgba(0,0,0,.85);
}
</style>