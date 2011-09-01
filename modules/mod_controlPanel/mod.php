<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************

   ******************************************************************************************
   * Control Panel Module                                                                   * 
   * Allows for administrative control of stuff on the site                                 *
   * version 0.1                                                                            *
   * Developed by Matt Howard, Phil Lopreiato                                               *
   ******************************************************************************************/


class mod_controlPanel {
	
	public $title = 'Control Panel';
	public $description = 'Allows administrators to change stuff on the site';
	public $path = 'mod_controlPanel';
	
	private $selectOutput = "";
	
	public function render($properties) {

		
		if(userPermissions(1) && $properties['pageId'] == 2){
		global $page, $root_path;
		
		//select form that was filled out and execute appropriate PHP code
		switch($_POST['mode']){
			
			//add skin in SQL
			case "addSkin":
				$row = mysql_fetch_array(mysql_query("SELECT * FROM `skins` ORDER BY `id` DESC"))or die($message =  mysql_error());
				$add = mysql_query("INSERT INTO `skins` (id,name,path,parent) VALUES ('".mysql_real_escape_string($row['id']+1)."','".mysql_real_escape_string($_POST['skinName'])."','".mysql_real_escape_string($_POST['skinPath'])."','".mysql_real_escape_string($_POST['skinParent'])."')");
				$message = $add?"Skin added sucessfully":mysql_error();
				logEntry("Added skin '".mysql_real_escape_string($_POST['skinName'])."'");
				break;
			
			//add page in SQL
			case "addPage":
				//check that a parent page has been selected
				if($_POST['parent'] == '-1'){
					$message = "Please select a parent page.";
				}else{
					//check for valid page title
					if(!$this->checkTitle($_POST['pageTitle']))
							$message = "Page title is required. Must be 1-20 characters long containing only upper or lowercase letters, spaces, and numbers.";
					else{
						//check is page already exists
						if(mysql_fetch_array(mysql_query("SELECT * FROM `pages` WHERE `parentId` = '".mysql_real_escape_string($_POST['parent'])."' AND `title` LIKE '".mysql_escape_string($_POST['pageTitle'])."'",$GLOBALS["mySQLLink"])))
							$message = "Page title already exists";
						else {
							if(is_numeric($_POST['menuOrder']) && $_POST['menuOrder'] >= 0 && $_POST['menuOrder'] <= 100){
								//find page id
								$query = mysql_query("SELECT * FROM `pages` ORDER BY `id` DESC",$GLOBALS["mySQLLink"]);
								$row = mysql_fetch_array($query);
								//insert row with page info into SQL
								$query = mysql_query("INSERT INTO  `uberbots_omni`.`pages` (`id` ,`parentId` ,`title` ,`order`,`inheritPermissions` ,`private`,`hide`,`redirect`) VALUES ('".($row['id']+1)."',  '".mysql_real_escape_string($_POST['parent'])."',  '".mysql_real_escape_string($_POST['pageTitle'])."','".(mysql_real_escape_string($_POST['menuOrder']))."','".($_POST["inherentBox"]=="on"?1:0)."','".($_POST["privateBox"]=="on"?1:0)."','".($_POST['hideBox']=='on'?1:0)."','".mysql_real_escape_string($_POST['redirect'])."');",$GLOBALS["mySQLLink"]);
								//add log entry for added page
								logEntry("added page ".$_POST['pageTitle']." id ".($row['id']+1)." with parent ".$_POST['parent']);
								$message = $query ? "Page addition successful!" : "Error: ".mysql_error();
							}else{
								$message = "Menu Order is invalid. Order must be numeric and between 0 and 100.";
							}
						}
					}
				}
			break;
				
			//delete a page in SQL
			case "delPage":
				//check that page has been selected
				if($_POST['page'] == '-1'){
					$message = "Please select a page to delete";
				}else{
					//check confirmation (not really needed, javascript does this already)
					if($_POST['confirmDel']=="on"){
						$name = mysql_query("SELECT * FROM `pages` WHERE `id` = '".mysql_real_escape_string($_POST['page'])."'",$GLOBALS["mySQLLink"]);
						$row = mysql_fetch_array($name);
						//delete pages in SQL
						$query = mysql_query("UPDATE `pages` SET `deleted` = '1' WHERE `id` = '".mysql_real_escape_string($_POST['page'])."'",$GLOBALS["mySQLLink"]);
						//delete modules assosciated with that page
						deleteMod($_POST['page']);
						//log the deletion
						logEntry("deleted page id ".$_POST['page']." titled ".$row["title"]);
						$message = $query?"Deleted sucessfully!":mysql_error();
						}
						else{
						$message="You need to confirm the deletion.";
						}
				}
			break;
				
			//modify a page in SQL
			case "editPage":
				$message = "";
				$pageId = $_POST['pageSelect'];
				
				//check if user has entered title 
				if($_POST['newTitle'] != ""){
					$newTitle = $_POST['newTitle'];
					//update title in SQL
					$query = mysql_query("UPDATE pages SET title = '".mysql_real_escape_string($newTitle)."' WHERE id = '".mysql_real_escape_string($pageId)."'",$GLOBALS["mySQLLink"]);
					$message .= $query?"Title Updated Sucessfully <br/>":mysql_error();
				}
				
				//check if user has entered new parent page
				if($_POST['newParent']!= -1){
					$newParent = $_POST['newParent'];
					//update parent in SQL
					$query = mysql_query("UPDATE pages SET parentId = '".mysql_real_escape_string($newParent)."' WHERE id = '".mysql_real_escape_string($pageId)."'",$GLOBALS["mySQLLink"]);
					$message .= $query?"Parent Updated Sucussfully <br/>":mysql_error();
				}
				
				//check for new menu order
				if(isset($_POST['newMenuOrder'])){
					$newOrder = mysql_real_escape_string($_POST['newMenuOrder']);
					$q = mysql_query("UPDATE pages SET order = '".$newOrder."' WHERE id = '".mysql_real_escape_string($pageId)."'",$GLOBALS['mySQLLink']);
				}
				
				//update permissions
				$query = mysql_query("UPDATE pages SET inheritPermissions = '".($_POST["newInherent"]=="on"?1:0)."' WHERE id = '".mysql_real_escape_string($pageId)."'",$GLOBALS["mySQLLink"]);
				$message .= $query?"Inherent Updated Sucessfully <br/>":mysql_error();
				
				//update private status
				$query = mysql_query("UPDATE pages SET private = '".($_POST["newPrivate"]=="on"?1:0)."' WHERE id = '".mysql_real_escape_string($pageId)."'",$GLOBALS["mySQLLink"]);
				$message .= $query?"Private Updated Sucessfully <br/>":mysql_error();
				//add log entry for page update
				
				$query = mysql_query("UPDATE pages SET hide = '".($_POST['newHideBox']=='on'?1:0)."' WHERE id = '".mysql_real_escape_string($pageId)."'",$GLOBALS["mySQLLink"]);
				$message .= $query?"Hide from menu updated sucessfully <br/>":mysql_error();

				//update redirect
				$query = mysql_query("UPDATE pages SET redirect = '".mysql_real_escape_string($_POST["newRedirect"])."' WHERE id = '".mysql_real_escape_string($pageId)."'",$GLOBALS["mySQLLink"]);
				$message .= $query?"Redirect Updated Sucessfully <br/>":mysql_error();

				//add log entry for page update
				logEntry("updated page, ID= ".$pageId."");
				break;
			
			//special user permissions
			case "specialPermission":
				//set vars
				$type = $_POST['permissions'];
				$user = $_POST['userSelect'];
				$page = $_POST['permissionPage'];
				$message = "";
				
				//check if username is entered
				if($_POST['userSelect'] == ""){
					$message .= "Please enter a username.";
				}else{
					//check if user has entered a page
					if($_POST['permissionPage'] != -1){
						//check that permission type has been selected and type is not delete
						if($_POST['permissionType' == -1] && $_POST['permissionType'] != "delPermission"){
							$message .= "Please specify a permission type.";
						}else{	
							
							//set more vars
							$user = strtolower($_POST['userSelect']);
							$user = mysql_real_escape_string($user);
							$pageId = mysql_real_escape_string($_POST['permissionPage']);
							$permissionType = mysql_real_escape_string($_POST['permissionType']);
									
									//this switch statement selects between the three types (add, modify, delete)
									switch($type){
										//default: nothing selected
										default:
											$message .= "Please specify add, edit, or delete permissions.";
											break;
										
										//add permissions
										case "addPermission":
											//check if permissions already exist
											$test = mysql_query("SELECT * FROM `pagePermissions` WHERE `username` = '".$user."' AND `pageId` = '".$pageId."'",$GLOBALS["mySQLLink"]);
											if(mysql_fetch_array($test)){
												$message .= "These user permissions already exist!";
											}else{									
												global $db;
												//select user id from phpBB table
												$query = $db->sql_query("SELECT * FROM `phpbb_users` WHERE `username_clean` = '".$user."'",$GLOBALS["mySQLLink"]);
												
												$array = $db->sql_fetchrow($query);
												if(!$array)
													$message .= "The specified user does not exist. Please try again.";
												else{
												$userId = $array['user_id'];
												
												//insert permissions into omni database
												$insert = mysql_query("INSERT INTO  `uberbots_omni`.`pagePermissions` (`userId` ,`username` ,`pageId` ,`type`)VALUES ('".$userId."', '".$user."' , '".$pageId."', '".$permissionType."');",$GLOBALS["mySQLLink"]) or die(mysql_error());
												$message .= $insert?"Permission addition sucessful":mysql_error();
												//add log entry for adding permissions
												logEntry("Added Special permissions for User ".$user." on page with ID of ".$pageId." with permissions type ".$permissionType."");
											}}
											break;
											
										//edit permissions
										case "editPermission":
											//update permissions in SQL
											$update = mysql_query("UPDATE pagePermissions SET type = '".$permissionType."' WHERE username='".$user."' AND pageId='".$permissionType."'",$GLOBALS["mySQLLink"]);
											$message .= $update?"Permissions updated sucessfully.":mysql_error();
											//add log entry
											logEntry("Updated permissions for user ".$user." on page ID ".$pageId." to type ".$permissionType."");
											break;
										
										//delete permissions
										case "delPermission":
											//delete permissions from SQL
											$del = mysql_query("DELETE FROM `pagePermissions` WHERE `username` = '".$user."' AND `pageId` = '".$pageId."'",$GLOBALS["mySQLLink"]);
											$message .= $del?"Permissions deleted sucessfully.":mysql_error();
											//add log entry
											logEntry("Delted permissions for user ".$user." on page ID ".$pageId."");
											break;	
										
									}
								
							
							}
						}else{
							$message .= "Please specify a page for special permissions to be applied.";
					}
				}
			break;	
				
			case "tweet":

			require_once( $root_path.'/includes/twitteroauth/twitteroauth.php' ); 	
		
			define("CONSUMER_KEY", "bgviT8n3zlLDf1T8mzkIUg");
			define("CONSUMER_SECRET", "4f1ay4eYAZJ8EUuGb0me7jipE4VJu4ggojxy3y9rc");
			define("OAUTH_TOKEN", "134827411-2CeWeWLdmVzGXuRMbNBrW7zgbzEIOED4JBCIm23k");
			define("OAUTH_SECRET", "aJLPywofIgRrl0pDL0Vuk5AOdzxpJJYQnNVCleSRyDg");
 
			$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_SECRET);
			$content = $connection->get('account/verify_credentials');
			$response = $connection->post('statuses/update', array('status' => $_POST["tweet"]));

			if ($connection->http_code == 200){
				$message = "Tweet Posted";
				logEntry("Tweeted '".$_POST["tweet"]."'");
			}
			else{
				$message = "Could not post Tweet to Twitter right now. Try again later.";
				logEntry("Twitter Failed. HTTP Code:".$connection->http_code.":".$response);
			}
			break;
		}
		
		
		$log = str_replace("\n","<p>",file_get_contents("$root_path/logs/security.txt"));
		
		$this->selectOutput .= $this->listChildren(0,1);
		$permissionOutput .= $this->showPermissions();
		$oldLogs = $this->oldLogs();
		$this->allPages .= $this->fullChildren(0,1);
		$skins = $this->getSkins();
		
		return parseSkin(array('skins'=>$skins,"fullChildren"=>$this->allPages,"children"=>$this->selectOutput,"permissions"=>$permissionOutput, "message"=>$message,"log"=>$log, "oldLogs"=>$oldLogs),'controlPanel',array("MESSAGE"=>isset($message)));
		
	}else{
		return "This is an unauthorized action. The control panel can only be viewed by an administrator and on the appropriate page.";
	}
	}

	public function renderEdit($properties) {
		return "This module has no editable properties.";
	}

	public function edit($properties) {
	}
	
	var $sqlNames, $sqlDefaults;
	
	public function setup() {
		$this->sqlNames = array();
		$this->sqlDefaults = array();
	}
	
	private function getSkins(){
		$out = "";
		$query = mysql_query("SELECT * FROM `skins`",$GLOBALS["mySQLLink"]);
		while($row = mysql_fetch_array($query)){
			$out .= "<option value=".$row['id'].(($row['id']==0)?" SELECTED":"").">".$row['name']."</option>";
		}
		return $out;		
	}
	
	private function oldLogs(){
		$output = "<div id=\"logList\" name=\"logList\">";
			$output .= "Old Logs:\n";
			$dir = "/home1/uberbots/public_html/omni/logs/";
			if (is_dir($dir)) {
				if($handle = opendir($dir)){
				$output .= "<br><ul>";
				while (false != ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						if($file != "security.txt" && $file != "error_log" && $file != "index.php"){
							$output .= "<li><a href=\"http://uberbots.org/omni/logs/".$file."\" target=\"_blank\">".$file."</a></li>";
						}
					}
				}
				$output .= "</div>";
				closedir($handle);
				}
			}else{
				$output .= "not directory";
			}
		return $output;
	}
	
	private function listChildren($parent, $level){
		$query = mysql_query("SELECT * FROM `pages` WHERE `parentId` = '$parent' AND `deleted` = '0' ORDER BY `order` ASC, `title` ASC",$GLOBALS["mySQLLink"]);
		while($row = mysql_fetch_array($query)){
			if($row["id"]!=0){
			$this->selectOutput .= "<option value='".$row["id"]."'>".str_repeat("-",$level).$row["title"]."</option>\n";
			$this->listChildren($row["id"],$level+1);
			}
		}
		
	}
	
	private function fullChildren($parent, $level){
		$query = mysql_query("SELECT * FROM `pages` WHERE `parentId` = '$parent' ORDER BY `order` ASC, `title` ASC",$GLOBALS["mySQLLink"]);
		while($row = mysql_fetch_array($query)){
			if($row["id"]!=0){
				$del = "";
				if($row['deleted'] == 1){
					$del = " - Deleted";
				}
				$this->allPages .= "<option value='".$row["id"]."'>".str_repeat("-",$level).$row["title"].$del."</option>\n";
				$this->fullChildren($row["id"],$level+1);
			}
		}
		
	}
	
	private function showPermissions(){
		$query = mysql_query("SELECT * FROM `pagePermissions`");
		
		$permissionOutput .= "<h3>Special Permissions List</h3><style type='text/css'>#permissionTable {width:100%;}
#permissionTable TD {padding:5px;width:25%;}</style><table id='permissionTable'><tr><td><b>User ID</b></td><td><b>Username</b></td><td><b>Page ID</b></td><td><b>Permission Type</b></td></tr>";
		while ($row = mysql_fetch_array($query)){
			
			if ($row['type'] == 0)
				$type = "Read";
			elseif ($row['type'] == 1)
				$type = "Write";
			$permissionOutput .= "<tr><td>".$row['userId']."</td><td>".$row['username']."</td><td>".$row['pageId']."</td><td>".$type."</td></tr>";
		}
		$permissionOutput .= "</table>";
		return $permissionOutput;
	}
	
	//check for valid page title
	private function checkTitle($title){
		return preg_match("%\A[A-Za-z0-9\s]{1,20}\Z%",$title);
	}
	


}
