<?php
namespace app\api\model;

use think\Model;
use think\Db;
use app\api\model\UserChicken;

class User extends Model
{
    //设置默认值
    protected $table = '';//设置数据库表
    protected $pk = 'id';//设置主键
    protected $autoWriteTimeTamp =  'true';
    protected $createTime = 'create_time'; //自动创建时间
    protected $updateTime = 'update_time'; //自动完成时间

    /**
     * 用户积分增减
     * @param uid 用户编号
     * @param half 加减乘除 符号
     * @param integral 积分
     */
    public function update_integral($uid = 0, $half = '+', $integral = 0)
    {
        if (!$uid) {
            return false;
        }
        if (!$integral) {
            return false;
        }
        //查询用户
        $user = Db::name('user')->where(['id' => $uid])->find();
        if (!$user) {
            return false;
        }
        $sql  = '';
        if (in_array($half, ['+','-','*','/'])) {
            //加
            if ($half == '+') {
                $sql = " UPDATE `raisingchickens_user` SET integral=integral + {$integral} WHERE id = {$user['id']} ;";
            }
            //减
            if ($half == '-') {
                $sql = " UPDATE `raisingchickens_user` SET integral=integral - {$integral} WHERE id = {$user['id']} ;";
            }
        }
        if (Db::execute($sql)) {
            return true;
        }
        return false;
    }

    /**
     * 用户鸡蛋增减
     * @param uid 用户编号
     * @param half 加减乘除 符号
     * @param egg 鸡蛋
     */
    public function update_egg($uid = 0, $half = '+', $egg = 0)
    {
        if (!$uid) {
            return false;
        }
        if (!$egg) {
            return false;
        }
        //查询用户
        $user = Db::name('user')->where(['id' => $uid])->find();
        if (!$user) {
            return false;
        }
        $sql  = '';
        if (in_array($half, ['+','-','*','/'])) {
            //加
            if ($half == '+') {
                $sql = " UPDATE `raisingchickens_user` SET egg=egg + {$egg} WHERE id = {$user['id']} ;";
            }
            //减
            if ($half == '-') {
                $sql = " UPDATE `raisingchickens_user` SET egg=egg - {$egg} WHERE id = {$user['id']} ;";
            }
        }
        if (Db::execute($sql)) {
            return true;
        }
        return false;
    }

    /**
    * 收费购买
    * @param uid
    * @param number
    * @param order_sn
    */
    public function user_adopt_vip($uid  = '', $number = '', $order_sn)
    {
        try {
            if (!$uid) {
                show(0, '缺少参数');
            }
            if (!$number) {
                show(0, '缺少参数');
            }
            if (!$order_sn) {
                show(0, '缺少参数');
            }
            $user =  Db::name('user')->where('id', $uid)->field('name')->find();
            $UserChicken_model = new UserChicken();
            $images = Db::name('sys_certificate')->where(['isenable' => 1,'isdelete' => 2])->field('image')->find();
        
            $user_certificate = Db::name('user_certificate')->where('uid', $uid)->find();
            for ($i = 1;$i <= $number;$i++) {
                //获取数据
                $bh =  create_number('chicked');
                $std_number = create_certificate_number();//证书编号
            $source = 2; //1(免费,2购买,3转赠)
            $isvip = 1;
                $data['uid'] = $uid;   //用户编号
            $data['identifier'] = $std_number;//证书编号
            $data['name'] = $user['name'] ;
                if (!$user_certificate) {
                    //第一次购买
                    if ($i == 1) {
                        //只生成一张证书
                        $ref = Db::table('raisingchickens_user_certificate')->insert([
                    'uid' => $uid ,
                    'identifier' => $std_number ?? '',
                    'number' => $bh ?? '',
                    'name' => $user['name'] ?? '',
                    'imageurl' => $images['image'] ?? '',
                    'create_time' => date('Y-m-d H:i:s')
                ]);
                    } else {
                        $ref = Db::table('raisingchickens_user_certificate')->insert([
                        'uid' => $uid ,
                        'identifier' => $std_number ?? '',
                        'number' => $bh ?? '',
                        'name' => '' ,
                        'imageurl' => $images['image'] ?? '',
                        'create_time' => date('Y-m-d H:i:s')
                    ]);
                    }
                } else {
                    //再次购买
                    $ref = Db::table('raisingchickens_user_certificate')->insert([
                    'uid' => $uid ,
                    'identifier' => $std_number,
                    'number' => $bh,
                    'name' => '' ,
                    'imageurl' => $images['image'] ?? '',
                    'create_time' => date('Y-m-d H:i:s')
                ]);
                }

                $data['order_sn'] = $order_sn; //订单编号
            $data['isvip'] = $isvip;//不在封存状态
            $data['number'] = $bh ;// 宠物编号
            $data['source'] = $source;//1(免费,2购买3转赠)
            $data['activity'] = 100;//活跃值
            $data['health'] = 100;//健康值
            $data['hunger'] = 0;//饥饿值
            $data['outegg'] = 0;//免费产量为0
            $data['cycle']  = 1;//一阶段
            $data['level'] =  1;//等级
            $data['create_time'] = date('Y-m-d H:i:s');//创建时间
            $UserChicken_model->insert($data);
                unset($data);
                $arr[] = $bh;
                unset($bh);
            }
        
         
        
            //修改用户数量
            $ref = Db::name('user')->where('id', $uid)->setInc('chicken', $number);
            return $arr;
        } catch (\Exception $e) {
            db('error')->insert([
                'error'       => 3,
                'desc'        => '付费领养',
                'text'        =>  $e->getMessage().'错误发生在:'.$e->getLine(),
                'create_time' => time()
            ]);
        }
    }
}
