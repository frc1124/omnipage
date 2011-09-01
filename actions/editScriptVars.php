<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************
   
   ******************************************************************************************
   * editScriptVars.php                                                                     *
   * Version 0.1                                                                            *
   * Developed by Phil Lopreiato                                                            *
   * Updates variables for 'script' module                                                  *
   ******************************************************************************************/

include "../includes/common.php";
mySQLConnect();

if(isset($_GET['ss_scriptType']) && isset($_GET['ss_pageId']) && isset($_GET['ss_instanceId']) && isset($_GET['ss_url'])){
	$props = array();
	foreach($_GET as $k => $v){
		$spl = str_split($k,3);
		$sub = substr($k,3);
		if($spl[0] == "ss_" && $k != 'ss_scriptType' && $k != 'ss_pageId' && $k != 'ss_instanceId' && $k != 'ss_url'){
			//$w = "UPDATE `moduleProps` SET propValue = '".mysql_real_escape_string($v)."' WHERE pageId = '".mysql_real_escape_string($_GET['ss_pageId'])."' AND instanceId = '".mysql_real_escape_string($_GET['ss_instanceId'])."' AND propName = '".mysql_real_escape_string($sub)."'";
			//$q = mysql_query($w) or die(mysql_error());
			$props[$k] = $v;
			//echo $_GET[$v];
			//echo $w;
		}
	}
	setVariables(mysql_real_escape_string($_GET['ss_pageId']),mysql_real_escape_string($_GET['ss_instanceId']),$props);
	//setVariables($_GET['ss_pageId'],$_GET['ss_instanceId'],$props);
	
	logEntry("Edit Script Variables for script type ".$_GET['ss_scriptType']." on module instance ".$_GET['ss_instanceId']." on page ".$_GET['ss_pageId'].".");
}

?>