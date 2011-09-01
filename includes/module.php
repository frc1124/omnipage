<?PHP
/* module support
*  version 0.1
*  developed by Matt Howard, Phil Lopreiato
*/

//all module includes
$dirHandle = opendir($root_path."/modules");
while($row = readdir($dirHandle)){
	if(file_exists($root_path."/modules/".$row."/mod.php")){
	include $root_path."/modules/".$row."/mod.php";
	}}

//render modules of given page ID
function renderModules($pageId){
	global $mySQLLink;
	$query = mysql_query("SELECT * FROM `modules` WHERE `pageId` = '".$pageId."' AND `deleted` = '0' ORDER BY `order` ASC",$mySQLLink) or die(mysql_error());
	$output = "";
	while ($row = mysql_fetch_array($query)){
		$properties = getProps($pageId,$row["instanceId"]);
		$module = getModule($row["modId"])->render($properties);
		$output .= parseSkin(array("content"=>$module,"pageId"=>$pageId,"instanceId"=>$row["instanceId"]),"basic_module");
		}
	return $output;
	}

//render edit state of modules
function renderEdit($pageId,$instanceId){
	global $mySQLLink;
	$query = mysql_query("SELECT `modId` FROM `modules` WHERE `instanceId`='$instanceId' AND `pageId` = '$pageId'",$mySQLLink) or die(mysql_error());
	$row = mysql_fetch_array($query);
	$properties = getProps($pageId,$instanceId);
	$module = getModule($row["modId"])->renderEdit($properties);
	return $module;
	}
	
//get properties in array
function getProps($pageId,$instanceId){
	global $mySQLLink;
	$properties = array();
	$propQuery = mysql_query("SELECT * FROM `moduleProps` WHERE `instanceId` = '".$instanceId."' AND `pageId` = '".$pageId."'",$mySQLLink) or die(mysql_error());
	while($propRow = mysql_fetch_array($propQuery)){
		$properties[$propRow["propName"]]=$propRow["propValue"];
	}
	$properties["pageId"]=$pageId;
	$properties["instanceId"]=$instanceId;
	return $properties;
	}

//return mod object from modId
function getModule($modId){
	switch($modId){
		case 0:
			return new mod_HTML();
			break;
		case 1:
			return new mod_bbcode();
			break;
		case 2:
			return new mod_like();
			break;
		case 3:
			return new mod_controlPanel();
			break;
		case 4:
			return new mod_sitemap();
			break;
		case 5:
			return new mod_calendar();
			break;
		case 6:
			return new mod_forumsActivity();
			break;
		case 7:
			return new mod_uploader();
			break;
		case 8:
			return new mod_gallery();
			break;
		case 9:
			return new mod_filetree();
			break;
		case 10:
			return new mod_news();
			break;
		case 11:
			return new mod_video();
			break;
		case 12:
			return new mod_simpleScript();
			break;
		case 13:
			return new mod_blog();
			break;
		}
	// if not valid modId
	return false;
	}
//delete module
function deleteMod($pageId,$instanceId=-1){
	mysql_query("UPDATE `modules` SET deleted = '1' WHERE `pageId` = '".mysql_real_escape_string($pageId)."' ".($instanceId==-1?"":("AND `instanceId` = '".mysql_real_escape_string($instanceId)."'")))or die(mysql_error());
	mysql_query("UPDATE `moduleProps` SET deleted = '1' WHERE `pageId` = '".mysql_real_escape_string($pageId)."' ".($instanceId==-1?"":("AND `instanceId` = '".mysql_real_escape_string($instanceId)."'")))or die(mysql_error());
	}
?>