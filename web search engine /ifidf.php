<!DOCTYPE html>
<html>
<head>
<meta content="text/html; charset=utf-8" />
<title></title>
<style type="text/css">
body{
 background: #FFF;
 color: #000;
 font-family: Arial, sans-serif;
 font-size: 13px;
 text-align: center;
 margin-top: 3px;
}
a:link{
 color: #00C;
}
a:visited{
 color: #551a8b;
}
#login{
 text-align: right;
}
#stype{
 margin-bottom: 4px;
}
#stype span{
 padding: 0 6px;
}
#search{
	margin: 0 auto;
	width: 600px;
	position: relative;
}
#more{
 width: 4em;
 position: absolute;
 top: 0;
 right: -4.5em;
}
#ft{
 margin: 54px auto 16px;
}
</style>
</head>
<body>
<div style="margin: 4px auto 19px;"><img src="Yonlanda.jpg" alt="Yolanda" width="203" height="55" /></div>
<form align="center" action="ifidf.php" method="post">
<!-- <select name="target">
  <option>FullText</option>
  <option>Metatag(type)</option>
  <option>FullText & Metatag</option>
</select> -->
<input type="text" name="search" value="" size="60" />
<input type="submit" value="Yolanda Search"/>
</form>
<div align="left">
<?PHP
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

if(isset($_POST['search'])){
	//保证输入的关键词分析----------------------------------------------
	$replace = array();
        for ($i=0; $i < 48 ; $i++) { 
            $replace[$i] = chr($i);
        }
        //print_r($replace2);
        for ($i=58,$j=48; $i < 65; $i++,$j++){
            $replace[$j] = chr($i);
        }
        //echo $j;
        //print_r($replace2);
        for ($i=91,$j=55; $i < 97; $i++,$j++){
            $replace[$j] = chr($i);
        }
        //echo $j;
        for ($i=123,$j=61; $i < 255; $i++,$j++){
            $replace[$j] = chr($i);
        }
        //print_r($replace2);
    $input = str_replace($replace,' ',strtolower($_POST['search']));
	$space_pattern = "/[ ]+/";
    $input = preg_replace($space_pattern, ' ', $input);
	trim($input);
	//----------------------------------------------------------------


	$arr = superExplode($input, ' ');//取词
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
	//print_r($arr);
	//这里尝试用视图去建立得到一个tfidf表---------------------------------
	$num1 = mysql_query("SELECT COUNT(`id`) from `webpage`");
	$number1 = mysql_fetch_array($num1);
	//echo $number1[0]."<br/>";

    $weight = array();
	$i = 0;
	$links = mysql_query("SELECT `hyperlink` from `webpage`");
	$links_fetch = mysql_fetch_array($links);
	while($i<$number1[0]){
		$weight[$i][0] = 0;
		$weight[$i][1] = $links_fetch[0];
		$links_fetch = mysql_fetch_array($links);
		$i++;
	}
	//print_r($weight);
	//print_r($arr);
	// $query = mysql_query("SELECT * FROM `key_words` WHERE `word` like '$arr[0]'");
	// $words = mysql_fetch_array($query);
	// //print_r($words);
	// $number2 = $words[2];
	// //echo $number2;
	// $result = $number1[0] / $number2;
	// $idf = log10($result);
	// echo $idf."<br/>";
























	//----------------------------------------------------------------
	foreach ($final as $keyword => $count) {
		$query = mysql_query("SELECT * FROM `key_words` WHERE `word` like '$keyword'");
		$words = mysql_fetch_array($query);//这部分还是可以正确选出的
		$number2 = $words[2];
		if($number2 != 0) {	    
			$result = $number1[0] / $number2;
			$idf = log10($result);//这里已经求出idf
		}
		else $idf = 0;
	    //echo $idf."<br/>";

		// $query1 = mysql_query("SELECT * FROM `key_webpage` WHERE '$words[0]' = `id_word`");
		// $idwebpage = mysql_fetch_array($query1);
		// echo $idwebpage[1].' '.$idwebpage[3];


		//以下处理并排序所有链接
		$i = 0;
		$query1 =mysql_query("SELECT `id` FROM `webpage`");
		$idwebpage = mysql_fetch_array($query1);
		while($idwebpage !=NULL){
			$query2 = mysql_query("SELECT `frequency` FROM `key_webpage` WHERE '$idwebpage[0]' = `id_webpage` AND '$words[0]' = `id_word`");
			$tf = mysql_fetch_array($query2);
			if($tf!= NULL) $weight[$i][0] += $tf[0]*$idf;
			$i++;
			$idwebpage = mysql_fetch_array($query1);
		}

		// while($idwebpage !=NULL){
		// 	$query2 = mysql_query("SELECT `hyperlink` FROM `webpage` WHERE '$idwebpage[1]' = `id`");
		// 	$hyperlink = mysql_fetch_array($query2);
		// 	// print_r($query);
		// 	echo "<a href='$hyperlink[0]'><b>".$hyperlink[0]."</b></a><br/>";
		// 	//echo $hyperlink[0].$words[1];
		// 	light($hyperlink[0], $words[1]);
		// 	//echo $hyperlink[0]."<br/>";
		// 	$idwebpage = mysql_fetch_array($query1);
		// 	//echo $idwebpage[1].' '.$idwebpage[3];
		// }
	}
	//SelectionSort($weight,$number1[0]);
	BubbleSort($weight,$final);
	
}

