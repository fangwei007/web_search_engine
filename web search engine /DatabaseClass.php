<!DOCTYPE html>
<html>
<head>
<title>Database Class</title>
</head>
<body>
<?php
 //************************
 // $database = new Database("localhost", "indexer3", "root", "123");
  /*
   include("DatabaseClass.php");
   $db->connect();
    //main code
	$db->close();
  */
 //************************
class Database
{
 private $host = "localhost";
 private $db = "cs609";
 private $user = "root";
 private $pw = "4608";
 
 private $link;

 function Database($host,$db,$user,$pw){
	 $this->host = $host;
	 $this->db = $db;
	 $this->user = $user;
	 $this->pw = $pw;
 }

function connect(){
	 $this->link = mysql_connect($this->host, $this->user,$this->pw) or die("Could not connect to: ".mysql_error());
	/* $this->link = @mysql_connect($this->Hostserver, $this->User, $this->Password);//use @ to avoid leaking account info while warning
	 if (!$this->linked){
		 die('Could not connect: ' . mysql_error());
		 }//select database;*/
    $db_selected = mysql_select_db($this->db,$this->link) or die("Can't use DB: ：".mysql_error());
	/*$db_selected = @mysql_select_db($this->Database, $this->linked);
	if (!$db_selected){
		die ("Can't use DB: " . mysql_error());
		}*/
	 }
	 //Advanced Syntax: $db_selected = mysql_select_db($this->Database, $this->linked) or die ("Can't use DB: " . mysql_error());
 //************************
 //close connection to the database
 //************************
function close(){
 mysql_close($this->link); 
}


}
?>
</body>
</html>