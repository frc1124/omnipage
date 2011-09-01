<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* Sitemap Module
*  version 0.1
*  developed by Matt Howard, Phil Lopreiato
*  This module will output a sitemap
*/


class mod_sitemap {

	private function listChildren($parent, $level, $url){
		
		$query = mysql_query("SELECT * FROM `pages` WHERE `deleted` = '0' AND `parentId` = '$parent' ORDER BY `order` ASC, `title` ASC") or die(mysql_error());
		while($row = mysql_fetch_array($query)){
			if($row["id"]!=0 && userPermissions(0,$row["id"])){
			$title = str_replace(" ","_",$row['title']);
            $this->output .= "<li><a href='http://uberbots.org/o/".$url.$title."'>".$row['title']."</a>";
            $this->output .= "<ul>";
            $this->listChildren($row["id"],$level+1,$url.$title."/");
			
            $this->output .= "</ul>";
			$this->output .= "</li>";
		   }
		}
	}

	public $title = 'Sitemap';
	public $description = 'Creates a sitemap';
	public $path = 'mod_sitemap';

	public function render($properties){
	$this->output = "
<style type=\"text/css\" scoped>
	#sitemap ul {margin-left:30px;}
	#sitemap li {margin-bottom:4px;}
</style>
<div id='sitemap'>
<ul>
";
	$url = "";
	
	$this->listChildren(0,1,$url);
	
	$this->output .= "</ul></div>";
	return $this->output;
	

	}

	public function renderEdit($properties){
	return "This module has no editable properties.";
	}

	public function edit($properties) {
	return "This module has no editable properties.";
	}
	
	var $sqlNames, $sqlDefaults;
	
	public function setup() {
		$this->sqlNames = array();
		$this->sqlDefaults = array();
	}
	
	
}
?>