<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* module history support
*  version 0.1
*  developed by Matt Howard, Phil Lopreiato
*/

function setVariables($pageId, $instanceId, $variables){
	global $user,$mySQLLink;
	$pageId = mysql_real_escape_string($pageId);
	$instanceId = mysql_real_escape_string($instanceId);
	//put old edit data into database
	//$oldProps = getProps($pageId,$instanceId); //array();
	$propQuery = mysql_query("SELECT * FROM `moduleProps` WHERE `instanceId` = '".$instanceId."' AND `pageId` = '".$pageId."'",$mySQLLink) or die(mysql_error());
	while($propRow = mysql_fetch_array($propQuery)){
			$oldProps[$propRow["propName"]]=$propRow["propValue"];
	}
	$id = mysql_fetch_array(mysql_query("SELECT * FROM editHistory ORDER BY editId DESC")); //get current highest edit ID
	$string = "INSERT INTO editHistory (editId, pageId, instanceId, time, ip, user) VALUES ('".($id['editId']+1)."','".$pageId."','".$instanceId."','".time()."','".$_SERVER['REMOTE_ADDR']."','".$user->data["username_clean"]."')";
	mysql_query($string)or die(mysql_error()); //insert edit into editHistory table
	
	foreach($oldProps as $name => $value){ //for each old module property, insert a row into modulePropsHistory table with data
		//echo "INSERT INTO modulePropsHistory (editId,propName,propValue) VALUES ('".($id['editId']+1)."','".$name."','".$value."')";
		//if($name != "pageId" && $name != "instanceId")
		if($name != "pageId" || $name != "instanceId"){
			mysql_query("INSERT INTO modulePropsHistory (editId, propName, propValue) VALUES ('".($id['editId']+1)."','".mysql_real_escape_string($name)."','".mysql_real_escape_string(stripslashes($value))."')")or die(mysql_error());
		}
	}

	foreach ($variables as $key => $value){ //for each new property (passed on through $variables array), update current mySQL record
		$exist = mysql_query("SELECT * FROM `moduleProps` WHERE `pageId` = '".$pageId."' AND `instanceId` = '".$instanceId."' AND `propName` = '".mysql_real_escape_string($key)."'")or die(mysql_error());
		if(mysql_num_rows($exist) > 0){
			mysql_query("UPDATE `moduleProps` SET `propValue` = '".mysql_real_escape_string($value)."' WHERE `pageId` = '".$pageId."' AND `instanceId` = '".$instanceId."' AND `propName` = '".mysql_real_escape_string($key)."'")or die(mysql_error());
		}else{
			mysql_query("INSERT INTO `moduleProps` (pageId, instanceId, propName, propValue) VALUES ('".$pageId."','".$instanceId."','".mysql_real_escape_string($key)."','".mysql_real_escape_string($value)."')")or die(mysql_error());
		}
	}
	
	return true;
}

function getEditHistory($pageId, $instanceId){
	$output = "";
	$editQuery = mysql_query("SELECT * FROM `editHistory` WHERE `pageId` = '".mysql_real_escape_string($pageId)."' AND `instanceId` = '".mysql_real_escape_string($instanceId)."' ORDER BY editId ASC")or die(mysql_error());
	if(mysql_num_rows($editQuery)<1){
		$output .= "<p>".$pageId." - ".$instanceId."</p><p>This module has no edit history.</p>";
	}else{
		$output .= "<table style='width:100%' id='editHistory_".$pageId."_".$instanceId."' name='editHistory_".$pageId."_".$instanceId."'>";
		$output .= "<tr id='editHistory_".$pageId."_".$instanceId."_headerRow' name='editHistory_".$pageId."_".$instanceId."_headerRow' style='text-decoration:bold;'><td>Edit Time</td><td>User</td><td>IP</td><td>Show/Hide Edit Data</td></tr>";
		while($row = mysql_fetch_assoc($editQuery)){
			$output .= "<tr name='edit_".$row['editId']."' id='edit_".$row['editId']."'>";
			$output .= "<td name='edit_".$row['editId']."_time'>".(date('m\-d\-Y \a\t G\:i:s',$row['time']))."</td>";
			$output .= "<td name='edit_".$row['editId']."_user'>".$row['user']."</td>";
			$output .= "<td name='edit_".$row['editId']."_ip'>".$row['ip']."</td>";
			$output .= "<td name='edit_".$row['editId']."_select'><a href='javascript:void(0)' onclick='getEditData(".$pageId.",".$instanceId.",".$row['editId'].")'>View Edit Data</a></td>";
			$output .= "</tr>";
		}
		$output .= "</table><button name='return' id='return' onclick='showMod(".$pageId.",".$instanceId.")'>Return to Module</button>";
		$output .= "<div id='editData'></div>";
	}
	
	return $output;
}

