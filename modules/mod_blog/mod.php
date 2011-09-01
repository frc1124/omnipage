<?PHP
/*Blog Module
* version 0.1
* Developed by Matt Howard, Phil Lopreiato
*/

class mod_blog {
	
	public $title = 'Blog';
	public $description = 'allows editors to post in a blog format';
	public $path = 'mod_blog';

	public function render($properties) {
		global $user;
		$permission = userPermissions(1);
		$output = "";
				//add and edit post
				if($permission&&isset($_POST["addPost"])){
					if(isset($_POST["postId"])){
						mysql_query("UPDATE `blogPosts` SET `title` = '".mysql_real_escape_string($_POST["title"])."', `text` = '".mysql_real_escape_string($_POST["text"])."' WHERE `time` = '".mysql_real_escape_string($_POST["postId"])."'");
						$output .= "<div style=\"color:red;font-weight:bold;\">Post edited</div>";
						}
					else{
						mysql_query("INSERT INTO `blogPosts` VALUES ('".$properties["blogId"]."','".mysql_real_escape_string($user->data["username_clean"])."','".time()."','".mysql_real_escape_string($_POST["title"])."','".mysql_real_escape_string($_POST["text"])."')") or die(mysql_error());
						$output .= "<div style=\"color:red;font-weight:bold;\">Post added</div>";
					}
					}
				//delete post
				if($permission&&isset($_GET["deletePost"])){
					$query = mysql_query("DELETE FROM `blogPosts` WHERE `time` = '".mysql_real_escape_string($_GET["deletePost"])."' AND `blogId` = '".$properties["blogId"]."'") or die(mysql_error());
					if(mysql_affected_rows()==1)
						$output .= "<div style=\"color:red;font-weight:bold;\">Post deleted</div>";
					else
						$output .= "<div style=\"color:red;font-weight:bold;\">Post not found</div>";
					}
				//show blog
				$query = mysql_query("SELECT * FROM `blogPosts` WHERE `blogId` = '".mysql_real_escape_string($properties["blogId"])."' ORDER BY `time` DESC") or die(mysql_error());
				while($row = mysql_fetch_array($query)){
					$output .= parseSkin(array("title"=>$row["title"],"text"=>($properties["format"]==0?parseBB($row["text"]):$row["text"]),"userName"=>$row["userName"],"date"=>date("l F j, Y h:i A",$row["time"]),"rawTime"=>$row["time"]),"blogPost",array("ISEDITABLE"=>$permission));
					}
				//show "add post" form
				if($permission){
					if(isset($_GET["editPost"])){
						$query = mysql_query("SELECT * FROM `blogPosts` WHERE `blogId` = '".mysql_real_escape_string($properties["blogId"])."' AND `time` = '".mysql_real_escape_string($_GET["editPost"])."'") or die(mysql_error());
						$row = mysql_fetch_array($query);
						}
					else{
						$row = array();
						}
					$output .= "<h1>".(isset($_GET["editPost"])?"Edit":"Add")." a post</h1>";
					$output .= "<form method=\"post\"><p>Title: <input type=\"text\" name=\"title\" style=\"width:400px;\" value=\"".$row["title"]."\"></p>";
					$output .= "<p><textarea name=\"text\" style=\"width:100%;height:200px;font:1em Arial, Helvetica, sans-serif normal;\">".$row["text"]."</textarea></p>";
					$output .= isset($_GET["editPost"])?("<input type=\"hidden\" name=\"postId\" value=\"".$row["time"]."\">"):"";
					$output .= "<p><input type=\"submit\" value=\"Post\" name=\"addPost\"></p></form>";
					}
		
		return $output;
	}

	public function renderEdit($properties) {
		return "<p>Blog Id: <input type=\"text\" id=\"blogId_".$properties["pageId"]."_".$properties["instanceId"]."\"></p>
		<p>The blog id should be an integer unique to this specific blog.</p>
		<button onclick=\"saveMod(".$properties["pageId"].",".$properties["instanceId"].",{blogId:$('#blogId".$properties["pageId"]."_".$properties["instanceId"]."').val()})\">Save</button>
		";
	}

	public function edit($properties) {
		//mysql_query("UPDATE `moduleProps` SET `propValue`='".mysql_real_escape_string($properties["code"])."' WHERE `pageId` = '".mysql_real_escape_string($properties["pageId"])."' AND `instanceId` = '".mysql_real_escape_string($properties["instanceId"])."' AND `propName` = 'code'") or die(mysql_error());
		setVariables(mysql_real_escape_string($properties['pageId']),mysql_real_escape_string($properties['instanceId']),array('code'=>$properties['code']));
	}
	
	var $sqlNames, $sqlDefaults;
	
	public function setup() {
		//format - 0=bbcode 1=html
		$this->sqlNames = array("blogId","format","type");
		$this->sqlDefaults = array("0","0","0");
	}
}
