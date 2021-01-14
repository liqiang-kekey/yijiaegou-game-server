<?php

/**
 * 跨域
 */
header('Access-Control-Allow-Origin: *');

/**
 * 生成随机字符串
 * @param int $lenth
 * @return string
 */
function str_random($lenth=6)
{
    $base = 'qwertyuiopasdfghjklzxcvbnm0123456789QWERTYUIOPASDFGHJKLZXCVBNM';
    $str = '';
    for ($i=1; $i<=$lenth; $i++) {
        $str .= $base[mt_rand(0, strlen($base) - 1)];
    }
    return $str;
}

/**
 * 大写字母转下划线加小写字母(忽略首字母)
 * @param string $name
 * @return string
 */
function str_format($name='')
{
    $temp_array = array();
    for ($i=0; $i<strlen($name); $i++) {
        $ascii_code = ord($name[$i]);
        if ($ascii_code >= 65 && $ascii_code <= 90) {
            if ($i == 0) {
                $temp_array[] = chr($ascii_code + 32);
            } else {
                $temp_array[] = '_'.chr($ascii_code + 32);
            }
        } else {
            $temp_array[] = $name[$i];
        }
    }
    return implode('', $temp_array);
}

/**
 * 数组无限级分类
 * @param $arr
 * @param bool $sub_list // 是否放在子数组内
 * @param $key_val // 第一级的上级ID参数值
 * @param array $config
 * @param int $level
 * @return array
 */
function arr_tree($arr, $sub_list=false, $key_val=0, $config=[], $level=1)
{
    $parent_key = isset($config['parent_key']) ? $config['parent_key'] : 'parent_id'; // 上级ID参数名称
    $key        = isset($config['key']) ? $config['key'] : 'id'; // 主键ID参数名称
    $res = array();
    foreach ($arr as $k=>$item) {
        if ($item[$parent_key] == $key_val) {
            unset($arr[$k]);
            $item['level'] = $level;
            if ($sub_list) {
                $item['sub_list'] = arr_tree($arr, $sub_list, $item[$key], $config, $level + 1);
                $res[] = $item;
            } else {
                $res[] = $item;
                $res = array_merge($res, arr_tree($arr, $sub_list, $item[$key], $config, $level + 1));
            }
        }
    }
    return $res;
}

/**
 * 取出html内的img地址
 * @param string $content
 * @return array
 */
function html_parse_img($content='')
{
    preg_match_all('/<img.*?src="(.*?)".*?>/', $content, $matches);
    return isset($matches[1]) ? $matches[1] : [];
}

/**
 * 递归创建目录
 * @param string $dir
 * @return bool
 */
function folder_build($dir='')
{
    if (!is_dir($dir)) {
        while (!is_dir(dirname($dir))) {
            if (!folder_build(dirname($dir))) {
                json_response(0, $dir.'目录写入失败');
            }
        }
        if (!is_writable($dir)) {
            return mkdir($dir, 0777, true);
        } else {
            json_response(0, $dir.'目录不可写');
        }
    }
}

/**
 * JSON格式返回数据
 * @param int $code
 * @param string $msg
 * @param array $data
 */
