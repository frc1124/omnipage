<?
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************
   
   ******************************************************************************************
   * sendEmail.php                                                                          *
   * Sends out mass emails and weekly updates                                               *
   * Developed by Matt Howard and Phil Lopreiato                                            *
   * Version 0.1                                                                            *
   ******************************************************************************************/  

include "../includes/common.php";

mySQLConnect();

//create unsuscribe/info email footer
$footer = "<br/><p>This message was sent by the &Uuml;berBots mass email system. If you are receiving this message in error, please email <a href='mailto:unsubscribe@uberbots.org'>unsubscribe@uberbots.org</a>. If you wish to remove yourself from the list manually, follow the instructions <a href='http://uberbots.org/o/Resources/Email_List_Sign_Up'>here</a>. If you have other questions or comments, please email the <a href='mailto:webmaster@uberbots.org'>webmaster</a>.</p>";

$list = 'Bcc: ';

$group = isset($_GET["group"])?$_GET["group"]:(isset($_POST["group"])?$_POST["group"]:3);

//if sent to team members/alumni
if($group=="1"||$group=="3"){
	//build "To:" list
	$query = $db->sql_query("
	SELECT  `phpbb_users`.`user_email` AS  `email` ,  `phpbb_profile_fields_data`.`pf_mail_subscription` AS  `mail_subscription` 
	FROM  `phpbb_users` 
	LEFT JOIN  `phpbb_profile_fields_data` ON  `phpbb_users`.`user_id` =  `phpbb_profile_fields_data`.`user_id` 
	WHERE  `phpbb_users`.`user_email` <>  ''
	AND (
	`phpbb_profile_fields_data`.`pf_mail_subscription` = 2
	OR ISNULL(  `phpbb_profile_fields_data`.`pf_mail_subscription`)
	)
	LIMIT 0 , 500
	");
	
	while($row = $db->sql_fetchrow($query)){
		$list .= "<".$row["email"].">; ";
	}
}

//if sent to parents
if($group=="2"||$group=="3"){
	//build "To:" list
	$query = mysql_query("SELECT `email` FROM  `emailList`")or die(mysql_error());
	//when we start double-opt-in, use the following line instead of the preceding line
	//$query = mysql_query("SELECT `email` FROM `emailList` WHERE `confirmed` = '1'");
	
	while($row = $db->sql_fetchrow($query)){
		//don't duplicate
		if(!strpos($list,$row["email"]))
			$list .= "<".$row["email"].">; ";
	}
}
//append footer
$message = $_POST['message'].$footer;

//makes headers
$head = 'MIME-Version: 1.0' . "\r\n" .'Content-type: text/html; charset=UTF-8'."\r\n".$list;
$subject = $_POST['subject'];
//mail message if admin is signed in
if(userPermissions(1,2)&&!isset($_GET["calendar"])){
	$mail = mail("",$_POST["subject"],$message,$head) or die("Mail sending failed");
	//$mail = mail("plnyyanks@gmail.com",$_POST['subject'],$message.$head);
	//echo "mail('',$subject,$message,$head) or die('Mail sending failed<p>'.$list)";
	//mail("plnyyanks@gmail.com","foo","foo")or die("foobar");
	if($mail){
		logEntry("Mass email sent :".$_POST["subject"]);
		logEmail($_POST['subject'],$message);
		header("location: /o/control_panel");
	}else{
		LogEntry("Mass email sending failed");
		echo "Mailing error!";
	}
}


//otherwise, message is weekly update if no IP is found
if($_SERVER['REMOTE_ADDR']==""||isset($_GET['calendar'])){
	//make html mail
	$list = 'MIME-Version: 1.0' . "\r\n" .
			'Content-type: text/html; charset=iso-8859-1'."\r\n".
			'X-Mailer: PHP/' . phpversion() ."\r\n". $list;
	
	//create weekly updates
	$timeStart = (24*60*60) + time();
	$timeEnd = $timeStart + (7*24*60*60);
	$updates.= "Hello, this is the &Uuml;berBots weekly event update for the week of " . date("m/d/y", $timeStart) . " to " . date("m/d/y", $timeEnd) . ".<br />\r\n";
	$updates.= "If you would like further information and updates, please visit our calendar page <a href=\"http://www.uberbots.org/o/calendar\">here</a>.<br /><br />\r\n\r\n";
	
	$query = mysql_query("SELECT * FROM `calendar` WHERE `startTime` > $timeStart AND `endTime` < $timeEnd ORDER BY `startTime` ASC") or die(mysql_error());
	
	$prevday = "abc";
	
	//day heading
	while ($row = mysql_fetch_assoc($query)) {
		if (date("d", $row['startTime']) != $prevday) {
			if ($prevday != "abc") {
				$updates.= "</div><br />";
			}
			$updates.= "<div style=\"color:#f33; border-bottom: #f33 solid 1px; font-size: 16px;\">" . date("l, F jS, Y", $row['startTime']) . "</div>\r\n<div style=\"padding-left: 10px;\">";
		}
		$updates.= date("g:ia", $row['startTime']) . "-" . date("g:ia", $row['endTime']) . ": " . $row['name'] . (!empty($row['description'])?(" - ".$row['description']):"" ). "<br />\r\n";
		//var_dump($row);		
		$prevday = date("d", $row['startTime']);
	}
	$updates .= "</div>";
	
	//recent forums posts
	
	$lastWeek = $timeStart - (7*24*60*60);
	
	$fSql = "SELECT * 
	FROM  `phpbb_topics` 
	WHERE  `phpbb_topics`.`topic_time` < '$timeStart' AND `phpbb_topics`.`topic_time` > '$lastWeek'
	ORDER BY `phpbb_topics`.`topic_time`
	LIMIT 0 , 30";
	
	$query = $db->sql_query($fSql);
	
	if ($db->sql_fetchrow($query)) {
		$auth->acl($user->data);
		
		$updates.= "<p><b>Recent Forum Posts</b></p>";
		
		$query = $db->sql_query($fSql) or die(mysql_error());
		
		while($row=$db->sql_fetchrow($query)){
			$firstPostQ = $db->sql_query("SELECT * 
FROM  `phpbb_posts` WHERE `post_id` = '".$row["topic_first_post_id"]."'");
			$firstPost = $db->sql_fetchrow($firstPostQ);
			
			$firstPost["post_text"]=substr(preg_replace("/(\[(.*?)\:(.*?)\]|<.+>)/", "",$firstPost["post_text"]),0,160)."...";
			
			if($auth->acl_get('f_read', $row["forum_id"])){
			$updates.= "<a href=\"http://uberbots.org/forums/viewtopic.php?f=".$row["forum_id"]."&t=".$row["topic_id"]."\" style=\"text-decoration:none;color:black;\">
			
			
			<div style=\" color:#f33; border-bottom: #f33 solid 1px; font-size: 16px;\">" . $row["topic_title"]. "</div>\n<div style=\"padding-left: 10px;\">
			".$firstPost["post_text"]."
			<br>
			<small>&nbsp; &nbsp; &nbsp; -Posted by ".$row["topic_first_poster_name"]."</small></div></a>";
			}
	}}
	$subject = "UberBots Weekly Updates: " . date("m/d/y", $timeStart) . " to " . date("m/d/y", $timeEnd);
	if(!isset($_GET["calendar"])){
		$mail = mail("",$subject,$updates.$footer,$list) or die("Mail sending failed<p>".$list);
		if($mail)
			logEntry("Weekly Updates Sent");
	}else{
		echo $updates."<p>".str_replace(array("<",">"),array("&lt;","&gt;"),$list);
	}
	
}

?>