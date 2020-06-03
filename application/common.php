<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use \think\Exception;

// 应用公共文件
/**
 * @todo 将二维数组变为一维键值对
 * @author lhw 2019-10-16
 * @param array $array 二维数据
 * @param string $column_key 键值
 * @param string $index_key 键名
 * @return array
 */
function arrayColumn($array, $column_key, $index_key = null)
{
    if (function_exists('array_column')) {
        $arr = array_column($array, $column_key, $index_key);
    } else {
        $arr = [];
        foreach ($array as $v) {
            if ($index_key) {
                $arr[$v[$index_key]] = $v[$column_key];
            } else {
                $arr[] = $v[$column_key];
            }
        }
    }
    return $arr;
}

/**
 * @todo 将二维数组key替换
 * @author lhw 2019-10-16
 * @param array $array 二维数据
 * @param string $index_key 键名
 * @return array
 */
function arrayKeyReplace($array, $index_key)
{
    $arr = [];
    foreach ($array as $v) {
        $arr[$v[$index_key]] = $v;
    }
    
    return $arr;
}

/**
 * @todo 输出json，并结束执行
 * @author lhw 2019-10-16
 * @param mixed $result 返回给客户端的数据
 * @param int $code 代码 0 成功 1 失败 2 未登录，注：更多自行定义 
 * @param string $message 错误时的提示
 * @return void
 */
function showJson($result = null, $code = 0, $message = '')
{
    header('Content-Type:application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    $r = ['code' => $code, 'message' => $message, 'response' => $result];

    exit(json_encode($r, 256));
}

/**
 * @todo 获取limit数据
 * @author lhw 2019-10-16
 * @param array $args 搜索参数
 * @return string
 */
function getLimit(array $args)
{
    $offset = isset($args['page']) && $args['page'] > 0 ? (int)$args['page'] : 1;
    $length = isset($args['limit']) && $args['limit'] > 0 ? (int)$args['limit'] : 10;

    if ($length > 500) {
        $length = 10;
    }

    $offset = ($offset - 1) * $length;
    
    return $offset.','.$length;
}

/**
 * @todo 获取随机字符串
 * @author lhw 2019-10-16
 * @param number $length
 * @param int $type 随机字符串类型 0大小字母+数字 1数字 2小写字母 3大写字母 4数字+小写字母 5数字+大写字母 6大小写字母
 * @return string|mixed
 */
function getRandomStr($length = 5, $type = 0)
{
    switch ($type) {
        case 1:
            $arr = range(0, 9);
            break;
        case 2:
            $arr = range('a', 'z');
            break;
        case 3:
            $arr = range('A', 'Z');
            break;
        case 4:
            $arr = array_merge(range(0, 9), range('a', 'z'));
            break;
        case 5:
            $arr = array_merge(range(0, 9), range('A', 'Z'));
            break;
        case 6:
            $arr = array_merge(range('a', 'z'), range('A', 'Z'));
            break;
        default:
            $arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
            break;
    }
    $len = count($arr) - 1;
    $str = '';
    for ($i = 0; $i < $length; $i ++) {
        $str .= $arr[mt_rand(0, $len)];
    }
    
    return $str;
}

/**
 * @todo 递归过滤自定义函数，默认删除空格
 * @author lhw
 * @param string|array $data 过滤的字符串或数组
 * @param string $filter_list 过滤函数名，多个以“｜”符号分隔，格式：trim或trim|addslashes
 * @return mixed
 */
function filter($data, $filter_list = 'trim')
{
    $filter_arr = explode('|', $filter_list);
    
    if (is_string($data)) {
        foreach ($filter_arr as $filter) {
            $result = call_user_func($filter, $data);
            $data   = $result;
        }
    } else if (is_array($data)) {
        $result = array();
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $result[$k] = filter($v, $filter_list);
            } else {
                foreach ($filter_arr as $filter) {
                    $result[$k] = call_user_func($filter, $v);
                    $v = $result[$k];
                }
            }
        }
        $data   = $result;
    }
    
    return $data;
}

