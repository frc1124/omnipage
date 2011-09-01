<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/*News Module
* version 0.1
* Developed by Matt Howard, Phil Lopreiato
*/

class mod_news {
	
	public $title = 'News';
	public $description = 'Shows news articles. Only used on news page.';
	public $path = 'mod_news';

	public function render($properties) {
		//edit an existing article
		if(isset($_POST["add"])&&userPermissions(1)&&isset($_GET["articleId"])){
			$query = mysql_query("UPDATE `uberbots_omni`.`articles` 
SET
`title` = '".mysql_real_escape_string($_POST["title"])."', content = '".mysql_real_escape_string($_POST["content"])."', photo = '".mysql_real_escape_string($_POST["picture"])."', caption = '".mysql_real_escape_string($_POST["caption"])."', importance = '".mysql_real_escape_string($_POST["importance"])."' 
WHERE `id` = ".mysql_real_escape_string($_POST["id"])."
;
") or die(mysql_error());
			logEntry("Article edited ".$_POST["title"]);
			return "The article has been sucessfully updated.";
			}
		//add a new article
		if(isset($_POST["add"])&&userPermissions(1)){
			$query = mysql_query("SELECT `id` FROM  `articles` ORDER BY `id` DESC");
			$newId = mysql_fetch_array($query);
			$newId = $newId["id"]+1;
			$query = mysql_query("INSERT INTO  `uberbots_omni`.`articles` (
`title` ,
`content` ,
`time` ,
`photo` ,
`caption` ,
`id` ,
`importance`
)
VALUES (
'".mysql_real_escape_string($_POST["title"])."',  '".mysql_real_escape_string($_POST["content"])."',  '".time()."',  '".mysql_real_escape_string($_POST["picture"])."',  '".mysql_real_escape_string($_POST["caption"])."',  '".$newId."',  '".mysql_real_escape_string($_POST["importance"])."'
);
") or die(mysql_error());
			return "The article has been sucessfully added.";
			logEntry("Article updated ".$_POST["title"]);
			}
		if(isset($_GET["edit"])&&userPermissions(1)&&isset($_GET["articleId"])){
			$query = mysql_query("SELECT * FROM `articles` WHERE `id` = '".mysql_real_escape_string($_GET["articleId"])."'");
			$row = mysql_fetch_array($query);
			return parseSkin($row,"newsEdit",array("INEDIT"=>true));
			}
		if(isset($_GET["add"])&&userPermissions(1)){
			return parseSkin(array(),"newsEdit",array("INEDIT"=>false));
			}
		//to output an article
		if(isset($_GET["articleId"])){
			$query = mysql_query("SELECT * FROM `articles` WHERE `id` = '".mysql_real_escape_string($_GET["articleId"])."'");
			if($row = mysql_fetch_array($query)){
				$article = "<a href='news'>&lt;&lt;back to news home</a>";
				$article .= "<h1>".$row["title"]."</h1>";
				$article .= "<div style=\"text-align:right;color:#990000;font-size:.75em;margin-bottom:10px;\">".date("F j, Y, g:i a",$row["time"])."</div>";
				$article .= "<div style=\"text-align:justify;line-height:125%;\">";
				$article .= "<div style='display:inline-block;float:right;padding:5px;margin:5px;border:#03c solid 1px;'>
				<img src='".$row["photo"]."' width='250px'><br><span style='font-size:.75em;font-weight:bold;'>".$row["caption"]."</span></div>";
				$article .= str_replace("\n","<p>",$row["content"]);
				$article .= "</div>";
				return $article;
			}
			else{
				return "The requested article was not found.";
			}
		}
		//show news homepage
		else{
			$articleRows = array();
			$weighedImportance = array();
			$query = mysql_query("SELECT * FROM `articles` ORDER BY `time` DESC LIMIT 0, 5");
			while($row=mysql_fetch_array($query)){
				$row["time"] = date("F j, Y, g:i a",$row["time"]);
				$row["content"] = substr($row["content"],0,140);
				$articleRows[] = $row;
				$weightedImportance[] = $row["importance"]-(time()-$row["time"])/3600/24/3;
				}
			array_multisort($weightedImportance,SORT_DESC,SORT_NUMERIC,$articleRows);
			$templateVars = array();
			for($i=0;$i<sizeof($articleRows);$i++){
				foreach($articleRows[$i] as $key => $value){
					$templateVars[$key.($i+1)] = $value;
					}
				}
			return parseSkin($templateVars,'newsHome');
			}
		}

	public function renderEdit($properties) {
		return "No customizable options for this module.";
	}

	public function edit($properties) {
	}
	
	var $sqlNames, $sqlDefaults;
	
	public function setup() {
		$this->sqlNames = array("");
		$this->sqlDefaults = array("");
	}
}
