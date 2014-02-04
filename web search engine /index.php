
<?PHP
//Here is a difference between require and include
require("DatabaseClass.php");
$database = new Database("localhost","cs609","root","4608");
$database->connect();

function superExplode ($str, $sep)
{
    $i = 0;
    $arr[$i++] = strtok($str, $sep);
    while ($token = strtok($sep)){
        $arr[$i++] = $token;
    }
    return $arr;
}

$http_urls = array(); //used to store cpatured urls
    function spider($url)
    {
        global $http_urls;
        $url_array = array();

        //判断url是否有效,这个方法效率太低，不能用
        /*$array = get_headers($url,1); 
        if(preg_match('/200/',$array[0])){ 
            echo "<pre/>";
            print_r($array);
        }
        else
        {
            echo "无效url资源！";
        }*/
        $first_content = file_get_contents($url);
        $second_content = file_get_contents($url);
        
        $first_pattern  = "/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/";
        $second_pattern = "/http:\/\/[a-zA-Z0-9\.]+/";
        
        preg_match_all($first_pattern, $first_content, $first_match);
        preg_match_all($second_pattern, $second_content, $second_match);
        
        $result1 = array_unique($first_match[0]);
        $result2 = array_unique($second_match[0]);
        
        $final_urls = array_merge($result1, $result2);
        
        //print_r($final_urls);
        
        for($n = 0; $n < count($final_urls); $n++)
        {
            //echo $final_urls[$i]."<br/>";
            if(@file_get_contents($final_urls[$n])) $http_urls[] = $final_urls[$n];
            //spider($final_urls[$i]);
        }
        //print_r($http_urls);
        //spider($final_urls[0]);
        //parser($url);
    }

        //在下面添加网页存储和数据处理代码
    function parser($url){
        $first_content = file_get_contents($url);
        $meta=get_meta_tags($url);
        
        mysql_query("INSERT INTO `webpage` (`hyperlink`) VALUES ('$url')");
        $id_webpage = mysql_query("SELECT `id` FROM `webpage` WHERE `hyperlink` = '$url'");
        $idwebpage = mysql_fetch_array($id_webpage);

        //这边不知道为什么存不进去所有的meta
        foreach($meta as $key => $value){
            //echo "meta name=\"".$key." \"content=\"".$value."\"<BR/>";
            mysql_query("INSERT INTO `meta_info` (`id_webpage`,`type`,`content`) VALUES ('$idwebpage[0]','$key','$value')");
        }

        $script_pattern = "/<script[^>]*?>.*?<\/script>/si";
        $first_content = preg_replace($script_pattern, ' ', $first_content);
        $strnotag = strip_tags(strtolower($first_content));
        $replace2 = array();
        for ($i=0; $i < 48 ; $i++) { 
            $replace2[$i] = chr($i);
        }
        //print_r($replace2);
        for ($i=58,$j=48; $i < 65; $i++,$j++){
            $replace2[$j] = chr($i);
        }
        //echo $j;
        //print_r($replace2);
        for ($i=91,$j=55; $i < 97; $i++,$j++){
            $replace2[$j] = chr($i);
        }
        //echo $j;
        for ($i=123,$j=61; $i < 255; $i++,$j++){
            $replace2[$j] = chr($i);
        }
        //print_r($replace2);
        $str2 = str_replace($replace2,' ',$strnotag);
        //regular exp to make ' '
        $nbsp = "nbsp";
        $str2 = str_replace($nbsp, ' ', $str2);
        $space_pattern = "/[ ]+/";
        $str2 = preg_replace($space_pattern, ' ', $str2);//这个可以直接用正则表达式替换
        
        //echo $str2."<br/>";
        trim($str2);
        
        $arr = superExplode ($str2, " ");//这边加0之后可以正常运行，但是还是有多余的空格等需要处理
        //print_r($arr);

        $final=array_count_values($arr);
        // arsort($final);
        unset($final["a"]);
        unset($final["an"]);
        unset($final["the"]);
        unset($final["for"]);
        unset($final["in"]);
        unset($final["by"]);
        unset($final["to"]);
        unset($final["on"]);
        unset($final["at"]);
        unset($final["as"]);
        unset($final["I"]);
        unset($final["you"]);
        unset($final["he"]);
        unset($final["she"]);
        unset($final["it"]);
        unset($final["my"]);
        unset($final["his"]);
        unset($final["her"]);
        unset($final["its"]);
        unset($final["they"]);
        unset($final["them"]);
        unset($final["me"]);
        unset($final["him"]);
        unset($final["their"]);
        unset($final["our"]);
        unset($final["your"]);
        unset($final["and"]);
        unset($final["that"]);
        unset($final["mr"]);
        unset($final["from"]);
        unset($final["with"]);
        unset($final["was"]);
        unset($final["were"]);
        unset($final["be"]);
        unset($final["have"]);
        unset($final["has"]);
        unset($final["but"]);
        unset($final["will"]);
        unset($final["or"]);
        unset($final["about"]);
        unset($final["who"]);
        unset($final["us"]);
        unset($final["of"]);
        unset($final["is"]);
        unset($final["am"]);
        unset($final["are"]);
        
        foreach($final as $words=> $count){

            if(strlen($words) < 2) unset($final["$words"]);
            else {//这里要判断是否表里已有这个词。
                $intable = mysql_query("SELECT `id` FROM `key_words` WHERE `word` = '$words'");
                $id_judge = mysql_fetch_array($intable);
                if($id_judge != NULL){
                    mysql_query("INSERT INTO `key_webpage` (`id_webpage`, `id_word`, `frequency`) VALUES('$idwebpage[0]', '$id_judge[0]', '$count')");
                    mysql_query("UPDATE `key_words` SET `file_amount` = `file_amount` + 1 WHERE `word` = '$words'");
                }
                else{
                mysql_query("INSERT INTO `key_words` (`word`) VALUES ('$words')");
                mysql_query("UPDATE `key_words` SET `file_amount` = `file_amount` + 1 WHERE `word` = '$words'");
                $id_word = mysql_query("SELECT `id` FROM `key_words` WHERE `word` = '$words'");
                $idword = mysql_fetch_array($id_word);
                mysql_query("INSERT INTO `key_webpage` (`id_webpage`, `id_word`, `frequency`) VALUES('$idwebpage[0]', '$idword[0]', '$count')");
                }
            }
        }
    }

 
    spider("http://www.nydailynews.com/new-york");
    parser("http://www.nydailynews.com/new-york");
    //print_r($http_urls);
    // $len = count($http_urls);
    // echo "the length of http_urls is {$len}<br/>";
    for($i = 0; $i < 10; $i++){
        //if($len > 400) break;
        //echo $http_urls[$i]."<br/>";
        spider($http_urls[$i]);
        //parser($http_urls[$i]);
        $http_urls = array_merge(array_unique($http_urls), $a = array());//删掉重复的url，并重新排列http_urls数组
        //if($i == $len - 1) $len = count($http_urls);
        
    }
    print_r($http_urls);
    for ($i=0; $i < 15; $i++) { 
        if($http_urls[$i] == "http://www.stevens.edu" || $http_urls[$i] == "http://stevens.edu") {
            $http_urls[$i] = $http_urls[$i]."/sit/";
        }
        parser($http_urls[$i]);
    }
    $database->close();
    
?>
