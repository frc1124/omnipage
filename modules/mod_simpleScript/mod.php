<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/*Simple Script Module
* version 0.1
* Developed by Phil Lopreiato
*/


class mod_simpleScript {
	
	public $title = 'Scripts';
	public $description = 'Allows for the use of a predefined script on a page.';
	public $path = 'mod_simpleScript';
	
	private $selectOutput = "";
	
	public function render($properties) {

		if($properties['type']=="0") //if no type is set, tell user to set type
			return "Edit module to set type.";
		 
		//return final skin output
		return parseSkin($properties,"simpleScript_".$properties['type'],$properties);
	}

	public function renderEdit($properties) {
	
		//return parseSkin(array('pageId'=>$properties['pageId'],'instanceId'=>$properties['instanceId']),'simpleScriptEdit',array());
		include "editing.php";
		global $currentSkin,$root_path;
			if($properties['type']=="0"){ //if no type, mod just added, show types
				$types = "";
				$dir = $root_path."/modules/mod_simpleScript"; //set directory to current skin
						if(is_dir($dir)){ //is it is a directory (safety check!)
							if($handle = opendir($dir)){ //open directory (safety check #2!)
								while (false != ($file = readdir($handle))) { //list directory contents
									if (strpos($file, '.csv',1)) { //only output *.csv files
										$name = basename($dir."/".$file,".csv"); //get file base name (everything but .csv)
										if($properties['type']==$name) //if current type, make this selected
											$isSelected = "SELECTED";
										else
											$isSelected = "";
										$types .= "<option value=\"".$name."\">".$name."</option>"; //output option
									}
								}
								$output = parseSkin(array("types"=>$types, "pageId"=>$properties['pageId'],"instanceId"=>$properties['instanceId']),'simpleScriptTypes',array()); //output skin for editing types
								closedir($handle); //close handle
							}else{
								$output = "no handle";
							}
						}else{
							$output = "not a directory";
						}
					}else{ //else, show variables for editing
						$output = getValues($properties['pageId'],$properties['instanceId'],$properties['type']);
					}
					return $output;	
			
			/*unset($ifs);
			$ifs = array();
			unset($repl);
			$vars = "";
			unset($k);
			unset($v);
			$scriptVars = array();
			
			
			
			$scriptVars = $this->getVars($properties, $currentSkin,0);
			unset($row);
			
			foreach($scriptVars as $k => $v){ //make row for var & value
				$q = mysql_query("SELECT * FROM `moduleProps` WHERE pageId = '".mysql_real_escape_string($properties['pageId'])."' AND instanceId = '".mysql_real_escape_string($properties['instanceId'])."' AND propName = '".mysql_real_escape_string($k)."'") or die(mysql_error());
				$row = mysql_fetch_array($q) or die(mysql_error());
				$vars .= "<tr><td>".$v."</td><td><input type='text' name=\"ss_".$k."\" id=\"ss_".$k."\" value=\"".$row['propValue']."\"</td></tr>";
			}
			$repl = array("url"=>$url->fullUrl,"scriptType"=>$properties['type'], "vars"=>$vars, "pageId"=>$properties['pageId'], "instanceId"=>$properties['instanceId']);*/
			
			//return parseSkin($repl,"simpleScriptEditVars",$ifs);
		
	}

	public function edit($properties) {
		include "editing.php";
		$pId = $properties['pageId'];
		$iId = $properties['instanceId'];
		
		//global $currentSkin;
		//mysql_query("UPDATE moduleProps SET propValue = '".$properties['type']."' WHERE pageId = '".$properties['pageId']."' AND instanceId = '".$properties['instanceId']."' AND propName = 'type'");
		//setVariables(mysql_real_escape_string($properties['pageId']),mysql_real_escape_string($properties['instanceId']),array('type'=>$properties['type']));
		//$this->checkVars($properties);
		if($properties['type']=='0'){
			$names = getNames($properties['type']);
			$defs = getDefs($properties['type']);
			foreach($names as $k => $v){
				mysql_query("INSERT INTO moduleProps (instanceId, pageId, propName, propValue, deleted) VALUES ('".$properties['instanceId']."','".$properties['pageId']."','".$k."','".$defs[$k]."','0')");
			}
		}
		setVariables(mysql_real_escape_string($pId),mysql_real_escape_string($iId),$properties);
	}
	
	var $sqlNames, $sqlDefaults;
	
	public function setup() {
		$this->sqlNames = array("type");
		$this->sqlDefaults = array("0");
	}
	
	private function getVars($properties, $currentSkin,$t){
			if($properties['type'] != "0"){
				$handle = fopen("/home1/uberbots/public_html/omni/skins/".$currentSkin."/".$properties['type'].".csv", "r");
				$count = 1;
				$out = array();
				while (($data = fgetcsv($handle, 100, ",")) != FALSE) {
					if($data[0] != "name" || $data[1] != "display name" || $data[2] != "default"){
						if($t == 2){
							$out[$data[0]] = $data[2];
						}else{
							$out[$data[0]] = $data[1];
						}
					}					
					//$out[$data[0]] = $t==2?$data[2]:$data[1];
					//$scriptData[];
				}
				fclose($handle);
			}
			return $out;
	}
	
	private function checkVars($properties){
		global $currentSkin;
		$vars = array();
		$vars = $this->getVars($properties,$currentSkin,2);
		foreach($vars as $k => $v){
			$q = mysql_query("SELECT * FROM `moduleProps` WHERE pageId = '".mysql_real_escape_string($properties['pageId'])."' AND instanceId = '".mysql_real_escape_string($properties['instanceId'])."' AND propName = '".mysql_real_escape_string($k)."'");
			$r = mysql_num_rows($q);
			if($r == 0){
				mysql_query("INSERT INTO `moduleProps` (pageId, instanceId, propName, propValue) VALUES ('".mysql_real_escape_string($properties['pageId'])."','".mysql_real_escape_string($properties['instanceId'])."','".mysql_real_escape_string($k)."','".mysql_real_escape_string($v)."')") or die(mysql_error());
			}
		}
	}

}
