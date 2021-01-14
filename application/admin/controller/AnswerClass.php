<?php
namespace app\admin\controller;
use app\common\service\AdminBase;
use app\api\model\AnswerClass as AnswerClassModel;

/**
 * 趣味问答类型设置
 * auth:yaodunyuan
 */
class AnswerClass extends AdminBase{

    var  $answerclassmodel;
    var  $list ;
    var  $id ;
    function _initialize($pagesize  = 15){
        $id = $this->id = input('id');
        $answerclassmodel = $this->answerclassmodel = new AnswerClassModel();
        $this->list  = $answerclassmodel->paginate($pagesize,false);
    }

    /**
     * 趣味问答类型列表
     */
    function index (){
        return view()->assign([
            'list' => $this->list ?? null ,
            'page' => isset($this->list) ? $this->list->render() : null
        ]);
    }

    /**
     * 趣味答题类型编辑、添加
     */
    function edit(){
        $id = $this->id;
        if(request()->isPost()){
            if($id) {
                //编辑
                $this->answerclassmodel->where('id', $id)->update($_POST) > 0 ? show(1, '操作成功') : show(0, '操作失败');
            }else{
                //添加
                $_POST['create_time'] = date('Y-m-d H:i:s');
                $this->answerclassmodel->save($_POST) > 0 ? show(1, '操作成功') : show(0, '操作失败');
            }
        }
        $item = $this->answerclassmodel->where('id',$this->id )->find();
        return view()
            ->assign([
                'item' => $item,
            ]);
    }

    /**
     * 趣味添加
     */
    function add(){
        return view('edit');
    }

    /**
     * 趣味答题类型删除
     */
    function delete(){
        $id = $this->id;
        if($id){
           $res =  $this->answerclassmodel->where('id',$id)->delete();
           $res ? show(1, '操作成功') : show(0, '操作失败');
        }
       
    }
}