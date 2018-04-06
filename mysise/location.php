<?php
//缓存中保存用户地理位置
function setLocation($openid,$locationX,$locationY)
{
    $link = new mysqli(WxPayConf_pub::MYSQL_SERVER,WxPayConf_pub::MYSQL_USERNAME,WxPayConf_pub::MYSQL_PASSWORD, WxPayConf_pub::MYSQL_DATABASE);
    $sql = "insert into Location(openid, locationX, locationY) values('$openid', '$locationX', '$locationY')";
    $sqll = "select * from Location where openid = $openid";
    $result = $link -> query($sqll);
    if ($result){
        $sqlll = "update Location where openid = $openid set locationX = $locationX, locationY = $locationY";
        $rs = $link -> query($sqlll);
        if ($rs){
            return "已更新缓存。\n现在可发送“附近”加目标的命令，如“附近酒店”，“附近商家”。";
        }else {
            return "更新缓存失败。";
        }
    }else{
        $rs = $link -> query($sql);
        if($rs){
            return "您的位置已缓存。\n现在可发送“附近”加目标的命令，如“附近酒店”，“附近商家”。";
        }
        else{
            return "未启用缓存，请先开启服务器的缓存功能。";
        }
    }

}

//从缓存中读取用户地理位置信息
function getLocation($openid)
{
    $link = new mysqli(WxPayConf_pub::MYSQL_SERVER,WxPayConf_pub::MYSQL_USERNAME,WxPayConf_pub::MYSQL_PASSWORD, WxPayConf_pub::MYSQL_DATABASE);
    $sql = "select * from Location where openid = '$openid'";
    $rs = $link -> query($sql);
   if($rs){
    while( $row = $rs -> fetch_row()){
           $locationX = $row[2];
           $locationY = $row[3];
       }
        $array = array(
            "locationX" => $locationX,
            "locationY" => $locationY
        );
        return $array;
    }else{
         return "未启用缓存，请先开启服务器的缓存功能。";
       }
}

?>