/**
 * @todo 读取Excel文件，支持xls（03版excel格式）与xlsx（07版excel格式），自动根据上传文件后缀判断，目前只支持 A-Z
 * @param string $field_name    上传文件字段名称
 * @param array $fields         读取字段名称数组，如果指定有数字key值，则以key值读取列，默认从0开始，如：['area_id','area_name'] 或[3=>'area_id',5=>'area_name']
 * @param number $start_row     起始行
 * @param number $end_row       结束行，如果大于能读取的行业，以能读取的为准，否只读取指定行数
 * @return array;
 */
function readExcel($field_name, $fields, $start_row = 2, $end_row = 1000)
{
    if (!isset($_FILES[$field_name]['error']) || $_FILES[$field_name]['error'] !== 0) {
        throw new Exception('上传文件不存在');
    }
    $ext = substr($_FILES[$field_name]['name'], strrpos($_FILES[$field_name]['name'], '.'));
    if (!in_array($ext, array('.xls','.xlsx'))) {
        throw new Exception('上传文件格式不正确，只能为：.xls或.xlsx');
    }

    vendor('phpexcel.PHPExcel');
    if ($ext == '.xls') {
        $objReader = \PHPExcel_IOFactory::createReader('Excel5');
    } else {
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
    }
    $objPHPExcel = $objReader->load($_FILES[$field_name]['tmp_name']);
    $sheet = $objPHPExcel->getSheet(0);
    $rows = $sheet->getHighestRow();        // 取得总行数
    if ($rows > $end_row) {
        $rows = $end_row;                   // 赋值给定行数
    }
    //$cols = $sheet->getHighestColumn();     // 取得总列数
    $arr = array();
    $cols_range = range('A', 'Z');
    for ($i = $start_row; $i <= $rows; $i ++) {
        foreach ($fields as $k => $v) {
            $arr[$i][$v] = trim($sheet->getCell($cols_range[$k].$i)->getValue());
        }
    }
    
    return $arr;
}

/**
 * 保存base64提交的图片文件
 * @author lhw
 * @param string $base64_image    图片的编码字符串
 * @param bool $domain          是返回带域名url地址
 * @param array $config         默认配制，dir：保存的目录名称，相对路径或绝对路径；url：相对于域名访问的路径地址；ext：图片扩展名称；size：图片文件大小
 * @throws Exception
 * @return void|array           返回保存后带目录的文件名称和访问url地址
 */
function saveBase64Image($base64_image, $domain = false, $config = [])
{
    //默认配制
    $conf = ['dir'=>'./upload/image/','url'=>'/upload/image/','ext'=>['png','jpg','jpeg','gif'],'size'=>2000000];
    if (!empty($config)) {
        //合并配制
        $conf = array_merge($conf, $config);
    }
    //$base64_image为图片的编码字符串
    if (empty($base64_image)) {
        return ;
    }

    preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result);
    try{
        if (count($result) != 3) {
            throw new \think\Exception('图片数据不正确。');
        }
        $file_ext = strtolower($result[2]);
        if (!in_array($file_ext, $conf['ext'])) {
            throw new \think\Exception('上传图片文件扩展不支持。');
        }
        if (strlen($base64_image) > $conf['size']) {
            throw new \think\Exception('上传图片文件太大了。');
        }

        $date           = date('Ymd');
        $pathname       = $conf['dir'].'/'.$date;

        if (!is_dir($pathname) && !mkdir($pathname, 0777, true)) {
            throw new \think\Exception('保存的图片目录没有权限。');
        }
        $name       = md5($base64_image).'.'.$file_ext;      //图片文件名加上图片扩展
        $savepath   = $pathname.'/'.$name;                  //图片保存目录
        //对图片进行解析并保存
        if (!file_put_contents($savepath, base64_decode(str_replace($result[1], '', $base64_image)))) {
            throw new \think\Exception('保存图片文件失败。');
        }

        $server_name = '';
        if ($domain) {
            $server_name = '//'.$_SERVER['SERVER_NAME'];
            if (is_ssl() || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'https://') === 0)) {
                $server_name = 'https:'.$server_name;
            }
        }
        $saveurl = $server_name.$conf['url'].'/'.$date.'/'.$name;
        return ['dir'=>$savepath, 'url'=>$saveurl];
    }catch (Exception $e){
        showJson('',1,$e->getMessage());
    }
}

