<?
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************
   
   ******************************************************************************************
   * smsUpdates.php                                                                         *
   * This file sends out sms updates for every calendar event 			                    *
   * Developed by Matt Howard		                                                        *
   * Version 0.1																			*
   ******************************************************************************************/

include "../includes/common.php";

mySQLConnect();

//get number subscribed for updates
$query = $db->sql_query("SELECT `pf_cellphone_number` as `number`, `pf_carrier` as `carrier` 
FROM  `phpbb_profile_fields_data` 
WHERE  `pf_cellphone_number` IS NOT NULL
AND  `pf_carrier` IS NOT NULL AND `pf_carrier` <> 1
LIMIT 0 , 500");

$list = 'Content-Type: text/plain; charset=UTF-8; format=flowed'."\r\n".
'User-Agent: OmniCore 1.0'."\r\n".
'X-Mailer: PHP/' . phpversion()."\r\n".
'Bcc: ';

$carriers = array(
2=>"message.alltel.com",
3=>"txt.att.net",
4=>"myboostmobile.com",
5=>"messaging.nextel.com",
6=>"messaging.sprintpcs.com",
7=>"tmomail.net",
8=>"vtext.com",
9=>"vmobl.com"
);

//loop through each phone number
while($row=$db->sql_fetchrow($query)){
	$list.=$row["number"]."@".$carriers[$row["carrier"]]."; ";
	}


// if sent to server through CRON job, the php $_SERVER['REMOTE_ADDR'] will be blank, signifies script was run for SMS update
if($_SERVER['REMOTE_ADDR']==""){
	//see if there are any event notifications to send
	$startTime = mktime(date("H"),0,0)+(24*60*60);
	$endTime = mktime(date("H"),0,0)+(25*60*60);
	$query = mysql_query("SELECT * FROM `calendar` WHERE `startTime` >= '$startTime' AND `startTime` < '$endTime'");
	
	//create content for each event
	while($row = mysql_fetch_array($query)){
		$content .= date("m/d, h:i A",$row["startTime"]).", ".$row["name"].(strlen($row["description"])>0?", DESCR: ".$row["description"]:"").(strlen($row["location"])>0?" at ".$row["location"]:"").".\n";
		}
	
	//stop here if there are no events
	if(!isset($content))
		exit;
}


//otherwise, if user has control panel permissions and a message is set, it must be an important message
if($_POST["message"]&&userPermissions(1,2)){
	$content = $_POST["message"];
	logEntry("sent SMS message: ".$_POST["message"]);
	}


//finally, mail it if there's something to mail
if(isset($content)){
	mail("","",$content,$list) or die("<p>Sending failed! PHP mail() function returned false</p>");
}

echo "Message sent.";
?>