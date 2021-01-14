<?php
namespace app\api\controller;

use think\Controller;
use app\api\model\User;
use app\api\model\Answer;
use app\api\model\SysAnswer;
use app\api\model\UserAnswer as UserAnserModel;
use app\api\model\AnswerClass;
use app\api\model\UserDoAnswer;
use app\api\model\UserChicken;

class Useranswer extends Controller
{
    //模型
    protected $answer_model;        //趣味问答模型
    protected $answer_class_model ; //趣味问答类型模型
    protected $sysanswer_model;     //系统设置趣味类型模型
    protected $user_answer_model ;  //用户题库模型
    protected $user_doanswer_model; //用户趣味答题模型
    protected $user_model;          //用户模型
    protected $user ;               //用户信息
    protected $list ;               //趣味集合
    protected $pagesizi ;           //分页
    protected $time;                //时间
    protected $user_chicken_model ; //用户小鸡
    protected $user_chicken_having_model ;//用户小鸡行为
    //初始化
    public function _initialize($pagesizi = 5)
    {
        if (request()->isPost()) {
            $openid = input('openid');
            $user_model = $this->user_model = new User();                           //用户模型
            $user_answer_model = $this->user_answer_model = new UserAnserModel();   //用户题库模型
            $answer_class_model = $this->answer_class_model = new AnswerClass();    //题库类型模型
            $sysanswer_model = $this->sysanswer_model = new SysAnswer();            //系统题库设置模型
            $answer_model = $this->answer_model = new Answer();                     //题库模型
            $user_doanswer_model = $this->user_doanswer_model = new UserDoAnswer(); //用户做任务模型
            $user_chicken_model = $this->user_chicken_model = new UserChicken();    //用户小鸡模型
            
            $time = $this->time = date('Y-m-d H:i:s');

            //获取用户
            if(!$user = $this->user =$this->user_model->getByOpenid($openid)) show(0,'用户尚未注册');

            //如果设系统的趣味设置答题为null,添加趣味答题
            if (!$sysanswer_row = $sysanswer_model->where('id',1)->find()) {
                $answer_class_row = $answer_class_model->get(1);
                $sysanswer_model->answer_class_id = $answer_class_row['id'];
                $sysanswer_model->answer_class_name = $answer_class_row['name'];
                $sysanswer_model->number = 5;       //每次返回5个
                $sysanswer_model->israndom = 1;     //开启随机
                $sysanswer_model->week_day = 1;     //每周1更新
                $sysanswer_model->update_time = $this->time;
                $sysanswer_model->save();
            }

            //今天是周一，更新题目
            if (date('w', time()) == $sysanswer_row->week_day && date_diff(date_create(explode(' ', $sysanswer_row->update_time)[0]), date_create(date('Y-m-d')))->d > 6) {
                $list_class_data = $answer_class_model->select();
                $randmo = random_int(0, count($list_class_data));
                
                while (true) {
                    if ($sysanswer_row->answer_class_id == $list_class_data[$randmo]->id) {
                        //重新生成随机数
                        $randmo =  random_int(0, count($list_class_data));
                    } else {
                        //修改当前的系统设置
                        $sysanswer_model->where('id', $sysanswer_row->id)->update([
                            'update_time' => $this->time,
                            'answer_class_id' => $list_class_data[$randmo]->id,
                            'answer_class_name' => $list_class_data[$randmo]->name,
                        ]);
                        break;
                    }
                }
            } else {
                //当前用户今日是否答题
              
                if (!$user_do_answer_list = $user_answer_model->where('create_time', '>', date('Y-m-d'))->find()) {
                    //查询所有的题库
                    $answer_data_list = $answer_model->where('class_id', $sysanswer_row->answer_class_id)->select();
                  
                    //获取昨日答题
                    $last_answer_data_list = $user_answer_model->where('submit_time', '>', date('Y-m-d', strtotime('-1 day')))->whereOr('submit_time', '<', date('Y-m-d'))->select();
                    if (!$last_answer_data_list) {
                        if(count($answer_data_list) == 0){
                            show(0,'当前题目类型暂无题目');
                        }
                        if(count($answer_data_list) <= $sysanswer_row->number && count($answer_data_list) <= 3){
                            $list = $this->list = $answer_data_list;
                        }else{
                            $list = $this->list = array_rand($answer_data_list,$sysanswer_row->number);
                        } 
                       
                    } else {
                        //剔除昨日答题
                        foreach ($last_answer_data_list as $k => $v) {
                            if ($key = array_search($v, $answer_data_list)) {
                                array_splice($answer_data_list, $key, 1);
                            }
                        }
                        if(count($answer_data_list) == 0){
                            show(0,'当前题目类型暂无题目');
                        }
                        if(count($answer_data_list) <= $sysanswer_row->number && count($answer_data_list) <= 3){
                            $list = $this->list = $answer_data_list;
                        }else{
                            $list = $this->list = array_rand($answer_data_list,$sysanswer_row->number);
                        } 
                    }
                    
                    //加入用户题库
                    if (count($answer_data_list) <= $sysanswer_row->number && count($answer_data_list) <= 3) {
                        $data_arrary =  [];
                        for ($i = 0; $i < count($list);$i++) {
                            $data['answer_class_id'] = $list[$i]['class_id'];
                            $data['answer_class_name'] = $list[$i]['class_name'];
                            $data['answer_id'] = $list[$i]['id'];
                            $data['answer_title'] = $list[$i]['title'];
                            $data['answer_reall'] = $list[$i]['really'];
                            $data['create_time'] = $this->time;
                            $data_arrary[$i] = $data;
                        }
                        $user_answer_model->saveAll($data_arrary);
                        $this->list = $data_arrary;
                    }else{
                        $data_arrary =  [];
                        for ($i = 0; $i < count($list);$i++) {
                            $data['answer_class_id'] = $answer_data_list[$list[$i]]['class_id'];
                            $data['answer_class_name'] = $answer_data_list[$list[$i]]['class_name'];
                            $data['answer_id'] = $answer_data_list[$list[$i]]['id'];
                            $data['answer_title'] = $answer_data_list[$list[$i]]['title'];
                            $data['answer_reall'] = $answer_data_list[$list[$i]]['really'];
                            $data['create_time'] = $this->time;
                            $data_arrary[$i] = $data;
                        }
                        $user_answer_model->saveAll($data_arrary);
                        $this->list = $data_arrary;
                    }
                } else {
                    
                    $user_answer_list = $user_answer_model->where(['create_time'=>[ '>', date('Y-m-d') ] ,'submit_time' => [ '>', date('Y-m-d') ] ])->find(1);
                    if (!$user_answer_list) {
                        $this->list = $user_answer_model->where(['create_time'=>[ '>', date('Y-m-d') ]  ])->limit(5)->select();
                    } else {
                        $this->list = null;
                    }
                }
            }
        } else {
            show(0, '非法请求');
        }
    }

