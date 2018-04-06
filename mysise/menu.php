<?php
include "WxPayConf_pub.php";
$appid = WxPayConf_pub::APPID;
$appsecret = WxPayConf_pub::APPSECRET;
$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";

$output = https_request($url);
$jsoninfo = json_decode($output, true);

$access_token = $jsoninfo["access_token"];


$jsonmenu = '{
      "button":[
      {
            "name":"MySise",
           "sub_button":[
            {
               "type":"click",
               "name":"绑定学号",
               "key":"band"
            },
            {
               "type":"click",
               "name":"解绑学号",
               "key":"unband"
            },
            {
               "type":"click",
               "name":"校园路线",
               "key":"route"
            },
             {
               "type":"click",
               "name":"成绩",
               "key":"goal"
            },
            
            {
               "type":"view",
               "name":"一卡通",
               "url":"http://ecard.scse.com.cn:8070/Home/Index"
            }]
      

       },	
       {
           "name":"查询",
           "sub_button":[
            {
               "type":"click",
               "name":"课程",
               "key":"curses"
            },
            {
               "type":"click",
               "name":"考勤",
               "key":"check"
            },
            {
               "type":"click",
               "name":"教务通知",
               "key":"time"
            },
            {
               "type":"click",
               "name":"个人信息",
               "key":"person_msg"
            },
            {
               "type":"click",
               "name":"周边信息",
               "key":"mapinfo"
            }]
       

       }]
 }';
//https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxc3615f57bddde28f&redirect_uri=http%3a%2f%2f1.wevenyang.applinzi.com%2fICCard%2fGetWxUserInfo.php&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect
$url1 = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
$result = https_request($url1, $jsonmenu);
var_dump($result);

function https_request($url,$data = null){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

?>