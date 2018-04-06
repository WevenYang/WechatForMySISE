<?php
include 'simple_html_dom.php';
include "MySiseUtil.php";

//echo getPersonMsg("1540911124", "15440515101257");
//获取个人资料
function getPersonMsg($name, $psd){

    $mainUrl = "http://class.sise.com.cn:7001/sise/login.jsp";
    $fh= file_get_contents($mainUrl);
    $h = new simple_html_dom();
    $h -> load($fh);
    $items = $h -> find("input[value]");
    $random1 = $items[0] -> name;
    $random2 =  $items[0] -> value;

    $post_data = array(
        "username" => $name,
        "password" => $psd,
        $random1 => $random2
    );

//设置cookie保存路径
    $cookie = dirname(__FILE__) . '/cookie_oschina.txt';
//    $click_url = dirname(__FILE__) . '/click_urls.txt';
//登录地址
    $url = "http://class.sise.com.cn:7001/sise/login_check.jsp";
//首页
    $url3 = "http://class.sise.com.cn:7001/sise/module/student_states/student_select_class/main.jsp";
    //初始化MySiseUtil
    $urls = new MySiseUtil();
//模拟登录
    $urls->login_post($url, $cookie, $post_data);
//获取登录页的信息
    $content = $urls->get_content($url3, $cookie);
//成功爬取各版块点击链接
    $html = new simple_html_dom();
    $html->load($content);
    $a = $html->find("td[onclick]");
//个人信息版块
    $person_msg_url = "http://class.sise.com.cn:7001/".substr($a[0]->onclick, 54, -1);
//获取登录页的信息
    $content = $urls->get_content($person_msg_url, "cookie_oschina.txt");
//成功爬取个人信息数据
    $html = new simple_html_dom();
    $html->load($content);
    $a = $html->find("div align='left'");
    $string =  "姓名:".yang_gbk2utf8(trim($a[3]->text()))."\n"."年级:".yang_gbk2utf8(trim($a[4]->text())).
        "\n"."专业:".yang_gbk2utf8(trim($a[5]->text()))."\n"."身份证号:".trim($a[6]->text())."\n"."电子邮箱:".trim($a[7]->text())."\n"."学习导师:".yang_gbk2utf8(trim($a[8]->text()))."\n"."辅导员:".yang_gbk2utf8(trim($a[9]->text()));
    return $string;
}

//个人考勤信息
function checked($name, $psd){

    $mainUrl = "http://class.sise.com.cn:7001/sise/login.jsp";
    $fh= file_get_contents($mainUrl);
    $h = new simple_html_dom();
    $h -> load($fh);
    $items = $h -> find("input[value]");
    $random1 = $items[0] -> name;
    $random2 =  $items[0] -> value;

    $post_data = array(
        "username" => $name,
        "password" => $psd,
        $random1 => $random2
    );

//设置cookie保存路径
    $cookie = dirname(__FILE__) . '/cookie_oschina.txt';
//    $click_url = dirname(__FILE__) . '/click_urls.txt';
//登录地址
    $url = "http://class.sise.com.cn:7001/sise/login_check.jsp";
//首页
    $url3 = "http://class.sise.com.cn:7001/sise/module/student_states/student_select_class/main.jsp";
    //初始化MySiseUtil
    $urls = new MySiseUtil();
//模拟登录
    $urls->login_post($url, $cookie, $post_data);
//获取登录页的信息
    $content = $urls->get_content($url3, $cookie);
//成功爬取各版块点击链接
    $html = new simple_html_dom();
    $html->load($content);
    $a = $html->find("td[onclick]");
//个人信息版块
    $check_url = "http://class.sise.com.cn:7001/".substr($a[3]->onclick, 49, -1);
//获取登录页的信息
    $content = $urls->get_content($check_url, "cookie_oschina.txt");
//成功爬取个人信息数据
    $str = "";
    $html = new simple_html_dom();
    $html->load($content);
    $a = $html->find("td[align=center]");

    for($i = 1;$i < count($a);$i++){
        $str = $str."课程代码:".yang_gbk2utf8(trim($a[$i]->text()))."\n";
        $str = $str."课程名称:".yang_gbk2utf8(trim($a[++$i]->text()))."\n";
        if (yang_gbk2utf8(trim($a[++$i]->text())) == ""){
            $str = $str."考勤情况:未知"."\n"."\n";
        }else{
            $str = $str."考勤情况:".yang_gbk2utf8(trim($a[++$i]->text()))."\n"."\n";
        }

    }
    return $str;
}

