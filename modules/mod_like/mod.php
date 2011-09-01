<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* Like button module
*  version 0.1
*  developed by Phil Lopreiato
*  This module contains the Facebook 'like' button and a 'sharethis' button for email, facebook, twitter, etc.
*/
class mod_like {

	public $title = 'Like and ShareThis';
	public $description = 'allows use of social networking and Facebook';
	public $path = 'mod_like';

	public function render($properties){
	
	return '
	<table style="boarder:0;"><tr><td>
	<iframe src="http://www.facebook.com/plugins/like.php?href='.urlencode('http://www.uberbots.org'.$_SERVER['REQUEST_URI']).'&amp;
	layout=standard&amp;
	show_faces=false&amp;
	width=400&amp;
	action=like&amp;
	colorscheme=light&amp;
	height=30" scrolling="no" frameborder="0" style="border:none;
	overflow:hidden;
	width:400px;
	height:30px;"
	allowTransparency="true">
	</iframe>
	</td>
	
	<td style="vertical-align:top;">
	<!-- AddThis Button BEGIN -->
<a class="addthis_button" addthis:url="http://example.com" href="http://www.addthis.com/bookmark.php?v=250&amp;username=xa-4c2ea0ae506c9822"><img src="http://s7.addthis.com/static/btn/v2/lg-share-en.gif" width="125" height="16" alt="Bookmark and Share" style="border:0"/></a><script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#username=xa-4c2ea0ae506c9822"></script>
<!-- AddThis Button END -->
	</td></tr></table>
	';
	}

	public function renderEdit($properties){
		return "There are no editing options for the like module.";
	}

	public function edit($properties) {
	
	}
	
	var $sqlNames, $sqlDefaults;
	
	public function setup() {
		$this->sqlNames = array();
		$this->sqlDefaults = array();
	}
}
?>