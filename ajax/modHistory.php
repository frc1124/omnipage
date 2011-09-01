<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* module history AJAX support
*  version 0.1
*  developed by Phil Lopreiato
*  receives GET variables and returns module in edit state
*/

//include common
include "../includes/common.php";

mySQLConnect();

$pageId = $_GET["pageId"];
$instanceId = $_GET["instanceId"];

if(!userPermissions(1,$pageId))
exit;

switch($_GET['mode']){
case "showMod":
		$query = mysql_query("SELECT * FROM `modules` WHERE `pageId` = '".mysql_real_escape_string($_GET['page'])."' AND `instanceId` = '".mysql_real_escape_string($_GET['instance'])."'");
		$modId = mysql_fetch_array($query);
		$modId = $modId["modId"];
		$mod = getModule($modId);
		$properties = getProps($_GET['page'],$_GET['instance']);
		echo $mod->render($properties);
		break;
		
	case "getEdits":
		echo getEditHistory($_GET['pageId'],$_GET['instanceId']);
		break;

	case "getEditData":
			//if($_GET['mode']=="getEditData"){
			echo getEditInfo($pageId,$instanceId,$_GET['id']);
			//}
		break;

	case "restoreEdit":
			//if($_GET['mode']=="restoreEdit"){
			echo restoreEdit($_GET['pageId'],$_GET['instanceId'],$_GET['id']);
			//}
			break;
	
	case "pageHistory":
		echo getPageHistory($_GET['page']);
		break;
}
?>