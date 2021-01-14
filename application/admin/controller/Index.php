<?php


namespace app\admin\controller;


use app\common\service\AdminBase;
// 首页
class Index extends AdminBase
{
    /**
     * 后台首页
     */
    public function index() {
        return $this->fetch('base/index');
    }

    /**
     * 欢迎页
     * @date 2020/6/3 14:10
     */
    public function welcome() {
        $preson_count = db('user')->field('id')->select();
        $c_count = db('user_chicken')->field('id')->select();

        return view('index/welcome')->assign([
            'pcount'        => count($preson_count) ,
            'todaycount'    => count(db('user')->where('create_time','>',date('Y-m-d'))->field('id')->select()),
            'ccount'        => count($c_count) ,
            'todayccount'   => count(db('user_chicken')->where('create_time','>',date('Y-m-d'))->field('id')->select()),
            'countmoney'    => db('applet_order')->where(['status' => 1])->field('sum(pay_money) m')->find()['m'] ?? 0,
            'todaymoney'    => db('applet_order')->where(['status' => 1,'pay_time' => ['>',strtotime(date('Y-m-d'))]])->field('sum(pay_money) m')->find()['m'] ?? '0.00',
            'outeggcount'   => db('user_chicken_having')->where(['class' => 2])->field('count(id) s')->find()['s'] ?? 0,
            'todayouteggcount' => db('user_chicken_having')->where(['class' => 2,'create_time' => ['>',date('Y-m-d')]])->field('count(id) s')->find()['s'] ?? 0,
        ]);
    }
}