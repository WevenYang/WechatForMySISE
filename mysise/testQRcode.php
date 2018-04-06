<?php
searchByOpenid("oooc9wIo8wc2FDzCcgj8t-Ed1Kv4");
echo $name;



    function searchByOpenid( $c ){
        $link = mysql_connect("sqld.duapp.com:4050","cda32db87250488ca90e731e969a37e4","59268da6a0644ac3b94ce3d707f64d82");
         mysql_select_db("mVDOfecrKJnAnZmoVtdG");
         $sql = "select * from User where open_id = '$c'";
        $rs = mysql_query($sql, $link);
        while( $row = mysql_fetch_object($rs)){
        	global $name = $row -> username;
            global $psd = $row -> password;
        }
        mysql_close();
    }