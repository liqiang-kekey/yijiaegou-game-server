<?php
namespace app\api\model;
use think\Model;
use think\Db;

class Shop extends Model{
        //设置默认值
        protected $table = '';//设置数据库表
        protected $pk = 'id';//设置主键
        protected $autoWriteTimeTamp =  'true';
        protected $createTime = 'create_time'; //自动创建时间
        protected $updateTime = 'update_time'; //自动完成时间

        /**
         * 库存删减
         * @param id 商品编号
         * @param half 符号
         * @param number
         */
        function stock_update($id = 0, $half =  '+' ,$number =0){
                if(!$id || !$number) return false;
                $goods = Db::table('raisingchickens_shop_goods')->where('id',$id)->find();
                if(!$goods) return false;
                $sql  = '';
                if(in_array($half,['+','-','*','/'])){
                    //减
                    if($half == '-'){
                        $sql = " UPDATE `raisingchickens_shop_goods` SET stock=stock - {$number} WHERE id = {$goods['id']} ;";
                    }
                }
                if(Db::execute($sql)){
                    return true;
                }
                return false; 
        }
}