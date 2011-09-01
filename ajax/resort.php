<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* module reordering AJAX support
*  version 0.1
*  developed by Matt Howard, Phil Lopreiato
*  receives POST variables and updates order of modules in SQL
*  !** NEEDS SECURITY FEATURES **!
*/

//include common
include "../includes/common.php";

mySQLConnect();

$order = explode(",",$_GET["order"]);
$pageId = $_GET["pageId"];
$instanceIds = array();

if(!userPermissions(1,$_GET["pageId"]))
exit;

for($i=0;$i<sizeOf($order);$i++){
	mysql_query("UPDATE `modules` SET `order` = '".$i."' WHERE `instanceID` = '".$order[$i]."' AND `pageId` = '".$pageId."'")  or die(mysql_error());
	}
logEntry("Resorted mods on pageId '".$pageId."'");
?>