function json_response($code=0, $msg='', $data=[])
{
    echo json_encode([
        'code' => $code,
        'msg'  => $msg,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * JSON格式返回数据
 * @param int $code
 * @param string $msg
 * @param array $data
 */
function show($code= 0, $msg='', $data=[])
{
    echo json_encode([
        'code' => intval($code) ?? 0,
        'msg'  => $msg,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * 参数检查
 * @param string $name
 * @param bool $default
 * @param string $tips
 * @return array|bool
 */
function param_check($name, $default=false, $tips='')
{
    $val = input($name);
    if (!empty($val)) {
        return $val;
    } else {
        if ($default !== false) {
            return $default;
        }
        json_response(0, empty($tips) ? "{$name}不能为空" : $tips);
    }
}

/**
 * Curl操作
 * @param string $type 请求类型 'POST' 或 'GET' 大小写都可以
 * @param string $url 请求地址 url
 * @param array $data 数组 cookie 请求cookie data post请求数据
 * @param bool $headerFile 返回头信息 如果页面做了跳转 则可以从返回头信息获得跳转地址，应用场景不多
 * @return bool|mixed
 */
function curl($type, $url, $data=[], $headerFile=false)
{
    $type = strtoupper($type);
    $type_list = ['POST', 'GET', 'PUT'];
    if (!in_array($type, $type_list)) {
        $type = 'POST';
    }
    $ch = curl_init();
    // 请求类型
    if ($type == 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
    } elseif ($type == 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); //设置请求方式
    }
    curl_setopt($ch, CURLOPT_URL, trim($url));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_ENCODING, ''); // 这个是解释gzip内容, 解决获取结果乱码 gzip,deflate
    // 是否存在请求字段信息
    if (!empty($data['data'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data['data']);
    }
    // 是否存在cookie
    if (!empty($data['cookie'])) {
        curl_setopt($ch, CURLOPT_COOKIE, $data['cookie']);
    }
    // 请求头
    if (!empty($data['header'])) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $data['header']);
    }
    // 设置代理
    if (!empty($data['proxy'])) {
        curl_setopt($ch, CURLOPT_PROXY, $data['proxy']);
    }
    // 证书
    if (!empty($data['ssl_cert'])) {
        curl_setopt($ch, CURLOPT_SSLCERT, $data['ssl_cert']);
    }
    if (!empty($data['ssl_key'])) {
        curl_setopt($ch, CURLOPT_SSLKEY, $data['ssl_key']);
    }

    // 返回ResponseHeader
    if ($headerFile) {
        curl_setopt($ch, CURLOPT_HEADER, 1);
    }
    // 设置请求超时时间
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    // 发送请求
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return false;
    }
    curl_close($ch);
    return $result;
}

/**
 * 数组转csv
 * @param array $data 数组
 * @param string $file_name 文件名字
 * @param array $header_name 表头名称
 */
function array_to_csv($data=[], $file_name='', $fields=[]) {
    if(empty($data) || !is_array($data)) {
        json_response(0, '数组类型错误');
    }
    // 表头
    header('Content-Type: application/vnd.ms-excel');   // header设置
    header("Content-Disposition: attachment;filename=".($file_name ? $file_name : '导出').".csv");
    header('Cache-Control: max-age=0');
    $fp = fopen('php://output','a');
    $header = empty($fields) ? array_keys($data[0]) : array_values($fields);
    $head = [];
    foreach($header as $i=>$value) {
        $value = is_array($value) ? $value[0] : $value;
        $head[$i] = iconv("UTF-8","GBK", $value);
    }
    fputcsv($fp,$head);
    foreach ($data as $i=>$item) {
        $list = [];
        foreach($fields as $k=>$v) {
            $value = isset($item[$k]) ? $item[$k] : '';
            if(is_array($v)) {
                if(is_array($v[1])) {
                    // 数组类型处理
                    $value = $v[1][$value];
                }else if(is_callable($v[1])) {
                    // 匿名函数处理
                    $value = $v[1]($item);
                }else if(is_string($v[1])) {
                    // 字符串类型处理
                    if($v[1]  == 'datetime') {
                        // 格式化时间戳
                        $value = $value > 0 ? date('Y-m-d H:i:s', $value) : '';
                    }else if(!empty($v[1])) {
                        // 设置字段默认值
                        $value = $v[1];
                    }
                }
            }
            // 处理数字变为科学计数法的问题
            $value .= "\t";
            $list[$k] = iconv("UTF-8","GBK", (string)$value);
        }
        fputcsv($fp, $list);
    }
    exit();
}

/**
 * 获得当前完整URL
 * @return string
 */
function url_current()
{
    $redirect_uri = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    return ($redirect_uri);
}

/**
 * 获取redis对象实例
 * @return Redis
 * @date 2020/6/4 14:04
 */
function redis_instance()
{
    $redis      = new Redis();
    $httpHost   = $_SERVER['HTTP_HOST'];//获取域名
    if ($httpHost == 'game.vrupup.com') {
        $redis->connect('r-bp18vmtk1409q08i00.redis.rds.aliyuncs.com', 6379);
        $redis->auth('1yVXE0uOItLIBCE');
        $redis->select(6);//选择数据库6
    } else {
        $redis->connect('127.0.0.1', 6379); //本地连接Redis
        $redis->auth('123456');
        $redis->select(6);//选择数据库6
    }
    return $redis;
}

/**
 * 第三方视频返回参数提示
 * @param result
 * @return string
 */
function video_code($result = 0)
{
    switch ($result) {
        case 0:
            return '成功';
        break;
        case -1:
            return '其他错误';
        break;
        case 9:
            return '没有这个用户';
        break;
        case 10:
            return '用户已经在线';
        break;
        case 11:
            return '用户密码错误';
        break;
        case 50:
            return '设备不在线';
        break;
        case 57:
            return '请求被拒绝,设备未验证';
        break;
        case 73:
            return '转发服务器不在线';
        break;
        case 77:
            return '不在定时监看范围内';
        break;
        case 1019:
            return '超过并发数或者欠费';
        break;
    }
}

/**
 * 创建宠物编号、订单编号
 * @param type
 */
function create_number($type = 0):string
{
    if (!$type) {
        return '';
    }
    if ($type =='chicked') {
        return 'Ck'.time().random_int(10000, 99999);
    }
    if ($type == 'order') {
        $rand_number = 'Uo'.time().random_int(10000, 99999);
        $order = model('user_order')->where('id', $rand_number)->find();
        if (!$order) {
            return $rand_number;
        }
        create_number('order');
    }
}

/**
 * 创建证书编号
 */
function create_certificate_number()
{
    $order = db('user_certificate')->order('id', 'DESC')->limit(1)->find();
    if (!$order) {
        return 'YJYJ000001';
    }
    //补足位数
    return 'YJYJ'.sprintf("%06d", $order['id']+1);
}


/**

* 获取本周所有日期

*/

function get_week($time = '', $format='Y-m-d')
{
    $time = $time != '' ? $time : time();
    
    //获取当前周几
    
    $week = date('w', $time);
    
    $date = [];
    
    for ($i=1; $i<=7; $i++) {
        $date[$i] = date($format, strtotime('+' . $i-$week .' days', $time));
    }
    
    return $date;
}

/**
 * excel导入
 * @param  file 文件
 */
function import_excel($file)
{
    $excel = \PHPExcel_IOFactory::createReader('Excel5');
    $objPHPExcel = $excel->load($file['tmp_name'], $encode='utf-8');//$file 为解读的excel文件
    $sheet = $objPHPExcel->getSheet(0);
    $highestRow = $sheet->getHighestRow(); // 取得总行数
    $success_item = $fail_item = 0;
    $count =  0;
    $class_count = 0;
    $time = date('Y-m-d H:i:s');
    for ($i = 2; $i <= $highestRow; $i ++) {
        if ($objPHPExcel->getActiveSheet()->getCell('A'.$i)) {
            //题库类型不存在
            if (!$class = db('answer_class')->where('name', $objPHPExcel->getActiveSheet()->getCell('A'.$i))->field('id,name')->find()) {
                $class_count += $c_id = db('answer_class')->insertGetId([
                    'name' => $objPHPExcel->getActiveSheet()->getCell('A'.$i),
                    'create_time' => $time,
                ]);
                $class = db('answer_class')->where('name', $c_id)->field('id,name')->find();
            }
            //添加问题
            $count += db('answer')->insert([
                'class_id'      => $class['id'],
                'class_name'    => $class['name'],
                'is_checked'    => 1,//1选择题，2问答题
                'title'         => $objPHPExcel->getActiveSheet()->getCell('B'.$i),
                'one'           => $objPHPExcel->getActiveSheet()->getCell('C'.$i),
                'two'           => $objPHPExcel->getActiveSheet()->getCell('D'.$i),
                'three'         => $objPHPExcel->getActiveSheet()->getCell('E'.$i),
                'five'          => $objPHPExcel->getActiveSheet()->getCell('F'.$i),
                'six'           => $objPHPExcel->getActiveSheet()->getCell('G'.$i),
                'seven'         => $objPHPExcel->getActiveSheet()->getCell('H'.$i),
                'really'        => $objPHPExcel->getActiveSheet()->getCell('I'.$i),
                'integral'      => 2,
                'create_time'   => $time,
            ]);
        }
        unset($class);
    }
    return ['count' => $count,'class_count'=> $class_count ];
}

 /**
  * excel导出
  */
function exportOrderExcel2($title, $cellName, $data)
{
    //引入核心文件
    vendor("PHPExcel.PHPExcel");
    $objPHPExcel = new \PHPExcel();
    //定义配置
        $topNumber = 2;//表头有几行占用
        $xlsTitle = iconv('utf-8', 'gb2312', $title);//文件名称
        $fileName = $title.date('_YmdHis');//文件名称
        $cellKey = array(
                'A','B','C','D','E','F','G','H','I','J','K','L','M',
                'N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
                'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM',
                'AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ'
        );
         
    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);//所有单元格（列）默认宽度
         
    //垂直居中
    $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
 
    //处理表头标题
    $objPHPExcel->getActiveSheet()->mergeCells('A1:'.$cellKey[count($cellName)-1].'1');//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $title);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(18);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
         
    //处理表头
    foreach ($cellName as $k=>$v) {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellKey[$k].$topNumber, $v);//设置表头数据
    }
         
    //处理数据
    $start = $topNumber+1;
    $j = $topNumber+1;
    //小鸡订单
    if (strpos($title, '小鸡订单') !== false) {
        foreach ($data as $k=>$v) {
            $objPHPExcel->getActiveSheet()->setCellValue("A".$start, $v['id']);
            $objPHPExcel->getActiveSheet()->setCellValue("B".$start, $v['name']);
            $objPHPExcel->getActiveSheet()->setCellValue("C".$start, $v['nickname']);
            $objPHPExcel->getActiveSheet()->setCellValue("D".$start, $v['openid']);
            $objPHPExcel->getActiveSheet()->setCellValue("E".$start, $v['order_sn']);
            $objPHPExcel->getActiveSheet()->setCellValue("F".$start, $v['number']);
            $objPHPExcel->getActiveSheet()->setCellValue("G".$start, $v['money']);
            $objPHPExcel->getActiveSheet()->setCellValue("H".$start, date('Y-m-d H:i:s', $v['pay_time']));
            $objPHPExcel->getActiveSheet()->setCellValue("I".$start, $v['province'].$v['city'].$v['area'].$v['address']);
            $start ++ ;
        }
    }
    //导出execl
    ob_end_clean();//防止乱码
    header('pragma:public');
    header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
    header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;
}


