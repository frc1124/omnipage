<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* Top navigation menu 
*  version 0.1
*  developed by Matt Howard
*/

function drawMenu(){
$output = array();
$query = mysql_query("SELECT * FROM `pages` WHERE `parentId` = '0' AND `deleted` != '1' ORDER BY `order` ASC",$GLOBALS["mySQLLink"]);
while($row=mysql_fetch_array($query)){
//make sure user has access and the page isn't a sitemap
	if(userPermissions(0,$row["id"])&&$row["title"]!="Sitemap"&&$row['hide']==0){
		$output[0] .= parseSkin(array("id"=>$row["id"],"title"=>$row["title"],
"url"=>strlen($row["redirect"])>0?$row["redirect"]:("/o/".str_replace(" ","_",$row["title"]))),
"top_menu_item");
		//draw second menu
		$secQuery = mysql_query("SELECT * FROM `pages` WHERE `parentId` = '".$row["id"]."' AND `deleted` != '1' ORDER BY `order` ASC",$GLOBALS["mySQLLink"]);
		while($secRow=mysql_fetch_array($secQuery)){
			if(userPermissions(0,$secRow["id"]) && $secRow['hide']==0){
				$output[1] .= parseSkin(array("id"=>$row["id"],"title"=>$secRow["title"],
"url"=>strlen($secRow["redirect"])>0?$secRow["redirect"]:("/o/".str_replace(" ","_",$row["title"]."/".$secRow["title"]))),
"second_menu_item");
			}
		}
}	}
return $output;
}

?>