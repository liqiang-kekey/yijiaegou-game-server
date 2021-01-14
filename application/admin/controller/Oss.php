<?php


namespace app\admin\controller;


use OSS\OssClient;
// 阿里云oss上传服务
class Oss
{
    /**
     * OSS单文件上传
     */
    public function upload_file() {
        try {
            if(empty($_FILES['file'])) json_response(1, '文件不能为空');
            // 多文件上传
            if(is_array($_FILES['file']['name'])) {
                $file_list = [];
                foreach ($_FILES['file'] as $key=>$item) {
                    for($i=0; $i<count($item); $i++) {
                        $file_list[$i][$key] = $item[$i];
                        if($key == 'size' && $item[$i] > 1024*1024*2) {
                            json_response(1, '最大上传2M');
                        }
                    }
                }
                require_once VENDOR_PATH. 'autoload.php';
                $config = config('oss');
                $ossClient = new OssClient($config['access_key_id'], $config['access_key_secret'], $config['upload_url'], false);
                $src_list = [];
                foreach ($file_list as $file) {
                    $arr = explode('.', $file['name']);
                    $ext = count($arr) > 1 ? end($arr) : '';
                    $save_name = $config['static_path'].date('Ymd').'/'.time().uniqid().(empty($ext) ? '' : ".{$ext}");
                    $res = $ossClient->uploadFile($config['bucket_name'], $save_name, $file['tmp_name']);
                    // 删除临时文件
                    @unlink($file);
                    $src_list[] = $config['static_url'].'/'.$save_name;
                }
                json_response(0, '上传成功', [
                    'src' => $src_list
                ]);
            }else { // 单文件上传
                if($_FILES['file']['size'] > 1024*1024*200) json_response(1, '最大上传2M');
                $file = $_FILES['file']['tmp_name'];
                // 开始上传
                require_once VENDOR_PATH. 'autoload.php';
                $config = config('oss');
                $ossClient = new OssClient($config['access_key_id'], $config['access_key_secret'], $config['upload_url'], false);
                $arr = explode('.', $_FILES['file']['name']);
                $ext = count($arr) > 1 ? end($arr) : '';
                $save_name = $config['static_path'].date('Ymd').'/'.time().uniqid().(empty($ext) ? '' : ".{$ext}");
                $res = $ossClient->uploadFile($config['bucket_name'], $save_name, $file);

                // 删除临时文件
                @unlink($file);

                if(!empty($res['info']['url'])) {
                    json_response(0, '上传成功', [
                        'src' => $config['static_url'].'/'.$save_name
                    ]);
                }else {
                    json_response(1, '上传失败');
                }
            }
        }catch (\Exception $e) {
            json_response(1, '404');
        }
    }

    /**
     * 读取网络图片并存储到OSS
     * @param string $url
     * @param string $ext
     * @return string
     * @throws \OSS\Core\OssException
     * @date 2020/6/3 18:13
     */
    public function read_img_save($url='', $ext='png') {
        $img = chunk_split(base64_encode(file_get_contents($url)));
        $img = str_replace(" ", '+', $img);
        $img = str_replace('data:image/jpeg;base64,', '', $img);
        $img = str_replace('data:image/jpg;base64,', '', $img);
        $img = str_replace('data:image/png;base64,', '', $img);
        $img = base64_decode($img);
        // 开始上传
        require_once VENDOR_PATH. 'autoload.php';
        $config = config('oss');
        $ossClient = new OssClient($config['access_key_id'], $config['access_key_secret'], $config['upload_url'], false);
        $save_name = $config['static_path'].date('Ymd').'/'.time().uniqid().(empty($ext) ? '' : ".{$ext}");
        $res = $ossClient->putObject($config['bucket_name'], $save_name, $img);
        return $config['static_url'].'/'.$save_name;
    }

    /**
     * base64上传
     * @param string $base64
     * @param string $ext
     * @return string
     * @throws \OSS\Core\OssException
     * @date 2020/8/10 18:37
     */
    public function base64_upload($base64='', $ext='.png') {
//        if(empty($base64)) json_response(0, 'base64文件不能为空');
        $base64 = str_replace(" ", '+', $base64);
        $base64 = str_replace('data:image/jpeg;base64,', '', $base64);
        $base64 = str_replace('data:image/jpg;base64,', '', $base64);
        $base64 = str_replace('data:image/png;base64,', '', $base64);
        $base64 = base64_decode($base64);
        // 开始上传
        require_once VENDOR_PATH. 'autoload.php';
        $config = config('oss');
        $ossClient = new OssClient($config['access_key_id'], $config['access_key_secret'], $config['upload_url'], false);
        $save_name = $config['static_path'].date('Ymd').'/'.time().uniqid().$ext;
        $res = $ossClient->putObject($config['bucket_name'], $save_name, $base64);
        if(!empty($res['info']['url'])) {
            return $config['static_url'].'/'.$save_name;
        }else {
            json_response(1, '上传失败');
        }
    }
}