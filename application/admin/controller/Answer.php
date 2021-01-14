<?php
namespace app\admin\controller;
use app\common\service\AdminBase;
use app\api\model\Answer as AnswerModel;

/**
 * 答题任务
 * auth:yaodunyuan
 * create 2020-09-01 13:44
 */
class Answer extends AdminBase
{
    var $answermodel ;
    var $list ;
    var $name;
    var $id ;
    function _initialize($pagesize = 15)
    {
        $this->answermodel =  new AnswerModel();
        $name = $this->name = input('name');
        $id = $this->id = input('id');
        if (!$name) {
            $this->list  = $this->answermodel->paginate($pagesize, false);
        }else{
            $this->list = $this->answermodel->where(['title' =>['like' ,"{$name}%"] ])->paginate($pagesize,false);
        }
    }
    
    /**
     * 题目列表
     */
    function index(){
        return  view()
        ->assign([
            'name' => $this->name,
            'list' => $this->list ?? null ,
            'page' => $this->list->render() 
        ]);
    }


    /**
     * 上传文件
     */
    function uploadfile(){
        $file = $_FILES['file'];
        if(!in_array(explode('.',$file['name'])[1],['xls','xlsx'])){
            show(0,'上传类型错误');
        }
        $data = import_excel($file);
        if($data['count']){
            show(1,'上传成功',$data);
        }
        show(0,'上传失败');
    }

    /**
     * 编辑
     */
    function edit(){
        if (request()->isPost()) {
            $data = $_POST;
            $data['class_name'] = db('answer_class')->where('id',$data['class_id'])->field('name')->find()['name'];
            $data['really'] = join('',$data['really']);
            if(!$this->id){
                db('answer')->insert($data) ? show(1, '操作成功') : show(0, '操作失败');
            }else{
                db('answer')->where('id', $this->id)->update($data) ? show(1, '操作成功') : show(0, '操作失败');
            }
        }
        $data = $this->answermodel->where('id',$this->id)->find();
        return view()->assign([
            'item' => $data,
            'class_list' => $class_list = db('answer_class')->select(),
            'sys_rw' => db('sys_reward')->where('type',1)->select(),//给予积分
        ]);
    }

    /**
     * 添加
     */
    function add(){
        $class_list = db('answer_class')->select();
        return view('edit')->assign([
            'class_list' => $class_list,
            'sys_rw' => db('sys_reward')->where('type',1)->select(),//给予积分
        ]);
    }

    /**
     * 删除
     */
    function delete(){
        if(!$this->id) show(0,'缺少ID');
        $this->answermodel->where(['id' => $this->id])->delete() ? show(1,'操作成功') : show(0,'操作失败');
    }

    /**
     * 下载模板文件
     */
    function downfile(){
        //echo 'dome.xls';die;
        $file = fopen(ROOT_PATH.'dome.xls',"rb");
        $title = 'dome.xls';
        header("Content-Type: application/force-download"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Type: application/download"); 
        header('Content-Disposition:inline;filename="'.$title.'"'); 
        while(!feof($file)){
            echo fread($file,8192);
            ob_flush();
            flush();
        }
        fclose();
    }
} 
