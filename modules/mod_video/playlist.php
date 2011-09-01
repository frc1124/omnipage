<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* Playlist creator
* v0.1
* Works with JW Player
* Reference: http://www.longtailvideo.com/support/jw-player/jw-player-for-flash-v5/12537/xml-playlist-support
* Programmed by Matt Howard
*/
	include "../../includes/common.php";
	
	header("Content-type:text/xml");
	
	mySQLConnect();
	
	//get video id
	$id = $_GET["id"];
	
	$query = mysql_query("SELECT * FROM `videos` WHERE `id` = '".mysql_real_escape_string($id)."'") or die(mysql_error());
	
	$row = mysql_fetch_array($query) or die("Video not found.");
?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/" xmlns:jwplayer="http://developer.longtailvideo.com/trac/"> 
<channel> 
<title>MRSS Playlist Playlist</title> 
<item> 
<title>Big Buck Bunny</title> 
<media:content url="/omni/media/videos/<? echo $row["filename"];?>" /> 
<jwplayer:http.startparam>start</jwplayer:http.startparam>
<description>Big Buck Bunny is a short animated film by the Blender Institute,
part of the Blender Foundation.</description>
<jwplayer:captions.file>/omni/media/videos/<? echo substr($row["filename"],0,strrpos($row["filename"],"."));?>.xml</jwplayer:captions.file> 
</item>
</channel> 
</rss>