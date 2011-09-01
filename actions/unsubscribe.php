#!/usr/bin/php -q
<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************

   ******************************************************************************************
   * unsubscribe.php                                                                         *
   * This file reads a piped email and unsuscribes the sender from mass emailing lists      *
   * Developed by Phil Lopreiato                                                            *
   * Version 0.1                                                                            *
   ******************************************************************************************/

//include common
include "../includes/common.php";

//connect to SQL database
mySQLConnect();

// read email from stdin
$fd = fopen("php://stdin", "r");
$email = "";
while (!feof($fd)) {
    $email .= fread($fd, 1024);
}
fclose($fd);
 
//parse email
$lines = explode("\n",$email); //break email by line
$otherHeaders = "";
$message = "";
$splitHeaders = true; //start by separating headers

$from = ""; 
$subject = ""; 
$message = ""; 
$contentType = "";
$to = "";
$splittingheaders = true;

for ($i=0; $i < count($lines); $i++) { 
	if ($splittingheaders) {  
		$otherHeaders .= $lines[$i]."\n"; 
		if (preg_match("/^From: (.*)/", $lines[$i], $matches)) { 
			$from = $matches[1]; 
		} 
	}
	if (trim($lines[$i])=="") { 
		// empty line, header section has ended 
		$splittingheaders = false; 
	} 
}
//parse from address
$pos = strrpos($from,"@");
$beforeAT = substr($from,0,$pos);
$start = strrpos($beforeAT," ");
$email = substr($from,$start);
$email = preg_replace("(;|,|<|>|\")","",$email);
$email = str_replace(" ","",$email);

//delete from parent database if exists
$parentQuery = mysql_query("SELECT * FROM emailList WHERE email = '".$email."'");
if(mysql_num_rows($parentQuery) > 0){
	$parentDel = mysql_query("DELETE FROM emailList WHERE email = '".$email."'");	
}

//unsuscribe from team member emails
$userQuery = $db->sql_query("SELECT * FROM phpbb_users WHERE user_email = '".$email."'");
$userData = $db->sql_fetchrow($userQuery);
$userId = $userData["user_id"];
$subCheck = $db->sql_query("SELECT * FROM phpbb_profile_fields_data WHERE user_id = '".$userId."'");
if(mysql_num_rows($subCheck) > 0){
	$db->sql_query("UPDATE phpbb_profile_fields_data SET pf_mail_subscription = '1' WHERE user_id = '".$userId."'");
}else{
	$db->sql_query("INSERT INTO phpbb_profile_fields_data (`user_id`, `pf_graduating`, `pf_team_positions`, `pf_mail_subscription`, `pf_cellphone_number`, `pf_carrier`) VALUES ('".$userId."', NULL, NULL, '1', NULL, NULL)");
}
//mail($email,"Unsubscribe From UberBots Mass Email","You have been sucessfully been removed from the UberBots mass email system. If you have any questions or concerns, please email the <a href='mailto:webmaster@uberbots.org'>webmaster</a>. <br/>Thanks, <br/><br/> The Webmaster<br/><br/><p>Please do not reply to this message</p>",'MIME-Version: 1.0' . "\r\n" .'Content-type: text/html; charset=UTF-8'. "\r\n" . "From: noreply@uberbots.org");
?>