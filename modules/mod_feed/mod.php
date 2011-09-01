<?PHP
/* Feed Module
*  version 0.1
*  developed by  Phil Lopreiato
*  This module will create a feed of various things on the website
*/

class mod_feed{
	public $title = 'Feed Viewer';
	public $description = 'Creates a feed-type list of content on the site';
	public $path = 'mod_feed';	
	
	public function render($properties){
		
	}
	
	public function renderEdit($properties){
		
	}
	
	public function edit($properties){
		
	}
	
	public function setup() {
		$this->sqlNames = array("contentTypes");
		$this->sqlDefaults = array("none");
	}
}


?>