/**
 * 上传文件
 * @author sxt
 * @param string $field_name 上传的参数名
 * @param string $save_dir 保存目录
 * @param array $config 配置 大小 和 扩展名
 * @return array|string
 */
function upload($field_name='image',$save_dir='/upload/image',$config=['size'=>1048576,'ext'=>'jpg,png,gif']){
    // 获取表单上传文件 例如上传了001.jpg
    $file = request()->file($field_name);
    if(empty($file)){
        return '未选择上传文件！';
    }
    // 移动到框架应用根目录/public/uploads/ 目录下
    $info = $file->validate($config)->move(ROOT_PATH . 'public' .$save_dir);
    if($info){
        // 成功上传后 获取上传信息
        return ['save_path'=>$save_dir.'/'.str_replace('\\','/',$info->getSaveName()),'path'=>$info->getPath(),'ext'=>$info->getExtension(),'filename'=>$info->getFilename()];
    }else{
        // 上传失败获取错误信息
        return $file->getError();
    }
}


/**
 *导出Excel
 * @param $list 需导出数据源
 * @param $colums 标题
 * 格式 ['index'=>'序号','name'=>'原料名称'] 第一种
 * 格式 ['index'=>['name'=>'序号','width'=>10],'name'=>['name'=>'原料名称','width'=>25]] width 不传 为自己适应 第二种
 * @param $name 文件名称
 * @param $title 大标题
 * @param $sheet_size sheet条数
 * @param $colums
 * zxj 2019-10-31
 * **/
