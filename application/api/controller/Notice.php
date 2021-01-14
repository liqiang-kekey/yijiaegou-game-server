<?php
namespace app\api\controller;
use app\api\model\SysGame;
use think\Controller;

/**
 * 公告
 * 
 */
class Notice extends Controller
{
    var $notice_model;
    function _initialize(){
        $notice_model = $this->notice = new SysGame();
    }

    /**
     * 获取系统设置公告
     * @return json
     */
    public function get_notice()
    {   
        //查询一起用的设置
        $notice = $this->notice->where(['isenable' => 1])->field('notice,logostarttime,logoendtime')->find();
        if (strtotime($notice['logostarttime'])  <= time() && time() <=strtotime($notice['logoendtime'])) {
            if (!$notice) {
                show(0, '系统尚未设置公告');
            }
            show(1, '查询成功', ['notice'=>$notice['notice']]);
        }else{
            show(0, '公告已过期');
        }
    }
}