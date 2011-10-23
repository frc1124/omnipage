<?
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************
   
   ******************************************************************************************
   * getAlbum.php                                                                       	*
   * This file displays an album's contents on the photo gallery							*
   * Developed by Matt Howard	                                                            *
   * Version 0.1																			*
   ******************************************************************************************/

include "../includes/common.php";

mySQLConnect();
if(is_numeric($_POST['year'])){
	$year = " AND `year` = '".$_POST['year']."'";
}elseif(isset($_GET['year'])){
	$year = " AND `year` = '".$_GET['year']."'";
}else{
	$year = "";
}
//return all pictures in starting album
$query = mysql_query("SELECT * FROM `photos` WHERE `parentId` = '".mysql_real_escape_string($_POST["parentId"])."'".($_POST["parentId"]==0?$year:"")." ORDER BY `YEAR` DESC") or die(mysql_error());

if($_POST["parentId"]==0){
	if(isset($_GET['year'])){
		$head = " From ".$_GET['year'];
	}else{
		$head = "";
	}
	echo "<h2>Photo Gallery".$head."</h2>";
	}

else{
	$albumRow = mysql_fetch_array(mysql_query("SELECT * FROM `photos` WHERE `photoId` = '".mysql_real_escape_string($_POST["parentId"])."'"));

	echo "<a href='javascript:void(0)' onclick='showAlbum(".$albumRow["parentId"].");hideRight();' style='float:right;'>Back</a>
	<h2>".htmlentities($albumRow["title"])."</h2>
	".htmlentities($albumRow["year"])." - ".htmlentities($albumRow["caption"])."<p>";

	}

while($row = mysql_fetch_array($query)){
	if($row["type"] == "1"){
		$thumbImage = mysql_fetch_array(mysql_query("SELECT * FROM `photos` WHERE `parentId` = '".$row["photoId"]."' AND `type` = '0'"));
		$output.= "<div class=\"thumbnail\" onclick=\"showAlbum(".$row["photoId"].")\"><img src=\"/omni/modules/mod_gallery/imageresize.php?type=albumThumbnail&amp;src=".urlencode($thumbImage["filepath"])."\"><br>".$row["title"]." - ".$row["year"]."</div>";
		}
	else{
		$output.= "<div class=\"thumbnail\" onclick=\"showPicture('".$row["filepath"]."','".htmlentities(addslashes($row["caption"]))."',this);\"><img src=\"/omni/modules/mod_gallery/imageresize.php?type=thumbnail&amp;src=".urlencode($row["filepath"])."\"><br>".$row["title"]."&nbsp;</div>";
	}}

echo $output;
?>