function exportExcel($list,$colums=[],$name="",$title="",$sheet_size=5000){
    $xls_title=$title;
    $file_name=$name.'_'.date('YmdHis');
    vendor('phpexcel.PHPExcel');
    $excel = new \PHPExcel();
    //横向单元格标识
    $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q',
        'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI',
        'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
    //设置大标题
    $colums_cout = count($colums);
    $count = count($list);
    $sheets = ceil($count/$sheet_size);
    for($sheet = 0;$sheet<$sheets;$sheet++){
        if($sheet>0){
            $excel->createSheet();
        }
        $excel->setActiveSheetIndex($sheet)->getStyle ( 'A:'.$cellName[$colums_cout-1] )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );  // 设置单元格水平对齐格式
        $excel->setActiveSheetIndex($sheet)->getStyle ( 'A:'.$cellName[$colums_cout-1] )->getAlignment ()->setVertical ( \PHPExcel_Style_Alignment::VERTICAL_CENTER );        // 设置单元格垂直对齐格式
        if($title != ''){
            $excel->getActiveSheet($sheet)->setCellValue('A1', $xls_title)->mergeCells('A1:'.$cellName[$colums_cout-1].'1')->getStyle()->getFont()->setSize(16);
            $excel->getActiveSheet($sheet)->getRowDimension('1')->setRowHeight(30);
        }

        $index = 0;
        foreach ($colums as $k => &$v){
            if(is_array($v)){
                if(isset($v['width']))
                    $excel->getActiveSheet($sheet)->getColumnDimension($cellName[$index])->setWidth($v['width']);
                else
                    $excel->getActiveSheet($sheet)->getColumnDimension($cellName[$index])->setAutoSize(true);
                //设置表头
                $excel->getActiveSheet($sheet)->setCellValue($cellName[$index].'2', $v['name']);
            }else{
                $excel->getActiveSheet($sheet)->setCellValue($cellName[$index].'2', $v);
            }
            $index++;
        }
        //表头加粗
        $excel->getActiveSheet($sheet)->getStyle('A1:'.$cellName[$colums_cout-1].'2')->getFont()->setBold(true);
        //加边框样式
        $styleThinBlackBorderOutline = ['borders' => ['allborders' =>['style' => \PHPExcel_Style_Border::BORDER_THIN ]]];
        unset($i,$index);
        $index = 1;
        for($t=$sheet*$sheet_size;$t<count($list);$t++){
            $i = $index + 2;
            $y = 0;
            if($t == ($sheet+1)*$sheet_size){
                break;
            }
            foreach ($colums as $ck => &$cv) {
                $excel->getActiveSheet($sheet)->setCellValue( $cellName[$y]. $i, $list[$t][$ck] . '');
//                $s = strlen($list[$t][$ck]);
//                if(intval($cv['width']) < intval($s))
//                    $excel->getActiveSheet($sheet)->getColumnDimension($cellName[$y])->setWidth($s);
                $y++;
            }
            $index++;
        }
        $excel->getActiveSheet($sheet)->getStyle( 'A2:'.$cellName[$colums_cout-1].($index+1))->applyFromArray($styleThinBlackBorderOutline);
        $excel->getActiveSheet($sheet)->getStyle('A2:'.$cellName[$colums_cout-1].($index+1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    }
    header('Content-type:application/vnd.ms-excel;charset=utf-8');
    header("Content-Disposition:attachment;filename=$file_name.xls");//attachment新窗口打印inline本窗口打印
    header('Cache-Control: max-age=0');
    $obj_writer=\PHPExcel_IOFactory::createWriter($excel,'Excel5');
    $obj_writer->save('php://output');
    exit;
}

/**
 * @todo 生成或直接输出二维码图片，图片大小不固定的
 * @author lhw 2019-11-13
 * @param string $text  二维码内容
 * @param false|string $outfile  是否保存图片，默认false，直接输出；保存图片需要传入绝对文件路径，目录必须存在，格式：Windows D:\qrcode\qrcode.png 或 linux /web/public/qrcode.png
 * @param number $level 容错级别，默认 0 范围值：0，1，2，3
 * @param number $size  二维码尺寸，默认6
 * @param number $margin 二维码补白，默认0无
 * @param boolean $saveandprint 是否保存图片并输出，默认false
 * @param array $merge_image    合并图片使用
 * @return null
 */
function qrcode($text, $outfile = false, $level = 0, $size = 6, $margin = 0, $saveandprint = false, $merge_image = [])
{
    vendor('qrcode.qrcode');
    
    \QRcode::png($text, $outfile, $level, $size, $margin, $saveandprint, $merge_image);
}

/**
 * @todo 获取时间区间的月份表
 * @param string|array $table 格式： pay_order | ['cash_order','cash_order_product']
 * @param string $start_time 格式：2019-12-27 或 2019-12-27 12:35:58
 * @param string $end_time
 * @return array
 */
function getTableMonth($table, $start_time, $end_time)
{
    $db_prefix = config('database.prefix');
    //转时间戳
    $start_time = strtotime($start_time);
    $end_time = strtotime($end_time);
    
    $start_year = date('Y', $start_time);   //开始的年
    $end_year = date('Y', $end_time);       //结束的年
    $start_m = date('n', $start_time);      //开始的月
    $end_m = date('n', $end_time);          //结束的月
    $end_ym = date('Y_n', $end_time);       //结束的年月
    
    $table_list = [];
    
    if ($start_year == $end_year) {
        for ($i = $start_m; $i <= $end_m; $i ++) {
            //年月表
            $ym = $start_year.'_'.str_pad($i, 2, '0', STR_PAD_LEFT);
            if (is_array($table)) {
                $temp_arr = [];
                foreach ($table as $v) {
                    if (!tableIsExist($db_prefix.$v.'_'.$ym)) {
                        break;
                    }
                    $temp_arr[$v] = $db_prefix.$v.'_'.$ym;
                }
                //表不存在跳过
                if (empty($temp_arr) || count($temp_arr) != count($table)) {
                    continue;
                }
                $table_list[] = $temp_arr;
            } else {
                if (!tableIsExist($db_prefix.$table.'_'.$ym)) {
                    continue;
                }
                
                $table_list[] = $db_prefix.$table.'_'.$ym;
            }
        }
    } else {
        for ($y = $start_year; $y <= $end_year; $y ++) {
            for ($i = ($y == $start_year ? $start_m : 1); $i <= 12; $i ++) {
                //年月
                $ym = $y.'_'.str_pad($i, 2, '0', STR_PAD_LEFT);
                
                if (is_array($table)) {
                    $temp_arr = [];
                    foreach ($table as $v) {
                        if (!tableIsExist($db_prefix.$v.'_'.$ym)) {
                            break;
                        }
                        $temp_arr[$v] = $db_prefix.$v.'_'.$ym;
                    }
                    //表不存在跳过
                    if (empty($temp_arr) || count($temp_arr) != count($table)) {
                        continue;
                    }
                    $table_list[] = $temp_arr;
                } else {
                    if (!tableIsExist($db_prefix.$table.'_'.$ym)) {
                        continue;
                    }
                    
                    $table_list[] = $db_prefix.$table.'_'.$ym;
                }
                
                if ($ym == $end_ym) {
                    break 2;
                }
            }
        }
    }
    
    return $table_list;
}

/**
 * 判断表是否存在
 * @param string $table_name    完整表名
 * @return boolean
 */
function tableIsExist($table_name)
{
    $str_sql="SHOW TABLES LIKE '%{$table_name}%'";
    $r = think\Db::query($str_sql);
    
    return empty($r) ? false : true;
}
/**
 * 经典的概率算法，
 * $proArr是一个预先设置的数组，
 * 假设数组为：array(100,200,300，400)，
 * 开始是从1,1000 这个概率范围内筛选第一个数是否在他的出现概率范围之内，
 * 如果不在，则将概率空间，也就是k的值减去刚刚的那个数字的概率空间，
 * 在本例当中就是减去100，也就是说第二个数是在1，900这个范围内筛选的。
 * 这样 筛选到最终，总会有一个数满足要求。
 * 就相当于去一个箱子里摸东西，
 * 第一个不是，第二个不是，第三个还不是，那最后一个一定是。
 * 这个算法简单，而且效率非常高，
 * 这个算法在大数据量的项目中效率非常棒。
 */
function get_rand($proArr) {
    $result = '';
    //概率数组的总概率精度
    $proSum = array_sum($proArr);
    //概率数组循环
    foreach ($proArr as $key => $proCur) {
        $randNum = mt_rand(1, $proSum);
        if ($randNum <= $proCur) {
            $result = $key;
            break;
        } else {
            $proSum -= $proCur;
        }
    }
    unset ($proArr);
    return $result;
}
/**
 * 根据两点经纬度获取距离
 */
function distance_calculate($longitude1, $latitude1, $longitude2, $latitude2) {
    $radLat1 = radian ( $latitude1 );
    $radLat2 = radian ( $latitude2 );
    $a = radian ( $latitude1 ) - radian ( $latitude2 );
    $b = radian ( $longitude1 ) - radian ( $longitude2 );

    $s = 2 * asin ( sqrt ( pow ( sin ( $a / 2 ), 2 ) + cos ( $radLat1 ) *
            cos ( $radLat2 ) * pow ( sin ( $b / 2 ), 2 ) ) );

    $s = $s * 6378.137; //乘上地球半径，单位为公里
    $s = round ( $s * 10000 ) / 10000; //单位为公里(km)
    return $s; //单位为km
}
function radian($d) {
    return $d * 3.1415926535898 / 180.0;
}
/**
 * 获取树形节点
 * @param $data array 数据
 * @param $parentValue integer 父级值
 * @param $pk string 主键值(父级值来源)
 * @param $parentKey string 父级值字段
 * @param $childrenKey string 子级容器
 * @return array
 */
function getTreeNode($data, $parentValue = 0, $pk = 'id', $parentKey = 'pid', $childrenKey = 'children') {
    if (!$data)  return [];
    $tree = [];
    foreach ($data as $key => $value) {
        if ($value[$parentKey] == $parentValue) {
            $value[$childrenKey] = getTreeNode($data, $value[$pk]);
            if (!$value[$childrenKey]) {
                unset($value[$childrenKey]);
            }
            $tree[] = $value;
        }
    }
    return $tree;
}