function getEditInfo($page,$instance,$id){
	$pageId = mysql_real_escape_string($page);
	$instanceId = mysql_real_escape_string($instance);
	$editId = mysql_real_escape_string($id);
	$output = "";
	$q = mysql_query("SELECT * FROM `modulePropsHistory` WHERE editId = '".$editId."'")or die(mysql_error());
	$output .= "<table id='editData_".$_GET['id']."' name='editData_".$editId."' style='width:100%;'><tr style='text-decoration:bold;'><td>Property Name</td><td>Property Value</td></tr>";
	while($row = mysql_fetch_assoc($q)){
		$output .= "<tr><td style='vertical-align:text-top;'>".$row['propName']."</td><td><div style='overflow:auto;width:100%'>".htmlentities($row['propValue'])."</div></td></tr>";
	}
	$output .= "</table>";
	$mod = mysql_fetch_array(mysql_query("SELECT * FROM modules WHERE pageId = '".$pageId."' AND instanceId = '".$instanceId."'"))or die(mysql_error());
	$output .= "<button name='revertButton' id='revertButton' onclick='revertEdit(".$pageId.",".$instanceId.",".$editId.")'>".($mod['deleted']==0?"Restore Module to this State":"Undelete Module to this State")."</button>";
	return $output;
}

function restoreEdit($page,$instance,$edit){
	global $mySQLLink;
	$out = "";
	$pageId = mysql_real_escape_string($page);
	$instanceId = mysql_real_escape_string($instance);
	$editId = mysql_real_escape_string($edit);
	
	$q = mysql_fetch_array(mysql_query("SELECT * FROM modules WHERE pageId = '".$pageId."' AND instanceId = '".$instanceId."'"))or die(mysql_error());
	if($q['deleted']==1){
		$res = mysql_query("UPDATE modules SET deleted = '0' WHERE pageId = '".$pageId."' AND instanceId = '".$instanceId."'")or die(mysql_error());
		$res2 = mysql_query("UPDATE moduleProps SET deleted = '0' WHERE pageId = '".$pageId."' AND instanceId = '".$instanceId."'")or die(mysql_error());
	}
	
	$s = "SELECT * FROM `modulePropsHistory` WHERE `editId` = '".$editId."'";
	$q = mysql_query($s,$mySQLLink)or die(mysql_error());
	$props = array();
	while($propRow = mysql_fetch_array($q,MYSQL_ASSOC)){
		$props[$propRow["propName"]]=$propRow["propValue"];
	}
	$update = setVariables($pageId,$instanceId,$props);
	if($update){
	logEntry("Reverted mod id on page id ".$pageId." and instance id ".$instanceId." to edit state ".$editId);
	$out .= "Sucessfully restored module";
	}else{
	$out .= $update;
	}
	return $out;
}

function restorePage($page){
	global $mySQLLink;
	$out = "";
	$pageId = mysql_real_escape_string($page);
	//undelete page
	$page = mysql_query("UPDATE pages SET deleted = '0' WHERE id = '".$pageId."'")or die(mysql_error());
	//undelete page's modules
	$mod = mysql_query("UPDATE modules SET deleted = '0' WHERE pageId = '".$pageId."'")or die(mysql_error());
	$modProp = mysql_query("UPDATE moduleProps SET deleted = '0' WHERE pageId = '".$pageId."'")or die(mysql_error());
	
	if($page && $mod && $modProp){
		$out = "Sucessfully restored page with ID ".$pageId;
		logEntry("Restored page ID ".$pageId);
	}else{
		$out = "error restoring page";
	}
	return $out;
}

function pageHistory($page){
	$q = mysql_query("SELECT * FROM modules WHERE pageId = '".mysql_real_escape_string($page)."'");
	$out = "<ul>";
	while($row = mysql_fetch_array($q)){
		$out .= "<li><a href='javascript:void(0);' class='modHistoryLink' id='history_".$page."_".$row['instanceId']."'>Instance Id: ".$row['instanceId']." ".($row['deleted']==1?"- deleted":"")."</a></li>";
	}
	$out .= "</ul>";
	
	$page = mysql_fetch_array(mysql_query("SELECT * FROM pages WHERE id = '".mysql_real_escape_string($page)."'"));
	if($page['deleted'] == 1){
		$out .= "<br/><p><b>Page Has Been Deleted";
		$out .= "<br/><button id='restorePage_".$page."_Button' class='restorePage'>Restore this Page</button></p>";
	}
	
	return $out;
}
?>