function request_applet_callback($unionid ='')
{
    try {
        if (!$unionid) {
            return '';
        }
        $url ='https://yyg.yijiaegou.com/wxapp.php?controller=MemberShare.getMemberShare';
        $unionid = $unionid;
        $source =  'minigames';
        $arr['data'] = [
            'unionid' => $unionid,
            'source'  => $source,
        ];
        //排序
        ksort($arr['data']);
        $sign = http_build_query($arr['data']);
        $myfile = fopen(ROOT_PATH."pri.key", "r") or die("无效密钥");
        $rsa_key_path = fread($myfile, filesize(ROOT_PATH."pri.key"));
        $private_key = openssl_pkey_get_private($rsa_key_path);
        if (!$private_key) {
            show(0, '无效密钥');
        }
        openssl_sign($sign, $encrypted, $private_key, OPENSSL_ALGO_SHA256);
        $encrypted = base64_encode($encrypted);
        $arr['data']['sign'] = $encrypted;
        $arr['data'] =  json_encode($arr['data']);
        $res = Curl('POST', $url, $arr);
        $data = json_decode($res, true)['data'];

        db('request_applet_log')->insert([
            'unionid' => $unionid,
            'source' => $source,
            'sign' => $encrypted,
            'callback' => $res,
            'create_time' => date('Y-m-d H:i:s')
         ]);
    } catch (\Exception $e) {
        db('error')->insert([
                'error'       => 3,
                'desc'        => '第三方接口请求数据:',
                'text'        => $e->getMessage().'错误发送在:'.$e->getLine(),
                'create_time' => time()
        ]);
    }
    return $data;
}
