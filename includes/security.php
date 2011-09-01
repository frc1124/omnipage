<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* Security features
*  version 0.1
*  developed by Matt Howard
*/

function logEntry($entry){
global $user,$root_path;
$file = fopen("$root_path/logs/security.txt",'a');
$entry = str_replace("\n","\\n",$entry);
fwrite($file,$user->data["user_email"].":".$user->data["username_clean"].":".$user->data["user_ip"].":".date("D M j G:i:s T Y").":".$entry."\n");
}

function userPermissions($type,$pageId=""){
	global $user,$page;
	//if pageId is not specified, use current one
	if($pageId == "")
		$pageId = $page->pageId;
	
	//everyone starts out without permission
	$result = false;
	
	//Admin and Management groups and Ligotti have automatic full permission (and evan for proofreading process)
	if(($user->data["group_id"]==5)||($user->data["group_id"]==10)||($user->data["user_id"]==73)||($user->data["user_id"]==22698))
	$result = true;
	
	$query = mysql_query("SELECT * FROM `pages` WHERE `id` = '".mysql_real_escape_string($pageId)."'") or die(mysql_error());
	$row = mysql_fetch_array($query);
	//if not private, user has read access
	if($row["private"]=="0"&&$type==0)
	$result = true;
	
	//inherit
	if($row["inheritPermissions"]=="1"&&userPermissions($type,$row["parentId"]))
	$result = true;
	
	//check database for individual user permissions
	$query = mysql_query("SELECT * FROM `pagePermissions` WHERE `pageId` = '".mysql_real_escape_string($pageId)."' AND `userId` = '".$user->data["user_id"]."'");
	$row = mysql_fetch_array($query);	
	
	if($row){
		if($row["type"]==0 && $type==0)
		$result = true;
		if($row["type"]==1)
		$result = true;
		}
	return $result;
	}
?>