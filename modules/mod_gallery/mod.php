<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* Gallery Module
* version 0.1
* Developed by Matt Howard, Phil Lopreiato
*/

class mod_gallery {
	
	public $title = 'Picture Gallery';
	public $description = 'Displays pictures in a gallery format.';
	public $path = 'mod_gallery';

	public function render($properties) {
	
		$output = "
<style type=\"text/css\" scoped>
.thumbnail {display:inline-block; width:119px; margin:5px;text-align:center;color:#003399;cursor:pointer;height:136px;}
#leftPanel {float:left;width:596px;border:#003399 solid 2px;padding:5px;height:610px;overflow-y:scroll;display:inline-block;}
#rightPanel {width:419px;border:#003399 solid 2px;padding:5px;height:625px;display:inline-block;margin-left:5px;text-align:center;}
#rightPanelLiner {width:419px;height:610px;display:table-cell;vertical-align:middle;padding-bottom:25px;text-align:center;}
</style>
<script type='text/javascript'>

function hideRight(){
	$('#rightPanel').fadeOut(500,function(){
		$('#leftPanel').animate({width:'596px'});
		});
	}

function showAlbum(id){
	$.post('/omni/ajax/getAlbum.php',{parentId:id},
		   function(data){
			   $('#leftPanel .thumbnail').each(function(){
				//$(this).css({left:$(this).offset().left,top:$(this).offset().top});
				})/*.css({position:'absolute'})*/.animate({/*left:$('#leftPanel').offset().left+231,top:$('#leftPanel').offset().top+217,*/opacity:0},500,
				function(){
					$('#leftPanel').html(data);
					$('#leftPanel .thumbnail').hide().fadeIn();
					})
			   });
	}

function next(){
	$(currentPic).next().click();
	}
function prev(){
	$(currentPic).prev().click();
	}

function showPicture(filepath,caption,curr){
	currentPic = curr;
	$('#rightPanelLiner').html('<p align=\"left\"><a href=\"javascript:void(0)\" onclick=\"prev();\">Previous</a><span style=\"float:right;\"><a href=\"javascript:void(0)\" onclick=\"next();\">Next</a></span></p><img src=\"http://www.uberbots.org/omni/modules/mod_gallery/imageresize.php?type=resize&amp;width=409&amp;src='+filepath+'\"><div style=\"text-align:right;padding-bottom:1em;\"><a href=\"/omni/media/photos/'+filepath+'\">Full Size</a> <a href=\"javascript:void(0)\" onclick=\"hideRight();\">Collapse Picture</a></div>'+caption+'<p>BBCode: <input value=\"[img]http://uberbots.org/omni/modules/mod_gallery/imageresize.php?type=resize&amp;src='+filepath+'[/img]\" readonly style=\"width:250px;\"></p>');
	if($('#leftPanel').css('width')!='144px'){
		$('#leftPanel').animate({width:'144px'},500,function(){
			$('#rightPanel').fadeIn(500);
			});
	}
	}
$(document).ready(function(){
	$('#rightPanel').hide();
	});
</script>
<div class='ui-corner-all' id='leftPanel'>";
		
		//return all pictures in starting album
		$output .= file_get_contents("http://uberbots.org/omni/ajax/getAlbum.php?parentId=0");
		
		$output .= "</div><div id='rightPanel' class='ui-corner-all'><div id='rightPanelLiner'></div></div>";
		
		return $output;
		
	}
	public function renderEdit($properties) {
	}

	public function edit($properties) {
	}
	
	var $sqlNames, $sqlDefaults;
	
	public function setup() {
		$this->sqlNames = array();
		$this->sqlDefaults = array();
	}
}