    /**
     * 获取用户题目
     */
    public function get_answer_list():json
    {
        if(!$list = $this->list) show(0,'暂无题目');
        //var_dump(implode(',',array_column($list,'answer_id')));
        $substring = implode(',',array_column($list,'answer_id'));
        //print_r($substring);
        $answer_list = $this->answer_model->where(['id'=>['in',$substring]])->select();
        //当前题库被删除则重新生成题目
        if(!$answer_list) {
            //当前是否选择了题库
            $current_sys_answer_select = $this->sysanswer_model->find(1);
            if(!$current_sys_answer_select){
                //如果当前的未选择
                $featch_answer_class = $this->answer_model->group('class_id')->select();
                if(!$featch_answer_class){
                    show(0,'当前暂未上传题目');
                }
                $featch_answer_class_id = $featch_answer_class[random_int(0,count($featch_answer_class))];
                //当前题型更换成现在的
                $this->sysanswer_model->answer_class_id = $featch_answer_class_id['class_id'];
                $this->sysanswer_model->answer_class_name = $featch_answer_class_id['class_name'];
                $this->sysanswer_model->number = 5;       //每次返回5个
                $this->sysanswer_model->israndom = 1;     //开启随机
                $this->sysanswer_model->week_day = 1;     //每周1更新
                $this->update_time = $this->time;
                $this->sysanswer_model->save();
                $current_sys_answer_select = $this->sysanswer_model->find(1);
            }
            //获取所有的题目
            $answer_list_all = $this->answer_model->where('class_id',$current_sys_answer_select->answer_class_id)->select();
            //重新生成随机数
            $data_list = array_rand($answer_list_all,$current_sys_answer_select->number);
            foreach ($data_list as $k=>$v){
                $data['answer_class_id'] = $answer_list_all[$k]['class_id'];
                $data['answer_class_name'] = $answer_list_all[$k]['class_name'];
                $data['answer_id'] = $answer_list_all[$k]['id'];
                $data['answer_title'] = $answer_list_all[$k]['title'];
                $data['answer_reall'] = $answer_list_all[$k]['really'];
                $data['create_time'] = $this->time;
                $data_arrary[$k] = $data;
            }
            
            $this->user_answer_model->saveAll($data_arrary);
            $answer_list = $data_arrary;
        }
        show(1,'查询成功',$answer_list);
    }

