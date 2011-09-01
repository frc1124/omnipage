<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************
   
   *******************************************************************************************
   *                                                                                         *
   * Checks for emails sent to the following addresses:                                      *
   * mentor.forwarder@uberbots.org                                                           *                                                                                 *
   * And sends them out at mass emails to specific groups                                    *
   *******************************************************************************************/

include "../includes/common.php";

$mentor_path = "/home1/uberbots/mail/uberbots.org/mentor.forwarder/new";

$handle = opendir($mentor_path);

while (false !== ($file = readdir($handle))) {
    if($file!="."&&$file!=".."){
		if($fileHandler = fopen($mentor_path."/".$file,'r'))
			$contents = fread($fileHandler,100000);
		else
			echo "file could not be read/opened ";

		fclose($fileHandler);

		preg_match("%Subject: (.+)%",$contents,$subject);

		preg_match("%From: (.+)%",$contents,$from);
		
		$firstLine = strpos($contents,"\n\n");
		
		$contents = substr($contents,$firstLine);
		
		//$contents = str_replace("\n  ","",$contents);		

		//get emails
		
		$emails = "\nBcc: ";
		
		$query = $db->sql_query("SELECT `user_email` FROM `phpbb_users` WHERE `group_id` = '12' AND `user_allow_pm` = '1'");
		
		while($row=$db->sql_fetchrow($query)){
			$emails.=$row['user_email']."; ";
			}
		
		echo $emails;
		
		mail("",$subject[0],$contents,$from[0].$emails);
		
		unlink($mentor_path."/".$file);		

		}
    }

closedir($handle);
?>