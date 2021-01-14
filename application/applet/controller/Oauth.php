<?php


namespace app\applet\controller;

// 授权模块
use think\Env;

class Oauth extends Base
{
    /**
     * 用户授权登录
     * @date 2020/8/12 14:12
     */
    public function oauth_login() {
        try {
            $js_code    = urldecode(param_check('js_code')); // 小程序wx.login获取的code, 必填参数
            $app_id     = Env::get('applet_app_id'); // 小程序 app_id
            $app_secret = Env::get('applet_app_secret'); // 小程序 app_secret
            // 请求微信授权接口, 获取openid
            $url  = "https://api.weixin.qq.com/sns/jscode2session?appid={$app_id}&secret={$app_secret}&js_code={$js_code}&grant_type=authorization_code";
            $data = json_decode(curl('GET', $url), true);
            // 判断接口是否请求成功
            if(empty($data['openid'])) {
                json_response(0, $data['errmsg'], $data);
            }
            // 判断openid是否存在
            $user = db('applet_user')
                ->where('openid', $data['openid'])
                ->field('id as user_id, openid, avatar, nickname, unionid')
                ->find();
           
            if(empty($user)) {
                $user_id = db('applet_user')->insertGetId([
                    'openid'      => $data['openid'],
                    'unionid'     => isset($data['unionid']) ? $data['unionid'] : '',
                    'create_time' => time()
                ]);
                $user = [
                    'user_id'  => $user_id,
                    'unionid'  => isset($data['unionid']) ? $data['unionid'] : '',
                    'openid'   => $data['openid'],
                    'mobile'   => '',
                    'avatar'   => '',
                    'nickname' => '',
                ];
                // 判断openid是否存在
                $user = db('applet_user')
                        ->where('openid', $data['openid'])
                        ->field('id as user_id, openid, avatar, nickname, unionid')
                        ->find();
            }
            

            json_response(1, 'success', [
                'session_key' => $data['session_key'],
                'user_info'   => $user
            ]);
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }


    /**
     * unionid解密
     * @date 2020/8/12 14:19
     */
    public function de_unionid() {
        try {
            $user_id      = $this->get_user_id();
            $app_id       = Env::get('applet_app_id'); // 小程序 app_id
            $session_key  = urldecode(param_check('session_key')); // get_session_key接口获得的session_key
            $iv           = urldecode(param_check('iv')); // 点击授权按钮获取的iv
            $e_data       = urldecode(param_check('e_data')); // 点击授权按钮获取encryptedData
            // AES解密
            $result = $this->decrypt_data($e_data, $iv, $session_key, $data, $app_id);
            // 判断结果
            if($result == 0) {
                $data = json_decode($data, true);
                if(empty($data['unionId'])) {
                    json_response(0, 'unionId为空', $data);
                }
                db('applet_user')->where('id', $user_id)->update([
                    'nickname'    => $data['nickName'],
                    'avatar'      => $data['avatarUrl'],
                    'unionid'     => isset($data['unionId']) ? $data['unionId'] : '',
                    'update_time' => time()
                ]);


                json_response(1, '解密成功', [
                    'unionid' => isset($data['unionId']) ? $data['unionId'] : ''
                ]);
            }else {
                json_response(0, '解密失败, 错误代码：'.$result, [
                    'session_key' => $session_key,
                    'id'          => $iv,
                    'e_data'      => $e_data
                ]);
            }
        } catch (\Exception $e) {
            json_response(0, '接口错误', [
                'info' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $sessionKey string getCode返回的session_key参数
     * @param $data string 解密后的原文
     * @param $app_id string AppId
     *
     * @return int 成功0，失败返回对应的错误码
     */
    private function decrypt_data($encryptedData, $iv, $sessionKey, &$data, $app_id) {
        $ErrorCode = [
            'IllegalAesKey'=> -41001, // encodingAesKey 非法
            'IllegalIv' => -41002,
            'IllegalBuffer' => -41003, // aes 解密失败
            'DecodeBase64Error' => -41004, // 解密后得到的buffer非法
            'OK' => 0,
        ];
        if (strlen($sessionKey) != 24) {
            return $ErrorCode['IllegalAesKey'];
        }
        $aesKey=base64_decode($sessionKey);
        if (strlen($iv) != 24) {
            return $ErrorCode['IllegalIv'];
        }
        $aesIV=base64_decode($iv);
        $aesCipher=base64_decode($encryptedData);
        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj=json_decode( $result );
        if( $dataObj  == NULL ) {
            return $ErrorCode['IllegalBuffer'];
        }
        if( $dataObj->watermark->appid != $app_id)
        {
            return $ErrorCode['IllegalBuffer'];
        }
        $data = $result;
        return $ErrorCode['OK'];
    }
}