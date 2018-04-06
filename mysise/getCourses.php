<?php

include "MySiseUtil.php";
include "simple_html_dom.php";
//初始化MySiseUtil
$util = new MySiseUtil();
//设置post数据
//$post_data = array(
//    "username" => "1340911114",
//    "password" => "12",
//    "5fce71ee67be50169b52fc22679bd659" => "ed6de40a793102484ded1fad72432f1d"
//);
//设置cookie保存路径
//$cookie = dirname(__FILE__) . '/cookie_oschina.txt';
//登录地址
//$url = "http://class.sise.com.cn:7001/sise/login_check.jsp";
//$url3 = "http://class.sise.com.cn:7001/sise/module/student_schedular/student_schedular.jsp";
//模拟登录
//$util->login_post($url, $cookie, $post_data);
//登录后要获取信息的地址
$url2 = $util -> getLine("click_urls.txt", 2);

//获取登录页的信息
$content = $util->get_content($url2, "cookie_oschina.txt");


//成功爬取个人信息数据
$html = new simple_html_dom();
$html->load($content);
$a = $html->find("td[class='font12']");
foreach($a as $item){
    if(trim($item->text()) == "&nbsp;"){
        echo "";
    }else{
        echo trim($item->text())."\n";
    }

}

