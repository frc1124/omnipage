<?
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************
 
   ******************************************************************************************
   * emailList.php                                                                          *
   * This file adds, removes, and confirms users on the parent email list                   *
   * Developed by Phil Lopreiato and Matt Howard                                            *
   * Version 0.1                                                                            *
   ******************************************************************************************/

include "../includes/common.php";
include "../includes/emailConfirm.php";

mySQLConnect();

if(isset($_POST["add"]))
	{
	if(!mysql_fetch_array(mysql_query("SELECT * FROM `emailList` WHERE `email` LIKE '".mysql_real_escape_string($_POST["email"])."'"))){
		mysql_query("INSERT INTO `emailList` VALUES ('".mysql_real_escape_string($_POST["email"])."', '0')") or die(mysql_error());
		echo "You are now on the mailing list. An email will be sent to the address supplied for confirmation.";
		sendConfirmation($_POST['email']);
		}
	else{
		echo "You are already subscribed.";
		}
	}

if(isset($_POST["unsubscribe"])){
	if(mysql_fetch_array(mysql_query("SELECT * FROM `emailList` WHERE `email` LIKE '".mysql_real_escape_string($_POST["email"])."'"))){
		mysql_query("DELETE FROM `emailList` WHERE `email` LIKE '".mysql_real_escape_string($_POST["email"])."'") or die(mysql_error());
		echo "You are now off the parent mailing list.";
	}else{
		$userQuery = $db->sql_query("SELECT * FROM phpbb_users WHERE user_email = '".$email."'");
		$userData = $db->sql_fetchrow($userQuery);
		$userId = $userData["user_id"];
		$subCheck = $db->sql_query("SELECT * FROM phpbb_profile_fields_data WHERE user_id = '".$userId."'");
		if(mysql_num_rows($subCheck) > 0){
			$db->sql_query("UPDATE phpbb_profile_fields_data SET pf_mail_subscription = '1' WHERE user_id = '".$userId."'");
			echo "You have been sucessfully removed from the team member mailing list.";
		}else{
			$db->sql_query("INSERT INTO phpbb_profile_fields_data (`user_id`, `pf_graduating`, `pf_team_positions`, `pf_mail_subscription`, `pf_cellphone_number`, `pf_carrier`) VALUES ('".$userId."', NULL, NULL, '1', NULL, NULL)");
			echo "You are not subscribed.";
		}	
	}
}
	
if(isset($_GET['confirm'])){
	if(mysql_fetch_array(mysql_query("SELECT * FROM `emailList` WHERE `email` LIKE '".mysql_real_escape_string($_GET['email'])."'"))){
		mysql_query("UPDATE `emailList` SET `confirmed` = '1' WHERE `email` LIKE '".mysql_real_escape_string($_GET['email'])."'")or die(mysql_error());		
		echo "Your email address has been confirmed";
	}else{
		echo "You are not signed up for the email list. Please sign up before confirming.";	
	}
}

echo "You will be redirected to the homepage in 5 seconds.
<meta http-equiv=\"REFRESH\" content=\"5;url=/o\">";
?>