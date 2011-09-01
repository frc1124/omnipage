<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/*Forums Activity Module
* version 0.1
* Developed by Matt Howard, Phil Lopreiato
*/

class mod_forumsActivity {
	
	public $title = 'Forums Activity';
	public $description = 'shows recent forums posts';
	public $path = 'mod_forumsActivity';

	public function render($properties) {
		global $db,$user,$auth;;
		
		$auth->acl($user->data);
		
		$items = "";
		$query = $db->sql_query("SELECT `phpbb_topics`.`topic_title` as `title`, `phpbb_topics`.`topic_last_poster_name` as `username`, `phpbb_posts`.`post_text` as `content`, `phpbb_posts`.`post_id` as `postId`, `phpbb_topics`.`topic_last_post_time` as `date`, `phpbb_topics`.`forum_id` as `forumId`, `phpbb_posts`.`post_id` as `postId`
								FROM `phpbb_topics`
								LEFT JOIN phpbb_posts ON `phpbb_posts`.`post_id` = `phpbb_topics`.`topic_last_post_id` 
								WHERE `phpbb_posts`.`post_approved` = 1 AND `phpbb_topics`.`topic_moved_id`=0
								ORDER BY `phpbb_topics`.`topic_last_post_time` DESC");
		for($i=0;$i<6;$row = $db->sql_fetchrow($query)){
			//check if user has permission to view topic
			if($auth->acl_get('f_read', $row["forumId"])){
				$row["content"]=substr(preg_replace("/(\[(.*?)\:(.*?)\]|<.+>)/", "",$row["content"]),0,160)."...";
				$row["date"]=date("m/d g:i a",$row["date"]);
				$items .= parseSkin($row,"forumsActivityLine");
				$i++;
				}
			}
		return parseSkin(array("items"=>$items),"forumsActivity");
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

?>