<?php
function getcachedir(){
    $dir = './cache';
    if (!is_dir($dir)) mkdir($dir,0777,true) or die('创建缓存目录失败！');
    return $dir;
}
function geticon($url,$type=0){
    if(empty($url))return '';
    if(isUrl($url) != '1'){return default_ico($type);}$http = '';
    if(substr($url, 0, 5) == 'https'){$http = 'https://'; }   //如果是https头，传到后面取图标时加上。防止出现302重定向    
    $arr = url_parse($url);$domain = $arr['host']; //分解目标域名
    $fav =$arr['fav']; //图标保存的路径和名称
    //调用缓存文件
    if (file_exists($fav)){ //有缓存就直接输出缓存    
        $file = file_get_contents($fav);
        if($file) return $type?base64EncodeImage($file):$file;
    }    
    $check1=getFav($http.$domain."/favicon.ico");if($check1) {cachefile($url,$check1); return $type?base64EncodeImage($check1):$check1;}//第一次尝试 根目录    
    $curl = get_url_content($url);//尝试读取内容并匹配ico文件
    $file = $curl['exec'];preg_match('|href\s*=\s*[\"\']([^<>]*?)\.ico[\"\'\?]|i',$file,$a);    //正则匹配
    if(!empty($a[1])){
        $file2=$a[1] .='.ico';
        if(!substr($file2,0,4)=='http'){//相对路径
            if(substr($file2, 0, 1) == '/'){$file2 = substr($file2, 1);}
            if(substr($file2, 0, 3) == '../'){$file2 = substr($a[1], 3);}
            if(substr($a[1], 0, 2) == './'){$file2 = substr($file2, 2);}
            $file2 = $http.$domain.'/'.$file2;//手动加上链接再试一次
        }
        $check2=getFav($file2);if($check2) {cachefile($url,$check2);return $type?base64EncodeImage($check2):$check2;}//第二次尝试
    }return '';
}
function url_parse($url){
    $arr = parse_url($url);$dir = getcachedir();
    $arr['fav']=$dir."/".$arr['host'].".ico";
    return $arr;
}
function cachefile($url,$content){
    if(empty($url) || empty($content))return false;
    $arr = url_parse($url);
    file_put_contents($arr['fav'],$content);
}
function threeapi($url,$type=0){
    $urls=array(
        'http://api.byi.pw/favicon/?url='.$url,
        'http://cdn.website.h.qhimg.com/index.php?domain='.$url,
    );
    if(!empty($urls[$type])){
        $check1=getFav($urls[$type]);
        if($check1) {cachefile($url,$check1);return $check1;}else return threeapi($urls[($type+1)]);
    }return false;
}
/**
 * 获取favion图标
 */
function getFav($url){
    $curl = get_url_content($url);
    $file = $curl['exec'];  //获取到的文件
    $zt = $curl['getinfo']; //状态    
    if($file && $zt['http_code'] == '200'){return $file;}    //有文件，并且返回状态为200
    return false;
}
function default_ico($type=0){
    $file = "null.ico";
    $data = file_get_contents($file);
    return $type?base64EncodeImage($data):$data;
}
/**
 * 获取GET或POST过来的参数
 * @param $key 键值
 * @param $default 默认值
 * @return 获取到的内容（没有则为默认值）
 */
function getParam($key,$default=''){
    return trim($key && is_string($key) ? (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $default)) : $default);
}
//将图片转化成base编码
function base64EncodeImage ($image_data,$mime='image/png') {
    $base64_image = 'data:' . $mime . ';base64,' . chunk_split(base64_encode($image_data));
    return $base64_image;
}
/**
 * curl获取数据
 * @param $bbb 目标url地址
 * @return ['exec'] 获取的内容
 * @return ['getinfo'] 返回的状态码
 */
function get_url_content($bbb) { 
   $ch = curl_init(); 
   $timeout = 5000;  //超时时间
   curl_setopt ($ch, CURLOPT_URL, $bbb); 
   curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);  
   curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT_MS, $timeout); 
   curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
   curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
   curl_setopt ($ch, CURLOPT_ENCODING, 'gzip'); //取消gzip压缩(代表：网易邮箱)
   $file_info['exec']= curl_exec($ch); 
   $file_info['getinfo'] = curl_getinfo($ch); //判断状态 有的情况下无法正确判断ico是否存在
   curl_close($ch); 
   return $file_info; 
}
/**
 * 判断字符串是否为域名
 * @param $s 目标url地址
 * @return 
 */
function isUrl($s) {  
    return preg_match('/^http[s]?:\/\/'.  
        '(([0-9]{1,3}\.){3}[0-9]{1,3}'. // IP形式的URL- 199.194.52.184  
        '|'. // 允许IP和DOMAIN（域名）  
        '([0-9a-z_!~*\'()-]+\.)*'. // 三级域验证- www.  
        '([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.'. // 二级域验证  
        '[a-z]{2,6})'.  // 顶级域验证.com or .museum  
        '(:[0-9]{1,4})?'.  // 端口- :80  
        '((\/\?)|'.  // 如果含有文件对文件部分进行校验  
        '(\/[0-9a-zA-Z_!~\*\'\(\)\.;\?:@&=\+\$,%#-\/]*)?)$/',  
        $s) == 1;  
}
?>