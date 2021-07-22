<?php
/**
 * php获取网站favicon图标
 */
include_once("fav_function.php");
$url = getParam('url'); //获取传过来的链接参数
$type = getParam('type'); //获取传过来的链接参数
if(empty($url)){die('?url=https://www.baidu.com and &type=1 get base64');}
if(substr($url, 0, 4) != 'http'){$url = 'http://'.$url;}
if(empty($type)){header('Content-type: image/x-icon');}//输出的是图标格式
$content=geticon($url,$type);
if(empty($content)){
    $content=threeapi($url);
    if($content && !empty($type))$content=base64EncodeImage($content);
}
if(empty($content)){echo default_ico($type);return;}
echo $content;return;
?>