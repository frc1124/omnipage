<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* page support
*  version 0.1
*  developed by Matt Howard
*  creates virtual directory and such
*/

class url{
	
	var $fullUrl,$pageId,$error404,$pageTitle,$privatePage;
	
	//initialize
	public function init(){
	global $mySQLLink;
	
	//full url
	$this->fullUrl = "http://www.uberbots.org".$_SERVER['REQUEST_URI'];

	//everything after ".org"
	$url = $_SERVER['REQUEST_URI'];

	//bug fix: if only "/o/?sid=blah", add home
	if(substr($url,0,4)=="/o/?")
	$url = "/o/home?".substr($url,4);
	
	$explode = explode("/",$url);
	
	$error404 = false;
	
	$parentId = 0;
	
	if($explode[sizeof($explode)-1]=="")
	unset($explode[sizeof($explode)-1]);
	
	$explode[sizeof($explode)-1] = explode("?",$explode[sizeof($explode)-1]);
	
	$explode[sizeof($explode)-1] = $explode[sizeof($explode)-1][0];
	
	for($i=2;$i<sizeOf($explode);$i++){
		
		$query = mysql_query("SELECT * FROM `pages` WHERE `deleted` = '0' AND `parentId` = '".$parentId."' AND `title` LIKE '".mysql_real_escape_string(str_replace("_"," ",$explode[$i]),$mySQLLink)."'",$GLOBALS["mySQLLink"]) or die(mysql_error());
		
		$row = mysql_fetch_array($query);
		
		$this->error404 = $row ? false : true;
		
		$parentId = $row["id"];
		$this->title = $row["title"];
		}
	
	if(sizeOf($explode)==2||sizeOf($explode)==1){
		$query = mysql_query("SELECT * FROM `pages` WHERE `deleted` = '0' AND `title` LIKE 'home'",$GLOBALS["mySQLLink"]) or die(mysql_error());
		
		$row = mysql_fetch_array($query);
		
		$parentId = $row["id"];
		$this->title = $row["title"];
		}
	//redirect
	if(strlen($row["redirect"])>0){
		header("location:".$row["redirect"]);
		exit;
		}

	$this->pageId = $parentId;
	
	$this->privatePage = $row["private"]?true:false; 
	}
	
	//return breadcrumbs for page
	public function breadCrumbs($page = -1){
		if($page = -1)
			$page = $this->pageId;
		$breadCrumbs = "<a href=\"/o\">Home</a>";
		$index = $page;
		$titles = $redirects = array();
		//get all pages parent of starting page
		while($index != 0){
			$query = mysql_query("SELECT `title`, `parentId`, `redirect` FROM `pages` WHERE `id` = $index") or die(mysq_error());
			$row = mysql_fetch_array($query);
			$titles[] = $row["title"];
			//check if redirect is false page (container)
			$redirects[] = ($row["redirect"]=="javascript:void(0);")?true:false;
			$index = $row["parentId"];
		}
		$url = "";
		//reassemble $titles into the breadcrumbs
		for($i = sizeof($titles)-1;$i >= 0 && sizeof($titles) != 0;$i--){
			$url .= "/".str_replace(" ","_",$titles[$i]);
			$breadCrumbs .= " > ".($redirects[$i]?"<a>":"<a href=\"/o".$url."\">").$titles[$i].($redirects[$i]?"</a>":"</a>");
		}
		return $breadCrumbs;
	}
}

?>