function getExamTime($name, $psd){

    $mainUrl = "http://class.sise.com.cn:7001/sise/login.jsp";
    $fh= file_get_contents($mainUrl);
    $h = new simple_html_dom();
    $h -> load($fh);
    $items = $h -> find("input[value]");
    $random1 = $items[0] -> name;
    $random2 =  $items[0] -> value;

    $post_data = array(
        "username" => $name,
        "password" => $psd,
        $random1 => $random2
    );

    //设置cookie保存路径
    $cookie = dirname(__FILE__) . '/cookie_oschina.txt';
    //    $click_url = dirname(__FILE__) . '/click_urls.txt';
    //登录地址
    $url = "http://class.sise.com.cn:7001/sise/login_check.jsp";
    //首页
    $url3 = "http://class.sise.com.cn:7001/sise/module/student_states/student_select_class/main.jsp";
    //初始化MySiseUtil
    $urls = new MySiseUtil();
    //模拟登录
    $urls->login_post($url, $cookie, $post_data);
    //获取登录页的信息
    $content = $urls->get_content($url3, $cookie);
    //成功爬取各版块点击链接
    $html = new simple_html_dom();
    $html->load($content);
    $a = $html->find("td[onclick]");
    $url2 = "http://class.sise.com.cn:7001/".substr($a[2]->onclick, 49, -1);
    //获取登录页的信息
    $content = $urls->get_content($url2, "cookie_oschina.txt");
    //成功爬取个人课程
    $str = "";
    $html = new simple_html_dom();
    $html->load($content);
    $a = $html->find("tr.odd");
    if($a == null){
        $str = "您近期没有任何考试安排";
    }else{
        foreach($a as $item)
            $str = $str.$item->text()."\n";
    }
    return $str;

}

function getCourseGoal($name, $psd){
    $mainUrl = "http://class.sise.com.cn:7001/sise/login.jsp";
    $fh= file_get_contents($mainUrl);
    $h = new simple_html_dom();
    $h -> load($fh);
    $items = $h -> find("input[value]");
    $random1 = $items[0] -> name;
    $random2 =  $items[0] -> value;

    $post_data = array(
        "username" => $name,
        "password" => $psd,
        $random1 => $random2
    );

    //设置cookie保存路径
    $cookie = dirname(__FILE__) . '/cookie_oschina.txt';
    //    $click_url = dirname(__FILE__) . '/click_urls.txt';
    //登录地址
    $url = "http://class.sise.com.cn:7001/sise/login_check.jsp";
    //首页
    $url3 = "http://class.sise.com.cn:7001/sise/module/student_states/student_select_class/main.jsp";
    //初始化MySiseUtil
    $urls = new MySiseUtil();
    //模拟登录
    $urls->login_post($url, $cookie, $post_data);
    //获取登录页的信息
    $content = $urls->get_content($url3, $cookie);
    //成功爬取各版块点击链接
    $html = new simple_html_dom();
    $html->load($content);
    $a = $html->find("td[onclick]");
    $url2 = "http://class.sise.com.cn:7001/".substr($a[0]->onclick, 54, -1);
    //获取登录页的信息
    $content = $urls->get_content($url2, "cookie_oschina.txt");
    //成功爬取个人课程
    $str = "";
    $html = new simple_html_dom();
    $html->load($content);
    $a = $html->find("tr.odd, tr.even");
//    if($a == null){
//        $str = "您近期没有任何考试安排";
//    }else{
    foreach($a as $item)
    {
        if (preg_match("/[\x7f-\xff]/", $item -> find("td", 2) -> text())){
            $i = $item -> find("td", 2);
            $goal = $item -> find("td", 8);
            $str = $str.($i->text()."\t\t".$goal->text())."\n";
        }else{
            $i = $item -> find("td", 1);
            $goal = $item -> find("td", 7);
            $str = $str.($i->text()."\t\t".$goal->text())."\n";
        }

    }
//    }
    return yang_gbk2utf8($str);
}

/**
 *自动判断把gbk或gb2312编码的字符串转为utf8
 *能自动判断输入字符串的编码类，如果本身是utf-8就不用转换，否则就转换为utf-8的字符串
 *支持的字符编码类型是：utf-8,gbk,gb2312
 *@$str:string 字符串
 */
function yang_gbk2utf8($str){
    $charset = mb_detect_encoding($str,array('UTF-8','GBK','GB2312'));
    $charset = strtolower($charset);
    if('cp936' == $charset){
        $charset='GBK';
    }
    if("utf-8" != $charset){
        $str = iconv($charset,"UTF-8//IGNORE",$str);
    }
    return $str;
}
