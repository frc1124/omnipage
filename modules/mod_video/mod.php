<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/*Video Module
* version 0.1
* Developed by Matt Howard
*/

class mod_video
	{
	
	public $title = 'Video';
	public $description = 'Displays videos in either a single video or gallery format';
	public $path = 'mod_video';

	public function render($properties) {
		switch ($properties['type']){
			case 0:
				$thumbs = "";
				$query = mysql_query("SELECT * FROM `videos` ORDER BY `time` DESC");
				while($row = mysql_fetch_array($query)){
					$thumbs .= parseSkin($row,"videoThumb");
					}
				$gal = $properties['type']==0?true:false;
				$output = parseSkin(array("thumbs"=>$thumbs,"view"=>$_GET["view"]),"videos", array("GALLERY"=>$gal,"VIEW"=>isset($_GET["view"])));
				return $output;
				break;
			case 1:
				
				break;
		}
	}

	public function renderEdit($properties) {

	}

	public function edit($properties) {
		mysql_query("UPDATE `moduleProps` SET `propValue`='".mysql_real_escape_string($properties["type"])."' WHERE `pageId` = '".mysql_real_escape_string($properties["pageId"])."' AND `instanceId` = '".mysql_real_escape_string($properties["instanceId"])."' AND `propName` = 'code'") or die(mysql_error());
	}
	
	var $sqlNames, $sqlDefaults;
	
	public function setup() {
		$this->sqlNames = array("type", "video");
		$this->sqlDefaults = array("0", "");
	}
}
