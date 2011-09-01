<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************
   
   ******************************************************************************************
   * addEvent.php      	                                                                    *
   * This file creates an event in the calendar SQL table and echo event information 		*
   * Developed by Phil Lopreiato                                                            *
   * Version 0.1																			*
   ******************************************************************************************/

$root_path = "/home1/uberbots/public_html/omni";
include "$root_path/includes/common.php";

mySQLConnect();

//check cal permissions
if(userPermissions(1,11) && $_POST['addEvent'] == "true"){
	if($_POST['formType']=="Add"){
		addEvent();
	}else{
		editEvent();	
	}
}
else{
	echo file_get_contents("http://www.uberbots.org/omni/error.php?errorCode=403");
	exit;
}
			
		function addEvent() {
				if(!userPermissions(1,11)){
				echo file_get_contents("http://www.uberbots.org/omni/error.php?errorCode=403");
				return false;
				}
				
			//add event in SQL
			
				//set day/time vars
				$startHour = $_POST['startHour'];
				$startMin = $_POST['startMin'];
				$startMonth = $_POST['startMonth'];
				$startDay = $_POST['startDay'];
				$startYear = $_POST['startYear'];
			
				$endHour = $_POST['endHour'];
				$endMin = $_POST['endMin'];
				$endMonth = $_POST['endMonth'];
				$endDay = $_POST['endDay'];
				$endYear = $_POST['endYear'];
				
				//date is not numeric, return error
				if(!is_numeric($startMonth) || !is_numeric($startDay) || !is_numeric($startYear) || !is_numeric($endMonth) || !is_numeric($endDay) || !is_numeric($endYear) ){
					echo "Error: A date is not in a numeric format. Correct format is: mm-dd-yyyy";
					return false;
				}
				
				//times are not numeric, return error
				if(!is_numeric($startHour) || !is_numeric($startMin) || !is_numeric($endHour) || !is_numeric($endMin) ){
					echo "Error: A time is not a number. Correct format is: hh-mm";
					return false;
				}
				
				//if years aren't 4 digets, return error
				if(strlen($startYear) <4 || strlen($endYear) <4){
					echo "Error: A year is not in 4 diget format. Please enter a year in the format 'yyyy'.";
					return false;
				}
				
				//if times aren't in 12 hour time, return error
				/*if($startHour >12 || $startHour <1 || $startMin >12 || ($startMin <1 && $startMin >0) || $startMin <0 || $endHour >12 || $endHour <1 || $endMin >12 || ($endMin <1 && $endMin >0) || $endMin <0){
					echo "Error: A time is not in 12 hour format. Correct format is: hh-mm";
					return false;
				}*/
				
				//if dates are invalid, return error
				if($startMonth >12 || $startMonth <1 || $endMonth >12 || $endMonth <1 || $endDay <1){
					echo "Error: A date entry in invalid. Correct format is: mm-dd-yyyy ";
					return false;
				}
				
				//turn 12 hr time into 24 hour time
				
				$daylightSavings = date("I",mktime($startHour, $startMin, 0, $startMonth, $startDay, $startYear));
				
				if($_POST['startPM'] == "1" && $startHour < 12){
					$startHour += 12;
				}else{
					$startHour += 0;
				}
				
				if($_POST['endPM'] == "1" && $endHour < 12){
					$endHour += 12;
				}else{
					$endHour += 0;
				}
				
				//make start time UNIX time
				date_default_timezone_set('America/New_York');
				$startUnix = mktime($startHour,$startMin,0,$startMonth,$startDay,$startYear);
				$endUnix = mktime($endHour,$endMin,0,$endMonth,$endDay,$endYear);
				
				if($startUnix >= $endUnix){
					echo "Error: event can not start after it ends. Please make sure of that.";
					return false;
				}
				
				//set rest of vars
				$eventType = $_POST['eventType'];
				$eventDescription = mysql_real_escape_string($_POST['eventDescription']);
				$eventName = mysql_real_escape_string($_POST['eventName']);
				$eventLocation = mysql_real_escape_string($_POST['locationSelect']);
				$locationBox = mysql_real_escape_string($_POST['locationBox']);
				if($eventLocation == "other")
					$eventLocation = $locationBox;
					
				$eventType = mysql_real_escape_string($_POST['eventType']);
				if($eventType == "other")
					$eventType = mysql_real_escape_string($_POST['typeBox']);
				
				//check event type
				if($eventType == "" || $eventType == "Enter Event Type"){
					echo "Error: You must set an event type.";
					return false;
				}
				
				//check event description
				if($eventDescription == ""){
					echo "Error: You must enter an event description";
					return false;
				}
				
				//check event name
				if($eventName == ""){
					echo "Error: You must enter an event name.";
					return false;
				}
				
				//check event location
				if($eventLocation == "Enter location"){
					echo "Error: You must enter an event location.";
					return false;
				}
				
				//check event type
				if($eventType == "" || $eventType == "Enter Event Type"){
					echo "Error: You must enter an event type.";
					return false;
				}
				
						//insert event data into SQL
						$query = mysql_query("SELECT * FROM `uberbots_omni`.`calendar` ORDER BY `id` DESC") or die(mysql_error());
						
						$row = mysql_fetch_array($query)or die(mysql_error());
						$id = $row['id']+1;
						mysql_query("INSERT INTO `uberbots_omni`.`calendar` (`id`, `name`, `description`, `type`, `startTime`, `endTime`, `location`) VALUES ('".mysql_real_escape_string($id)."', '".mysql_real_escape_string($eventName)."', '".mysql_real_escape_string($eventDescription)."', '".mysql_real_escape_string($eventType)."', '".mysql_real_escape_string($startUnix)."', '".mysql_real_escape_string($endUnix)."', '".mysql_real_escape_string($eventLocation)."')") or die(mysql_error());
						
						$startTime = date("D n-j-y \a\t g:i A",$startUnix);
						$endTime = date('D n-j-y \a\t g:i A', $endUnix);
						
						logEntry("Added event with title ".$eventName." starting on ".$startUnix." and ending on ".$endUnix."");
						
						echo "<span><a href=\"javascript:void(0);\" id=\"".$id."\" class=\"eventLink\">".$eventName."</a>
								<div id=\"event_".$id."\" class=\"eventTip ui-corner-all\">".$eventName."<br>".$eventDescription."<br>
								<b>Event Type:</b> ".$eventType."<br>
								<b>Start:</b> ".$startTime."<br>
								<b>End:</b> ".$endTime."<br>
								<b>Location:</b> ".$eventLocation."<br>
								</div></span>";
										
		}

