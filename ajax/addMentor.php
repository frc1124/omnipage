<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************
   
   ******************************************************************************************
   * addMentor.php           	                                                            *
   * This file creates adds a mentor as 'attending' an event on the calendar				*
   * Developed by Matt Howard and Phil Lopreiato	                                        *
   * Version 0.1																			*
   ******************************************************************************************/

//add mentor ajax
include '../includes/common.php';
mySQLConnect();
global $mySQLLink,$user;

if($_POST['mode']=="add"){
	if($user->data["group_id"]==12){
		$eventId = mysql_real_escape_string($_POST['eventId']);
		$mentor = mysql_real_escape_string($_POST['mentor']);

		if((mysql_num_rows(mysql_query("SELECT * FROM `calendarMentors` WHERE userId = '".mysql_real_escape_string($mentor)."' AND eventId = '".mysql_real_escape_string($eventId)."' AND response = '1'",$mySQLLink)) == 0) && (mysql_num_rows(mysql_query("SELECT * FROM `calendarMentors` WHERE userId = '".mysql_real_escape_string($mentor)."' AND eventId = '".mysql_real_escape_string($eventId)."' AND response = '0'",$mySQLLink)) == 0)){//check that mentor isn't already attending 
			$query = mysql_query("INSERT INTO `calendarMentors` (userId, eventId, response, comment) VALUES ('".mysql_real_escape_string($mentor)."','".mysql_real_escape_string($eventId)."','1','')",$mySQLLink) or die(mysql_error()); //insert into SQL
		}else{
			$query = mysql_query("UPDATE calendarMentors SET response = '1' WHERE userId = '".mysql_real_escape_string($mentor)."' AND eventId = '".mysql_real_escape_string($eventId)."'");
		}

		if($query)
		echo "Sucessfully added mentor ".$user->data["username_clean"]." attending this event.";
		else
		echo "Error adding mentor: ".$query;
	}else{
		echo "User is not a mentor.";
	}
}

if($_POST['mode']=="rem"){
	if($user->data["group_id"]==12){
		$eventId = mysql_real_escape_string($_POST['eventId']);
		$mentor = mysql_real_escape_string($_POST['mentor']);
		$query = mysql_query("UPDATE calendarMentors SET response = '0' WHERE userId = '".mysql_real_escape_string($mentor)."' AND eventId = '".mysql_real_escape_string($eventId)."'",$mySQLLink)or die(mysql_error());
		if($query)
			echo "Sucessfully removed mentor ".$user->data["username_clean"]." attending this event.";
	}
}
?>