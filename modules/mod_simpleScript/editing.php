<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

	//include "../../includes/common.php";
switch($_GET['mode']){
	case "saveVars":
		echo saveVars($_GET);
		break;
}

function saveVars($props){
	$pageId = $props['pageId'];
	$instanceId = $props['instanceId'];
	$out = "";
	$out = array();
	foreach($props as $k => $v){
		if($k != 'pageId' && $k != 'instanceId' && $k != 'mode')
			$out[$k] = $v;
	}
	setVariables($pageId,$instanceId,$out);
}

function getVarInfo($type){
		global $root_path;
		if($type != "0"){
			$handle = fopen($root_path."/modules/mod_simpleScript/".$type.".csv", "r");
			$count = 1;
			$out = array();
			$i = 0;
			while (($data = fgetcsv($handle, 100, ",")) != FALSE) {
				if($i != 0){
					$out[$data[0]] = $data;
				}					
				$i++;
			}
			fclose($handle);
		}
		return $out;
	}
	
/*function getNames($type){
		global $currentSkin, $root_path;
		if($type != "0"){
			$handle = fopen($root_path."/modules/mod_simpleScript/".$type.".csv", "r");
			$count = 1;
			$out = array();
			$i = 0;
			while (($data = fgetcsv($handle, 100, ",")) != FALSE) {
				if($i != 0){
						$out[$data[0]] = $data[1];
				}					
				//$out[$data[0]] = $t==2?$data[2]:$data[1];
				//$scriptData[];
				$i++;
			}
			fclose($handle);
		}
		return $out;
}

function getTypes($type){
	//get input types: 0 is textbox, 1 is textarea
	global $currentSkin,$root_path;
		if($properties['type'] != "0"){
			$handle = fopen($root_path."/modules/mod_simpleScript/".$type.".csv", "r");
			$count = 1;
			$out = array();
			while (($data = fgetcsv($handle, 100, ",")) != FALSE) {
				if($data[0] != "//name"){
						$out[$data[0]] = $data[3];
				}					
				//$out[$data[0]] = $t==2?$data[2]:$data[1];
				//$scriptData[];
			}
			fclose($handle);
		}
		return $out;
}

function getDefs($type){
		global $currentSkin;
		if($properties['type'] != "0"){
			$handle = fopen("/home1/uberbots/public_html/omni/modules/mod_simpleScript/".$type.".csv", "r");
			$count = 1;
			$out = array();
			while (($data = fgetcsv($handle, 100, ",")) != FALSE) {
				if($data[0] != "//name"){
						$out[$data[0]] = $data[2];
				}					
				//$out[$data[0]] = $t==2?$data[2]:$data[1];
				//$scriptData[];
			}
			fclose($handle);
		}
		return $out;
}*/


function getValues($pageId, $instanceId, $type){
			/*$scriptVars = getNames($type);
			$types = getTypes($type);*/
			$scriptVars = getVarInfo($type);
			unset($row);
			$vars = "";
			$map = "{";
			foreach($scriptVars as $k => $v){ //make row for var & value
				$q = mysql_query("SELECT * FROM `moduleProps` WHERE pageId = '".mysql_real_escape_string($pageId)."' AND instanceId = '".mysql_real_escape_string($instanceId)."' AND propName = '".mysql_real_escape_string($k)."'") or die(mysql_error());
				$row = mysql_fetch_array($q);
				if(!$row)
					$row = array();
				
				if($v[3] == 0){
					$vars .= "<label for='".$k."' style='display:inline-block;width:150px;'>".$v[1]."</label><input type='text' name='".$k."' id='".$k."' value=\"".$row['propValue']."\" /><br/>";
				}if($v[3] == 1){
					$vars .= "<label for='".$k."' style='display:inline-block;width:150px;'>".$v[1]."</label><textarea style='height:4em;width:100%' name='".$k."' id='".$k."'>".$row['propValue']."</textarea><br/>";
				}
				$map .= $k.":$('#".$k."').attr('value'),";
			}
			$map .= "}";
			$repl = array("map"=>$map,"url"=>$url->fullUrl,"scriptType"=>$type, "vars"=>$vars, "pageId"=>$pageId, "instanceId"=>$instanceId);
			return parseSkin($repl,"simpleScriptEditVars");

}
?>