    /**
     * 校验用户答题
     * @param openid
     * @param data 
     * @return json
     */
    function do_answer(){
        $user_chicken_ids = $this->user_chicken_model->where(['uid' => $this->user->id,'isvip' => 1])->select(); 
        if(!$user_chicken_ids) show(0,'当前暂无小鸡');
        if(!$class = input('class')) show(0,'缺少类型');

        if ($class == 1) {
            if (!$data  = input('data')) {
                show(0, '缺少参数data');
            }
            //print_r($this->user_doanswer_model->where(['submit_time' => ['>',date('Y-m-d')],'uid'=>$this->user->id  ])->find());
            if (!$is_do = $this->user_doanswer_model->where(['submit_time' => ['>',date('Y-m-d')],'uid'=>$this->user->id  ])->find()) {
                $data = json_decode($data, true);
                $answer_data = $this->answer_model->where(['id'=>['in',array_column($this->list, 'answer_id')]])->field('id,class_id,class_name,title,really,really,integral')->select();
                //处理数组
                $jf = 0;
                $count = 0;
                $do = [];
                foreach ($answer_data as $k => $v) {
                    if ($v['really'] == $data[$k]) {
                        $jf += $v['integral'];
                        $count ++;
                    }
                    $ds = [
                    'uid'               => $this->user->id,
                    'answer_class_id'   => $v->class_id,
                    'answer_class_name' => $v->class_name,
                    'answer_id'         => $v->id,
                    'answer_title'      => $v->title,
                    'answer_reall'      => $v->really,
                    'user_selected'     => $data[$k],
                    'integral'          => $v['integral'],
                    'status'            => $v['really'] == $data[$k] ? 1 :2,
                    'create_time'       => $this->time,
                    'submit_time'       => $this->time,
                ];
                    $do[] = $ds;
                }
                //插入用户今日答题
                $this->user_doanswer_model->saveAll($do);
                $data = [
                    'integral' => $jf,
                    'proportion' => $count.'/'.count($answer_data),  //比例
                    'accuracy' => ($count/count($answer_data) * 100).'%', //正确率
                ];
                //修改人物积分
                if ($jf > 0) {
                    $this->user_model->where('id', $this->user->id)->update([
                        'integral' => $this->user->integral + $jf,
                    ]);
                }
                foreach ($user_chicken_ids as $v) {
                    //加入完成活跃度
                    db('user_chicken_having')->insert([
                        'uid'           => $this->user['id'],
                        'c_id'          => $v['id'],
                        'class'         => 3, //活跃度
                        'name'          => '增加活跃度',
                        'activity'      => 5,
                        'date'          => date('Y-m-d'),
                        'create_time'   => $this->time,
                    ]);
                    //修改状态
                    if($v['activity'] + 5 >= 100){
                        $this->user_chicken_model->where('id',$v['id'])->update(['activity' => 100]);
                    }else{
                        $this->user_chicken_model->where('id',$v['id'])->setInc('activity',5);
                    }
                }
                show(1, '成功', ['user' => $this->user_model->where('id', $this->user->id)->find(),'data' => $data]);
            }
            show(0, '今天答题任务已完成，请明天再答');
        }
    }
}
