<?php

class MySiseUtil
{
    //登陆
    public function login_post($url, $cookie, $post) {
        $curl = curl_init();//初始化curl模块
        curl_setopt($curl, CURLOPT_URL, $url);//登录提交的地址
        curl_setopt($curl, CURLOPT_HEADER, 0);//是否显示头信息
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0);//是否自动显示返回的信息
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie); //设置Cookie信息保存在指定的文件中
        curl_setopt($curl, CURLOPT_POST, 1);//post方式提交
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));//要提交的信息
        curl_exec($curl);//执行cURL
        curl_close($curl);//关闭cURL资源，并且释放系统资源
    }

    //登陆后获取内容
    public function get_content($url, $cookie) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie); //读取cookie
        $rs = curl_exec($ch); //执行cURL抓取页面内容
        curl_close($ch);
        return $rs;
    }

    //读取链接文件指定行
    function getLine($file, $line, $length = 4096){
        $returnTxt = null; // 初始化返回
        $i = 1; // 行数

        $handle = @fopen($file, "r");
        if ($handle) {
            while (!feof($handle)) {
                $buffer = fgets($handle, $length);
                if($line == $i) $returnTxt = $buffer;
                $i++;
            }
            fclose($handle);
        }
        return $returnTxt;
    }
}