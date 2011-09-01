<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/*Uploader Module
* version 0.1
* Developed by Matt Howard
*/

class mod_uploader {
	
	public $title = 'Uploader';
	public $description = 'upload files to a location for a purpose';
	public $path = 'mod_uploader';

	public function render($properties) {
		
		if($properties["photos"]=="on"&&!isset($_POST["step1"])){
		$out = '<h1>Upload Pictures</h1>
<p><h2>Step 1: Pick Album</h2></p>
<form action="" method="post">
<input type="hidden" name="step1" value="done" />
<select id="albumName" name="albumName" onchange="if(this.value==\'new\')$(\'#newAlbum\').show(); else $(\'#newAlbum\').hide();">
<option value=""></option>
<option value="new">Create a New Album</option>
';
$q = mysql_query("SELECT * FROM `photos` WHERE `type` = '1' ORDER BY `year` DESC, `title` DESC");
while($row = mysql_fetch_array($q)){
	$out .= '<option value="'.$row['title'].'_'.$row['year'].'">'.$row['title'].'-'.$row['year'].'</option>';
}
$out .= '
</select>
<div id="newAlbum" style="display:none;">
<p><label style="width:175px;display:inline-block;">Album Name:</label><input name="newName" id="newName" /></p>
<p><label style="width:175px;display:inline-block;">Description:</label><input name="description" id="description" /></p>
<p><label style="width:175px;display:inline-block;">Year:</label><input name="year" id="year" /></p>
</div>
<br/><input type="submit" value="Select"></form>';
		return $out;
	}
		
		return (($properties["photos"]=="on")?'
<p><h2>Step 2: Select Pictures and Upload</h2></p>':"").'
<applet name="jumpLoaderApplet"
	code="jmaster.jumploader.app.JumpLoaderApplet.class"
	archive="/omni/modules/mod_uploader/jumploader_z.jar"
	width="100%"
	height="500"
	mayscript>
    	<param name="uc_imageEditorEnabled" value="false"/>
		<param name="uc_uploadUrl" value="'.$properties["handler"].
		(($properties["photos"]=="on")?"?albumName=".urlencode($_POST["albumName"])."&description=".urlencode($_POST["description"])."&year=".urlencode($_POST["year"])."&newName=".urlencode($_POST["newName"]):"")
		.'"/>
		<param name="uc_partitionLength" value="1000000"/>
You need a java plugin for your browser to use this uploader.
</applet>
';
return $out;
	}

	public function renderEdit($properties) {
		return "Handler: <input class='editBox' id='textarea_".$properties["pageId"]."_".$properties["instanceId"]."' value='".$properties["handler"]."'><p>
Use for photo gallery: <input type='checkbox' ".($properties["photos"]=="on"?"checked":"")." id='photos'>
		<button onclick=\"saveMod(".$properties["pageId"].",".$properties["instanceId"].",{handler:$('#textarea_".$properties["pageId"]."_".$properties["instanceId"]."').val(),photos:($('#photos:checked').val()==null)?'off':'on'})\">Save</button>";
	}

	public function edit($properties) {
		mysql_query("UPDATE `moduleProps` SET `propValue` = '".mysql_real_escape_string($properties["handler"])."' WHERE `pageId` = '".mysql_real_escape_string($properties["pageId"])."' AND `instanceId` = '".mysql_real_escape_string($properties["instanceId"])."' AND `propName` = 'handler';") or die(mysql_error());
		mysql_query("UPDATE `moduleProps` SET `propValue`='".mysql_real_escape_string($properties["photos"])."' WHERE `pageId` = '".mysql_real_escape_string($properties["pageId"])."' AND `instanceId` = '".mysql_real_escape_string($properties["instanceId"])."' AND `propName` = 'photos'") or die(mysql_error());
	}
	
	var $sqlNames, $sqlDefaults;
	
	public function setup() {
		$this->sqlNames = array("handler","photos");
		$this->sqlDefaults = array("","off");
	}
}
