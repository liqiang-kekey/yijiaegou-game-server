<?php
namespace app\api\model;
use think\Model;
use think\Db;

class UserCertificate extends Model{
        //设置默认值
        protected $table = '';//设置数据库表
        protected $pk = 'id';//设置主键
        protected $autoWriteTimeTamp =  'true';
        protected $createTime = 'create_time'; //自动创建时间
        protected $updateTime = 'update_time'; //自动完成时间
}