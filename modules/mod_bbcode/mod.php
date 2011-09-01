<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/*HTML Module
* version 0.1
* Developed by Matt Howard, Phil Lopreiato
*/

class mod_bbcode {
	
	public $title = 'BBCode';
	public $description = 'uses bulletin board markup';
	public $path = 'mod_bbcode';

	public function render($properties) {
		return parseBB($properties["code"]);
	}

	public function renderEdit($properties) {
		return "<textarea class='editBox' style='width:100%;height:100px;' id='textarea_".$properties["pageId"]."_".$properties["instanceId"]."'>".$properties["code"]."</textarea><p>
		<button onclick=\"saveMod(".$properties["pageId"].",".$properties["instanceId"].",{code:$('#textarea_".$properties["pageId"]."_".$properties["instanceId"]."').val()})\">Save</button>
		";
	}

	public function edit($properties) {
		setVariables($properties['pageId'],$properties['instanceId'],array("code"=>$properties['code']));
		//mysql_query("UPDATE `moduleProps` SET `propValue`='".mysql_real_escape_string($properties["code"])."' WHERE `pageId` = '".mysql_real_escape_string($properties["pageId"])."' AND `instanceId` = '".mysql_real_escape_string($properties["instanceId"])."' AND `propName` = 'code'") or die(mysql_error());
	}
	
	var $sqlNames, $sqlDefaults;
	
	public function setup() {
		$this->sqlNames = array("code");
		$this->sqlDefaults = array("BBCode markup");
	}
}
