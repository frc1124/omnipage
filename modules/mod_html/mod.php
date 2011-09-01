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

class mod_HTML {
	
	public $title = 'HTML';
	public $description = 'uses basic HTML markup';
	public $path = 'mod_html';

	public function render($properties) {
		return $properties["code"];
	}

	public function renderEdit($properties) {
		global $currentSkin;
		return 
"<textarea style='width:550px;height:200px;' name='textarea_".$properties["pageId"]."_".$properties["instanceId"]."' id='textarea_".$properties["pageId"]."_".$properties["instanceId"]."'>".htmlentities($properties["code"])."</textarea><p>
<button onclick=\"saveMod(".$properties["pageId"].",".$properties["instanceId"].",{code:$('#textarea_".$properties["pageId"]."_".$properties["instanceId"]."').val()})\">Save</button>
<script type='text/javascript'>
 $(function() {
 $('#textarea_".$properties["pageId"]."_".$properties["instanceId"]."').htmlarea({
	loaded: function(){
		this.showHTMLView();
	}
 });
 });
</script>

		";
	}

	public function edit($properties) {
		//mysql_query("UPDATE `moduleProps` SET `propValue`='".mysql_real_escape_string($properties["code"])."' WHERE `pageId` = '".mysql_real_escape_string($properties["pageId"])."' AND `instanceId` = '".mysql_real_escape_string($properties["instanceId"])."' AND `propName` = 'code'") or die(mysql_error());
		setVariables(mysql_real_escape_string($properties['pageId']),mysql_real_escape_string($properties['instanceId']),array('code'=>$properties['code']));
	}
	
	var $sqlNames, $sqlDefaults;
	
	public function setup() {
		$this->sqlNames = array("code");
		$this->sqlDefaults = array("HTML markup");
	}
}
