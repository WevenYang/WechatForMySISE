<?php

include "getPerson_msg.php";
include 'phpqrcode.php';
include 'WxPayConf_pub.php';

define("Token", "weven");
$wechatObj = new wechatCallbackapiTest();
if (!isset($_GET['echostr'])) {
    $wechatObj->responseMsg();
}else{
    $wechatObj->valid();
}

class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $nonce = $_GET["nonce"];
        $timestamp = $_GET["timestamp"];

        $token = Token;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    public function responseMsg(){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if(!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj -> MsgType);
            switch($RX_TYPE){
                case "text":
                    $resultStr = $this -> receiveText($postObj);
                    break;
                case "event":
                    $resultStr = $this -> receiveEvent($postObj);
                    break;
				case "location":
                    $result = $this->receiveLocation($postObj);
                    break;
                default:
                    $resultStr = "";
                    break;
            }
            echo $resultStr;
        }else{
            echo "";
            exit;
        }
    }

    private function receiveText($object){
		 $funcFlag=0;
         $arr = explode(" ", $object -> Content);
		 
		 $keyword=trim($object->Content);
         $category=substr($keyword,0,6);
		 $entity=trim(substr($keyword,6,strlen($keyword)));

		 if($category!="附近"){
             if(preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $object -> Content)>0){
             	$array = $this -> searchTeacher(trim($object -> Content));
                 if($array[0] == ""){
                 	$str = "没有找到你想要的？可添加微信号：huaruan321,私聊为您查询解答。";
                 }else{
                 	$str = "工号 ".$array[0]."电话".$array[1];
                 }
                 $resultStr = $this -> transmitText($object, $str, $funcFlag);
             }else {
                 $str = $this -> saveDatabase($arr[0], $arr[1], $object -> FromUserName);
                //$contentStr = "你发送的内容是：".$object -> FromUserName;
                $resultStr = $this -> transmitText($object, $str, $funcFlag);                 
             }           
		 }else{
			 switch($category){
             case "附近":
              include("location.php");
              $location=getLocation($object->FromUserName);
              if(is_array($location)){
                 include("mapbaidu.php");
                 $content=catchEntitiesFromLocation($entity,$location["locationX"],$location["locationY"],"5000");
              }else{  $content=$location;  }
               //  $content = "你发送的是文本，内容为：".$location["locationX"]."   ".$location["locationY"];
              break;
         default:
         	$content=$object->FromUserName;
         break;
         }
         if(is_array($content)){
             $resultStr=$this->transmitNews($object, $content);
         }else{
             $resultStr=$this->transmitText($object, $content);
              }   
		 }
        return $resultStr;
		
    }
	
    private function receiveLocation($object){
       include("location.php");
       $content=setLocation($object->FromUserName,(string)$object->Location_X,(string)$object->Location_Y);
       $result=$this->transmitText($object,$content);
       return $resultStr;
   }
   
    //将学号密码openId一起存入到数据库
    private function saveDatabase($a, $b, $c){
//        $link = mysql_connect(WxPayConf_pub::MYSQL_SERVER,WxPayConf_pub::MYSQL_USERNAME,WxPayConf_pub::MYSQL_PASSWORD);
//        mysql_select_db(WxPayConf_pub::MYSQL_DATABASE);
        $link = new mysqli(WxPayConf_pub::MYSQL_SERVER,WxPayConf_pub::MYSQL_USERNAME,WxPayConf_pub::MYSQL_PASSWORD, WxPayConf_pub::MYSQL_DATABASE);
        //实在不解，为何这里的字段值需要用单引号括着？
        $sql = "insert into User(username, password, open_id) values($a, '$b', '$c')";
        $rs = $link -> query($sql);
        if($rs){
            return '绑定成功';
        }else{
            return '绑定失败';
        }
        $link -> close();
    }

    //删除数据库中的学号姓名
    private function deleteDatabase($c){
//     $link = mysql_connect(WxPayConf_pub::MYSQL_SERVER,WxPayConf_pub::MYSQL_USERNAME,WxPayConf_pub::MYSQL_PASSWORD);
//     mysql_select_db(WxPayConf_pub::MYSQL_DATABASE);
        $link = new mysqli(WxPayConf_pub::MYSQL_SERVER,WxPayConf_pub::MYSQL_USERNAME,WxPayConf_pub::MYSQL_PASSWORD, WxPayConf_pub::MYSQL_DATABASE);
        $sql = "delete from User where open_id = '$c'";
        $rs = $link -> query($sql);
        if($rs){
            return true;
        }else{
            return false;
        }
        $link -> close();

    }


    //查询数据库有关open_id的信息
    private function searchByOpenidForNameAndId( $c ){
//        $link = mysql_connect(WxPayConf_pub::MYSQL_SERVER,WxPayConf_pub::MYSQL_USERNAME,WxPayConf_pub::MYSQL_PASSWORD);
//         mysql_select_db(WxPayConf_pub::MYSQL_DATABASE);
        $link = new mysqli(WxPayConf_pub::MYSQL_SERVER,WxPayConf_pub::MYSQL_USERNAME,WxPayConf_pub::MYSQL_PASSWORD, WxPayConf_pub::MYSQL_DATABASE);
        $sql = "select * from User where open_id = '$c'";
        $rs = $link -> query($sql);
        while( $row = $rs -> fetch_row()){
            $name = $row[2];
            $psd = $row[3];
        }
        $a = array();
        $a[] = $name;
        $a[] = $psd;
        return $a;
        $link -> close();
    }

	//查询数据库教师信息
    private function searchTeacher( $c ){
//        $link = mysql_connect(WxPayConf_pub::MYSQL_SERVER,WxPayConf_pub::MYSQL_USERNAME,WxPayConf_pub::MYSQL_PASSWORD);
//         mysql_select_db(WxPayConf_pub::MYSQL_DATABASE);
        $link = new mysqli(WxPayConf_pub::MYSQL_SERVER,WxPayConf_pub::MYSQL_USERNAME,WxPayConf_pub::MYSQL_PASSWORD, WxPayConf_pub::MYSQL_DATABASE);
        $sql = "select * from Teacher where teacher = '$c'";
        $rs = $link -> query($sql);
        while( $row = $rs -> fetch_row()){
            $num = $row[1];
            $phone = $row[2];
        }
        $a = array();
        $a[] = $num;
        $a[] = $phone;
        return $a;
        $link -> close();
    }

    private function receiveEvent($object){
        $contentStr = "";
        switch($object -> Event){
            case "subscribe":
                $contentStr = "欢迎关注";
                break;
            case "unsubscribe":
                break;
            case "CLICK":
                switch($object -> EventKey){
                    case "band":
                        $contentStr = "请输入您的学号密码，请注意两个号码之间仅用一个空格符隔开，比如“123456789 12”";
                        break;

                    case "unband":
                        if($this -> deleteDatabase($object -> FromUserName)){
                            $contentStr = "解除绑定成功";
                        }else{
                            $contentStr = "解除绑定失败";
                        }
                        break;

                    case "share":
                        $token = $this -> getImage($object -> FromUserName);
                        $contentStr = array("MediaId"=>$this->finalMediaId($token));
                        break;

                    case "time":
                        list($name, $psd) = $this -> searchByOpenidForNameAndId($object -> FromUserName);
                        $contentStr = $this -> yang_gbk2utf8(getExamTime($name, $psd));
                        break;
                        
                    case "goal":
                        list($name, $psd) = $this -> searchByOpenidForNameAndId($object -> FromUserName);
                        $contentStr = getCourseGoal($name, $psd);
                        break;

                    case "curses":
                        list($name, $psd) = $this -> searchByOpenidForNameAndId($object -> FromUserName);
                        $contentStr = $this -> yang_gbk2utf8($this -> getCourses($name, $psd));
                        //	$contentStr = "hhh";
                        break;

                    case "check":
                        list($name, $psd) = $this -> searchByOpenidForNameAndId($object -> FromUserName);
                        $contentStr = checked($name, $psd);
                        //	$contentStr = $this -> searchByOpenid($object -> FromUserName);
                        break;

                    case "person_msg":
                        list($name, $psd) = $this -> searchByOpenidForNameAndId($object -> FromUserName);
                        $contentStr = getPersonMsg($name, $psd);
                        break;

                    case "route":
					    //这里更换图片mediaid
                        $contentStr = array("MediaId"=>"W9UTwPwMw3uU-VHdS0V6F7mj3dx_jqvm3x62InDKzAUs3efz6EKjRiJeCQp43knQ");
                        break;
						
					case "mapinfo":
					    $contentStr = "请先发送位置给我。点击右下角‘+’号，选择‘位置’发送。\n成功发送位置后，输入如‘附近酒店’、‘附近商家’、‘附近交通’查询周边信息。";
                    
                    default:
                        break;
                }
                break;
            default:
                break;
        }
        if(is_array($contentStr)){
            $resultStr = $this->transmitImage($object, $contentStr);
        }else{
            $resultStr = $this->transmitText($object, $contentStr);
        }
        return $resultStr;
    }




    private function transmitText($object, $content, $funcFlag=0){
        $textTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[text]]></MsgType>
        <Content><![CDATA[%s]]></Content>
        <FuncFlag>%d</FuncFlag>
        </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName,time(),
            $content, $funcFlag);
        return $resultStr;
    }

    private function transmitNews($object, $arr_item, $funcFlag=0){
        if(!is_array($arr_item))
            return;
        $itemTpl = "<item>
                    <Title><![CDATA[%s]]></Title>
                    <Description><![CDATA[%s]]></Description>
                    <PicUrl><![CDATA[%s]]></PicUrl>
                    <Url><![CDATA[%s]]></Url>
                    </item>";
        $item_str="";
        foreach($arr_item as $item)
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        $newsTpl="<xml>
                  <ToUserName><![CDATA[%s]]></ToUserName>
                  <FromUserName><![CDATA[%s]]></FromUserName>
                  <CreateTime>%s</CreateTime>
                  <MsgType><![CDATA[news]]></MsgType>
                  <Content><![CDATA[]]></Content>
                  <ArticleCount>%s</ArticleCount>
                  <Articles>.$item_str</Articles>
                  <FuncFlag>%s</FuncFlag>
                 </xml>";
        $resultStr = sprintf($newsTpl, $object->FromUserName, $object->ToUserName,
            time(), count($arr_item), $funcFlag);
        return $resultStr;
    }

    private function getCourses($name, $psd){

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
        $courses_url = "http://class.sise.com.cn:7001/".substr($a[1]->onclick, 40, -1);
        //获取登录页的信息
        $content = $urls->get_content($courses_url, "cookie_oschina.txt");
        //成功爬取个人课程
        $str = "";
        $html = new simple_html_dom();
        $html->load($content);
        $a = $html->find("td[class='font12']");
        foreach($a as $item){
            if(trim($item->text()) == "&nbsp;"){
                $str = " ".$str;
            }else{
                $str = $str.trim($item->text())." ";
            }

        }
        return $str;
    }


    /**
     *自动判断把gbk或gb2312编码的字符串转为utf8
     *能自动判断输入字符串的编码类，如果本身是utf-8就不用转换，否则就转换为utf-8的字符串
     *支持的字符编码类型是：utf-8,gbk,gb2312
     *@$str:string 字符串
     */
    private function yang_gbk2utf8($str){
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

//回复图片消息
    private function transmitImage($object, $imageArray)
    {
        $itemTpl = "<Image>
					<MediaId><![CDATA[%s]]></MediaId>
					</Image>";

        $item_str = sprintf($itemTpl, $imageArray['MediaId']);

        $xmlTpl = "<xml>
				<ToUserName><![CDATA[%s]]></ToUserName>
				<FromUserName><![CDATA[%s]]></FromUserName>
				<CreateTime>%s</CreateTime>
				<MsgType><![CDATA[image]]></MsgType>
				$item_str
				</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }


    private function getImage($id){
        $appid = "wxc3615f57bddde28f";
        $appsecret = "519f0df003f2b0c9a43952e246f39365";
        $getTokenUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
        $output = $this -> https_request($getTokenUrl);
        $jsoninfo = json_decode($output, true);
//获取token
        $access_token = $jsoninfo["access_token"];
//用户的open_id
        $open_id = $id;
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=$open_id&lang=zh_CN";
        $output = $this -> http_request($url);
        $obj = json_decode($output);
        $path = $obj -> headimgurl;
        $this -> getImg($path, $open_id);
        $this -> getQRCode($open_id);
        return $access_token;
    }

    private function getQRCode($open_id){

        $logo = $open_id.'.jpg';//准好的logo图片
        $QR = 'QRCode.jpg';//已经生成的原始二维码图

        if ($logo !== FALSE) {
            $QR = imagecreatefromstring(file_get_contents($QR));
            $logo = imagecreatefromstring(file_get_contents($logo));
            $QR_width = imagesx($QR);//二维码图片宽度
            $QR_height = imagesy($QR);//二维码图片高度
            $logo_width = imagesx($logo);//logo图片宽度
            $logo_height = imagesy($logo);//logo图片高度
            $logo_qr_width = $QR_width / 5;
            $scale = $logo_width/$logo_qr_width;
            $logo_qr_height = $logo_height/$scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
            //重新组合图片并调整大小
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
                $logo_qr_height, $logo_width, $logo_height);
        }
//输出图片
        imagepng($QR, 'helloweixin.jpg');
//    echo '<img src="helloweixin.jpg">';
    }

    private function http_request($url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if(curl_errno($curl)){
            return 'ERROR'.curl_error($curl);
        }
        curl_close($curl);
        return $data;
    }

    private function https_request($url,$data = null){
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
//获取用户头像
    private function getImg($url, $open_id){
        $ImagePath = dirname(__FILE__). '/'.$open_id.'.jpg';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        $content = curl_exec($curl);
        curl_close($curl);
        file_put_contents($ImagePath, $content);
    }


    private function https_request_mediaId($url, $data)
    {
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

    private function finalMediaId($token){
        $type="image";
        $filepath=dirname(__FILE__)."/helloweixin.jpg";
        $filedata=array("media" => "@".$filepath);
        $access_token = $token;
        $url="https://api.weixin.qq.com/cgi-bin/media/upload?access_token=$access_token&type=$type";
        $res=$this -> https_request_mediaId($url,$filedata);
        $obj = json_decode($res);
        return $obj -> media_id;
    }

}
