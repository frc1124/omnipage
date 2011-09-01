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

//return all pictures in starting album
$query = mysql_query("SELECT * FROM `photos` WHERE `parentId` = '".mysql_real_escape_string($_POST["parentId"])."' ORDER BY `YEAR` DESC") or die(mysql_error());

if($_POST["parentId"]==0){
	echo "<h2>Photo Gallery</h2>";
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