// function Swap($A,$i, $j)
// {
//     $t = $A[$i];
//     $A[$i] = $A[$j];
//     $A[$j] = $t;
// }
function BubbleSort($arr,$input){ 
$num = count($arr); 
for($i=1;$i<$num;$i++){ 
	for($j=$num-1;$j>=$i;$j--){ 
		//echo $arr[$j][0]." ".$arr[$j-1][0]."<br/>";
			if($arr[$j][0]>$arr[$j-1][0]){ 
			$iTemp = $arr[$j-1]; 
			$arr[$j-1] = $arr[$j]; 
			$arr[$j] = $iTemp; 
		} 
	} 
} 
//打印权重数组的部分
//print_r($arr);
echo "<br/>";
echo "<table align=center>";
	foreach ($arr as $key => $value){
		echo "<h1><a href='$value[1]'><b>".$value[1]."</b></a></h1><br/>";
		light($value[1], $input);
	}
	echo "</table>";
return $arr; 
} 
// function Swap($a, $b)
// {
//     $temp = $a;
//     $a = $b;
//     $b = $temp;
// }
// function SelectionSort($arr, $size)
// {
//     //int i, j, min;
//     for($i=0;$i<$size-1;$i++)
//     {
//         $min = $i;
//         for($j=$i+1;$j<$size;$j++)
//             if($arr[$min][0] > $arr[$j][0])
//                 $min = $j;
//         Swap($arr[$i], $arr[$min]);
//     }
// }
// function QKSort($A,$l,$u){
//     if($l>=$u)
//         return ;
//     $i=$l-1;
//     $x=$A[$u][0];
//     echo $x."<br/>";
//     $tmp;
//     for($j=$l;$j<$u;$j++){
//         if($A[$j][0]<=$x){
//             $i++;
//             $tmp = $A[$j];
//             $A[$j] = $A[$i];
//             $A[$i] = $tmp;
//         }
//     }
//     $i++;
//     swap($A,$i,$u);
//     QKSort($A,$l,$i-1);
//     QKSort($A,$i+1,$u);
// }

function light($url,$input){
	//$arr = superExplode($input, ' ');
	$file = file_get_contents($url);
	$script_pattern = "/<script[^>]*?>.*?<\/script>/si";
    $first_content = preg_replace($script_pattern, ' ', $file);
    $strnotag = strip_tags(strtolower($file));
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
        unset($replace2[","]);
        unset($replace2["."]);
        $str2 = str_replace($replace2,' ',$strnotag);
    	$nbsp = "nbsp";
    	$str2 = str_replace($nbsp, ' ', $str2);
    	$space_pattern = "/[ ]+/";
        $str2 = preg_replace($space_pattern, ' ', $str2);
    echo substr($str2, 0,200);
	foreach ($input as $keyword => $count) {
		//$flag = 0;
	//$file = file_get_contents($url);
	$fh = fopen($url, "r");
	if($fh){
		while(!feof($fh)) {
			$str=strtolower(fgetss($fh));
			//$str = strip_tags($str);
			//if($flag++ == 0) echo $str;
			if(strrpos($str,$keyword)||strrpos($str,$keyword)===0){
				$find = array("$keyword");
				$newkeyword = "<cite style='color: orange;'>".$keyword."</cite>";
				$replace=array("$newkeyword");
				$str2 = str_replace($replace2,' ',$str);
    			$nbsp = "nbsp";
    			$str2 = str_replace($nbsp, ' ', $str2);
    			$space_pattern = "/[ ]+/";
       		    $str2 = preg_replace($space_pattern, ' ', $str2);
				echo "..".str_replace($find,$replace,$str2)."..<br/>";
				break;
			}
			// else {
			// 	echo $str;
			// 	break;
			// }
		}
		fclose($fh);
	}
}
echo "<br/>";
}

// function light($url,$input){
// 	//$arr = superExplode($input, ' ');
// 	foreach ($input as $key => $keyword) {
// 	$first_content = file_get_contents($url);

//     $first_content = preg_replace($script_pattern, ' ', $first_content);
//     $strnotag = strip_tags(strtolower($first_content));
// 	//$fh = fopen($url, "r");
// 	$find = array("$keyword");
// 	$newkeyword = "<cite style='color: orange;'>".$keyword."</cite>";
// 	$replace=array("$newkeyword");
// 	str_replace($find, $replace, $strnotag);
// 	$nbsp = "nbsp";
//     $str2 = str_replace($nbsp, ' ', $strnotag);
//     echo "<html>";
// 	echo $strnotag;
// 	echo "</html>";
// 	$head = strstr($strnotag, $keyword);
// 	//echo substr($head, 0,100);
// 	// if($fh){
// 	// 	while(!feof($fh)) {
// 	// 		$str=strtolower(fgetss($fh));
// 	// 		$str = strip_tags($str);
// 	// 		if(strrpos($str,$keyword)||strrpos($str,$keyword)===0){
// 	// 			$find = array("$keyword");
// 	// 			$newkeyword = "<cite style='color: orange;'>".$keyword."</cite>";
// 	// 			$replace=array("$newkeyword");
// 	// 			echo "..".str_replace($find,$replace,$str)."..";
// 	// 			//break;
// 	// 		}
// 			// else {
// 			// 	echo $str;
// 			// 	break;
// 			// }
// // 		}
// // 		fclose($fh);
// // 	}
// }
// echo "<br/>";
// }




$database->close();
?>
</div>
</body>
</html>