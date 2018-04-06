<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/25
 * Time: 13:42
 */
include 'WxPayConf_pub.php';

$link = new mysqli(WxPayConf_pub::MYSQL_SERVER,WxPayConf_pub::MYSQL_USERNAME,WxPayConf_pub::MYSQL_PASSWORD, WxPayConf_pub::MYSQL_DATABASE);
//$link = new mysqli("127.0.0.1", "root", "", "test");
$sql = "select * from User";
$rs = $link -> query($sql);
while( $row = $rs -> fetch_row()){
    $name = $row[2];
    $psd = $row[3];
}
$a = array();
$a[] = $name;
$a[] = $psd;
echo $name;
echo "finish";
$link -> close();