function editEvent(){
	if(!userPermissions(1,11)){
		echo file_get_contents("http://www.uberbots.org/omni/error.php?errorCode=403");
		return false;
	}
	
	//update event in SQL
	
	//get date/time vats
	//set day/time vars
	$startHour = $_POST['startHour'];
	$startMin = $_POST['startMin'];
	$startMonth = $_POST['startMonth'];
	$startDay = $_POST['startDay'];
	$startYear = $_POST['startYear'];

	$endHour = $_POST['endHour'];
	$endMin = $_POST['endMin'];
	$endMonth = $_POST['endMonth'];
	$endDay = $_POST['endDay'];
	$endYear = $_POST['endYear'];

	//date is not numeric, return error
	if(!is_numeric($startMonth) || !is_numeric($startDay) || !is_numeric($startYear) || !is_numeric($endMonth) || !is_numeric($endDay) || !is_numeric($endYear) ){
		echo "Error: A date is not in a numeric format. Correct format is: mm-dd-yyyy";
		return false;
	}
	
	//times are not numeric, return error
	if(!is_numeric($startHour) || !is_numeric($startMin) || !is_numeric($endHour) || !is_numeric($endMin) ){
		echo "Error: A time is not a number. Correct format is: hh-mm";
		return false;
	}
	
	//if years aren't 4 digets, return error
	if(strlen($startYear) <4 || strlen($endYear) <4){
		echo "Error: A year is not in 4 diget format. Please enter a year in the format 'yyyy'.";
		return false;
	}
	//if dates are invalid, return error
	if($startMonth >12 || $startMonth <1 || $endMonth >12 || $endMonth <1 || $endDay <1){
		echo "Error: A date entry in invalid. Correct format is: mm-dd-yyyy ";
		return false;
	}
	
	//turn 12 hr time into 24 hour time
	
	$daylightSavings = date("I",mktime($startHour, $startMin, 0, $startMonth, $startDay, $startYear));
	
	if($_POST['startPM'] == "1" && $startHour < 12){
		$startHour += 12;
	}else{
		$startHour += 0;
	}
	
	if($_POST['endPM'] == "1" && $endHour < 12){
		$endHour += 12;
	}else{
		$endHour += 0;
	}
	
	//make start time UNIX time
	date_default_timezone_set('America/New_York');
	$startUnix = mktime($startHour,$startMin,0,$startMonth,$startDay,$startYear);
	$endUnix = mktime($endHour,$endMin,0,$endMonth,$endDay,$endYear);
	
	if($startUnix >= $endUnix){
		echo "Error: event can not start after it ends. Please make sure of that.";
		return false;
	}
	
	//set rest of vars
	$eventType = $_POST['eventType'];
	$eventDescription = mysql_real_escape_string($_POST['eventDescription']);
	$eventName = mysql_real_escape_string($_POST['eventName']);
	$eventLocation = mysql_real_escape_string($_POST['locationSelect']);
	$locationBox = mysql_real_escape_string($_POST['locationBox']);
	if($eventLocation == "other")
		$eventLocation = $locationBox;
		
	$eventType = mysql_real_escape_string($_POST['eventType']);
	if($eventType == "other")
		$eventType = mysql_real_escape_string($_POST['typeBox']);
	
	//check event type
	if($eventType == "" || $eventType == "Enter Event Type"){
		echo "Error: You must set an event type.";
		return false;
	}
	
	//check event description
	if($eventDescription == ""){
		echo "Error: You must enter an event description";
		return false;
	}
	
	//check event name
	if($eventName == ""){
		echo "Error: You must enter an event name.";
		return false;
	}
	
	//check event location
	if($eventLocation == "Enter location"){
		echo "Error: You must enter an event location.";
		return false;
	}
	
	//check event type
	if($eventType == "" || $eventType == "Enter Event Type"){
		echo "Error: You must enter an event type.";
		return false;
	}
	
	//update event in SQL
	$query = mysql_query("UPDATE `uberbots_omni`.`calendar` SET `name` = '".mysql_real_escape_string($eventName)."', `description` = '".mysql_real_escape_string($eventDescription)."', `type` = '".mysql_real_escape_string($eventType)."', `startTime` = ".mysql_real_escape_string($startUnix).", `endTime` = ".mysql_real_escape_string($endUnix).", `location` = '".mysql_real_escape_string($eventLocation)."' WHERE `id` = '".mysql_real_escape_string($_POST['eventId'])."'")or die(mysql_error());	
	
	$startTime = date("D n-j-y \a\t g:i A",$startUnix);
	$endTime = date('D n-j-y \a\t g:i A', $endUnix);
	
	logEntry("Edited event with ID: ".mysql_real_escape_string($_POST['eventId']));
	
	echo "<p>".$eventName."</p> <!-- popup of event info, start with event name -->
		  <p>".$eventDescription."</p> <!-- event description -->
		  <p><strong>Event Type: </strong>".$eventType."</p> <!-- event type -->
		  <p><strong>Start: </strong>".$startTime."</p> <!-- event start time -->
		  <p><strong>End: </strong>".$endTime."</p> <!-- event end time -->
		  <p><strong>Location: </strong>".$eventLocation."</p> <!-- event location -->
		  <p><strong>Number of Mentors:</strong> ".mysql_num_rows(mysql_query("SELECT * FROM `calendarMentors` WHERE `eventId` = '".$row['id']."' AND `response` = '1'"))."</p>
		  ";
		 
}
?>