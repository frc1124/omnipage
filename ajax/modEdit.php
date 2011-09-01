<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* module editing AJAX support
*  version 0.1
*  developed by Matt Howard, Phil Lopreiato
*  receives POST variables and returns module in edit state
*  !** NEEDS SECURITY FEATURES **!
*/

//include common
include "../includes/common.php";

mySQLConnect();
$pageId = $_GET["pageId"];
$instanceId = $_GET["instanceId"];

if(!userPermissions(1,$pageId))
exit;

switch($_GET['mode']){
	case "renderEdit":
		//if($_GET["mode"]=="renderEdit")
		echo renderEdit($pageId,$instanceId);
		break;
	
	case "saveMod":
		//if($_GET["mode"]=="saveMod"){
		$query = mysql_query("SELECT * FROM `modules` WHERE `pageId` = '".mysql_real_escape_string($pageId)."' AND `instanceId` = '".mysql_real_escape_string($instanceId)."'")or die(mysql_error());
		$modId = mysql_fetch_array($query)or die(mysql_error());
		$modId = $modId["modId"];
		$mod = getModule($modId);
		$properties = array();
		foreach($_GET as $k => $v){
			if($k != 'mode')
			$properties[$k] = $v;
		}
		$mod->edit($properties);
		logEntry("Edited mod instance id '".$instanceId."' from pageId '".$pageId."'");
		$properties = getProps($pageId,$instanceId);
		echo $mod->render($properties);
		//}
		break;

	case "delete":
		//if($_GET['mode']=="delete"){
		deleteMod($pageId,$instanceId);
		logEntry("Deleted mod instance id '".$instanceId."' from pageId '".$pageId."'");
		//}
		break;
		
	case "showMod":
		$query = mysql_query("SELECT * FROM `modules` WHERE `pageId` = '".mysql_real_escape_string($pageId)."' AND `instanceId` = '".mysql_real_escape_string($instanceId)."'")or die(mysql_error());
		$modId = mysql_fetch_array($query);
		$modId = $modId["modId"];
		$mod = getModule($modId);
		$properties = getProps($pageId,$instanceId);
		echo $mod->render($properties);
		break;
}
?>