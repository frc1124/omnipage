#!/usr/bin/php -q
<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************
 
   ******************************************************************************************
   * emailPipe.php                                                                          *
   * This file reads a piped email and forawrds it to the appropriate address               * 
   * Developed by Phil Lopreiato                                                            *
   * Version 0.1                                                                            *
   * to add a group, in cpanel, create a new email forwarder and pipe it to a program. make *
   * sure the pipe script location is:                                                      *
   * /usr/bin/php -q /home1/uberbots/public_html/omni/actions/emailPipe.php                 *
   ******************************************************************************************/

//include common
include "../includes/common.php";

//connect to SQL database
mySQLConnect();

// read from stdin
$fd = fopen("php://stdin", "r");
$email = "";
while (!feof($fd)) {
    $email .= fread($fd, 1024);
}
fclose($fd);

//parse email
$lines = explode("\n",$email);
$otherHeaders = "";
$message = "";
$splitHeaders = true;

$from = ""; 
$subject = ""; 
$message = ""; 
$contentType = "";
$to = "";
$splittingheaders = true;

for ($i=0; $i < count($lines); $i++) { 
	if ($splittingheaders) {  
		$otherHeaders .= $lines[$i]."\n"; 
		if (preg_match("/^Subject: (.*)/", $lines[$i], $matches)) { 
			$subject = $matches[1]; 
		} 
		if (preg_match("/^From: (.*)/", $lines[$i], $matches)) { 
			$from = $matches[1]; 
		} 
		if (preg_match("/^To: (.*)/", $lines[$i], $matches)) { 
			$to = $matches[1]; 
		} 
		if (preg_match("/^Content-Type: (.*)/", $lines[$i], $matches)) { 
			$contentType = $matches[1]; 
		} 
	} else { 
		// not a header, but message 
		$message .= $lines[$i]."\n"; 
	}
	
	if (trim($lines[$i])=="") { 
		// empty line, header section has ended 
		$splittingheaders = false; 
	} 
}

//parse MIME Content-Type
$bound = explode("boundary=",$contentType); //this is the boundary delimination key
$explodeMessage = explode("--".$bound[1],$message); //explode message into MIME sections
$htmlFound = false;
$headerFinished = false;
$htmlMessage = "";
foreach($explodeMessage as $k => $v){
	$messageLines = explode("\n",$v);	
	for($i = 0;$i<count($messageLines);$i++){
		if($htmlFound){
			$htmlMessage .= $messageLines[$i];	
		}
		if (preg_match("/^Content-Type: text\/html(.*)/", $messageLines[$i])) { 
			$htmlFound = true;		
		}
	}
	if($htmlFound){
		break;	
	}
}

//parse 'to' field for group names
$addresses = preg_split("(,|;)",$to);
$groups = array();
foreach($addresses as $k => $addr){
	$addressParts = explode(" ",$addr);
	$last = count($addressParts);
	$groupName = explode("@",$addressParts[$last-1]);
	foreach($groupName as $key => $name){
		$groupName[$key] = preg_replace("(<|>|\")","",$groupName[$key]);	
	}
	array_push($groups,$groupName[0]);
}

//make send-to list
$list = "";
foreach($groups as $k => $group){
	switch($group){
		default:
			$groupId = -1;
			break;
		case "pipetest":
			$groupId = 20;
			break;
		case "mentors":
			$groupId = 12;
			break;
		case "webmaster":
			$groupId = 5;
			break;
	}
	if(isset($_GET['test'])){
		$groupId = $_GET['group'];
	}
	
	$query = $db->sql_query("SELECT * FROM `phpbb_user_group` WHERE `group_id` = ".$groupId);
	while($user = $db->sql_fetchrow($query)){
		$q = $db->sql_query("SELECT * FROM  `phpbb_users` WHERE  `user_id` = ".$user['user_id']);
		while($row = $db->sql_fetchrow($q)){
			if(!strpos($list,$row['user_email'])){
				$list .= $row['user_email'].'; ';
			}
		}
	}	
}

//compile other headers
$headers = 	'Bcc: '.$list.'\r\n'.
			'MIME-Version: 1.0' . "\r\n" .
			'Content-type: text/html; charset=iso-8859-1'."\r\n".
			'X-Mailer: PHP/' . phpversion() ."\r\n".
			'Reply-To: '.$from."\r\n". 
			'From: ' . $from ."\r\n" . 
			$headers;

$htmlMessage .= "<div>This message forwarded by UberBots' email piping.</div>";

mail("plnyyanks@gmail.com",$subject,$list."<br/>".$headers."<br/>".$htmlMessage,$headers)or die("Mail Sending Failed".$list);

if(!isset($_GET['test'])){
//send email out to list
//mail($list,$subject,$htmlMessage,$headers)or die("Mail Sending Failed".$list);
}else{
	echo $list;
	echo $htmlMessage;
	echo $headers;
}
?>