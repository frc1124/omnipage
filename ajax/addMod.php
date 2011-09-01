<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************
   
   ******************************************************************************************
   * addModule.php                                                                          *
   * This file  adds a module on a given page												*
   * Developed by Matt Howard, Phil Lopreiato                                               *
   * Version 0.1																			*
   ******************************************************************************************/

//include common
include "../includes/common.php";

mySQLConnect();

if(!userPermissions(1,$_GET["pageId"]))
exit;

//add modules
if($_GET["mode"]=="add"){
//get mod object
$mod =  getModule($_GET["modId"]);

//create `modules` table row

//but first get number of mods already on page from `instance` and `order`
$query = mysql_query("SELECT `instanceId` FROM `modules` WHERE `pageId` = '".mysql_real_escape_string($_GET["pageId"])."' ORDER BY `instanceId` DESC",$GLOBALS["mySQLLink"]);
$count = mysql_fetch_array($query);
$count = $count["instanceId"]+1;

//now finally create row
$query = mysql_query("INSERT INTO `modules` VALUES ('".mysql_real_escape_string($_GET["pageId"])."','".mysql_real_escape_string($_GET["modId"])."','$count','$count','0')");

//last, make properties

$mod->setup();
for($i=0;$i<sizeof($mod->sqlNames);$i++){
	mysql_query("INSERT INTO `moduleProps` VALUES ('$count','".mysql_real_escape_string($_GET["pageId"])."','".mysql_real_escape_string($mod->sqlNames[$i])."','".mysql_real_escape_string($mod->sqlDefaults[$i])."','0')") or die(mysql_error());
	echo "INSERT INTO `moduleProps` VALUES ('$count','".mysql_real_escape_string($_GET["pageId"])."','".mysql_real_escape_string($mod->sqlNames[$i])."','".mysql_real_escape_string($mod->sqlDefaults[$i])."','0')";
	}
	
logEntry("Added mod '".$mod->title."' in pageId '".$_GET["pageId"]."'");
}

//list all modules
if($_GET["mode"]=="list"){
	$output = "";
	for($i=0;$mod=getModule($i);$i++){
		$skinVars = array();
		$skinVars["modId"] = $i;
		$skinVars["path"] = $mod->path;
		$skinVars["title"] = $mod->title;
		$skinVars["desc"] = $mod->description;
		
		$output.=parseSkin($skinVars,"module_list_line");
	}
	echo parseSkin(array("output"=>$output),"module_list");
}
?>