<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* Calendar Module
 * version 1.0
 * Developed by Phil Lopreiato
 * Uses PHP Calendar script from http://scripts.franciscocharrua.com/php-calendar.php (sort of)
 * Includes meeting minutes and mentors integration
 * Meeting minutes allow forum topics to be linked to calendar events
 * Mentors integration allows mentors to show what meeting they can attend
 * This module is fully skinned. It uses all skin files that start with 'cal'+file name, as well as 'calendar'
 */

class mod_calendar {
	
	/* This class is for the calendar module */
	
	public $title = 'Calendar'; //title of the module
	public $description = 'creates a one-week or one-month calendar or a list of upcoming events'; //description of the module 
	public $path = 'mod_calendar'; //path to module files (from /omni/modules)

	public function render($properties) {
		
		/* render function is the function that is called whenever the module needs to be drawn
		 * it contains all code necessary to display the module
		 * it takes the $properties array, which contains all properties for the module (page, instance, etc.)
		 * $properties is defined in 'module.php' and contains all info in the 'moduleProps' table assosciated with the module, as well as page ID and instance ID
		 */
		
		//include global variables
		//$root_path = /home1/uberbots/public_html/omni - use for URLs
		//$current skin is the name of the current skin - use for skin stuff
		global $root_path, $currentSkin;
		
		//more globals
		//$user is user related data
		//$db is phpbb SQL stuff
		global $user,$db;

		//check is user is mentor
		$isMentor = $user->data["group_id"]==12?true:false;
		
		//old mentors integration, now in view event stuff
		//add mentor to event in SQL
		/* if(isset($_GET["addMe"])&&$isMentor){
			if(!mysql_fetch_array(mysql_query("SELECT * FROM `calendarMentors` WHERE userId = '".$user->data["user_id"]."' AND eventId = '".mysql_real_escape_string($_GET["mentors"])."'"))) //check that mentor isn't already attending 
				$query = mysql_query("INSERT INTO `calendarMentors` VALUES ('".$user->data["user_id"]."','".mysql_real_escape_string($_GET["mentors"])."','','')") or die(mysql_error()); //insert into SQL
		}

		if(isset($_GET["mentors"])){ //show signup page
			$eventId = $_GET["mentors"]; //this event's ID
			$query = mysql_query("SELECT * FROM `calendarMentors` WHERE `eventId` = '".mysql_real_escape_string($eventId)."'") or die(mysql_error()); //select calendarMentor table
			$mentorList = "";
			while($row = mysql_fetch_array($query)){ //make list of mentors attending
				$userQuery = $db->sql_query("SELECT `username` FROM `phpbb_users` WHERE `user_id`='".$row['userId']."'");
				$userName = $db->sql_fetchrow($userQuery);
				$mentorList .= parseSkin(array("username"=>$userName["username"]),"mentorList", array()); //parse skin mentorList, which makes the list of mentors
			}
			
			$eventId = urlencode($eventId);
			return parseSkin(array("eventId"=>$eventId,"list"=>$mentorList),"mentors",array("MENTOR"=>$isMentor)); //return final mentor page skin
		}*/
		
		//view event page
		if(isset($_GET['viewEvent']) && isset($_GET['eventId']) && is_numeric($_GET['eventId']) && $properties['type']!=2){ //check for GET variables
			unset($rep);
			unset($ifs);
			$q = mysql_query("SELECT * FROM `calendar` WHERE id = ".mysql_real_escape_string($_GET['eventId']));
			$a = mysql_fetch_array($q);
			$sTime = $a['startTime'];
			$sTime = date("l j F, Y \- g:i A", $sTime);
			$eTime = $a['endTime'];
			$eTime = date("l j F, Y \- g:i A",$eTime);
			//global phpBB database stuff
			global $db;
			$minute = "";
			$query = mysql_query("SELECT * FROM `calendarLinks` WHERE `eventId` = '".$a["id"]."'") or die(mysql_error());
			while($min = mysql_fetch_assoc($query)){ //returns all linked topics for today for meeting minutes
				$title = $db->sql_query("SELECT `topic_title` FROM `phpbb_topics` WHERE `topic_id` = '".$min["postId"]."' AND (`forum_id` = '".$min["forumId"]."' OR `topic_first_post_id` = '".$min["forumId"]."')");
				$titleRow = $db->sql_fetchrow($title);
				if($titleRow)
					$minute .= "<li><a href='/forums/viewtopic.php?t=".$min["postId"]."&amp;f=".$min["forumId"]."'>".$titleRow["topic_title"]."</a></li>";
			}		
			$this->minutes = !empty($minute);
			$this->minuteURL = urlencode(date("D n-j-y",$a['startTime']));
			$mentorQuery = mysql_query("SELECT * FROM `calendarMentors` WHERE eventId = '".mysql_real_escape_string($_GET['eventId'])."' AND response = '1'");
			$numMentors = mysql_num_rows($mentorQuery);
			if($numMentors>0 && $user->data['user_id'] != ANONYMOUS){
				$this->mentors = TRUE;
			}else{
				$this->mentors = FALSE;
			}
			
			$eventId = mysql_real_escape_string($_GET["eventId"]); //this event's ID
			$mentorQuery = mysql_query("SELECT * FROM `calendarMentors` WHERE `eventId` = '".mysql_real_escape_string($eventId)."' AND `response` = '1'") or die(mysql_error()); //select calendarMentor table
			$mentorList = "";
			while($row = mysql_fetch_array($mentorQuery)){ //make list of mentors attending
				$userQuery = $db->sql_query("SELECT `username` FROM `phpbb_users` WHERE `user_id`='".$row['userId']."'");
				$userName = $db->sql_fetchrow($userQuery);
				$mentorList .= parseSkin(array("username"=>$userName["username"]),"mentorList", array()); //parse skin mentorList, which makes the list of mentors
			}
			$thisMentor = mysql_query("SELECT * FROM `calendarMentors` WHERE `eventId` = '".mysql_real_escape_string($eventId)."' AND userId = '".$user->data["user_id"]."' AND response = '1'");
			$thisArray = mysql_fetch_array($thisMentor);
			$n = mysql_num_rows($thisMentor);
			$addMentor = "<a href='javascript:void(0);' name='addMentor' id='addMentor' onclick='addMentor(".$user->data["user_id"].",".mysql_real_escape_string($_GET['eventId']).")'>Add Me</a><br/>";
			$ismentor = ($user->data["group_id"]==12 && $n==0)?true:false;
			$remMentor = "<a href='javascript:void(0);' name='remMentor' id='remMentor' onclick='remMentor(".$user->data["user_id"].",".mysql_real_escape_string($_GET['eventId']).")'>Remove Me</a><br/>";
			$mentorAttending = ($user->data["group_id"]==12 && $n>0)?true:false;
			$minURL = urlencode(date("D n-j-y",$a['startTime']));
			$rep = array("minuteURL"=>$minURL,"id"=>$a['id'],"eventName"=>$a['name'],"startTime"=>$sTime,"endTime"=>$eTime, "location"=>$a['location'],"type"=>$a['type'], "description"=>$a['description'], "minutes"=>$minute, "mentors"=>$mentorList,"mentorLink"=>$addMentor,"remMentor"=>$remMentor);
			$ifs = array("MINUTE"=>$this->minutes,"MENTORS"=>$this->mentors,"mentorCheck"=>$ismentor,"mentorAttending"=>$mentorAttending);
			return parseSkin($rep,'calViewEvent',$ifs);
		}

		//get pageId/module instance/properties
		$this->pageId = $properties['pageId'];
		$this->instanceId = $properties['instanceId'];
		//module type: 0 is full cal, 1 is week cal
		$this->props = $properties['type'];
		 
		 //check if user is admin
		$pagePermissions = userPermissions(1);
		
		
		if(isset($_GET["date"])){ //check if start date is set for other months than current (in UNIX time)
			$date = $_GET["date"]; //get UNIX timestamp for start
			$day = date("j",$date); //day of month
			$month = date("m",$date); //month number
			$month_name = date("F",$date); //month name
			$year = date("Y",$date); //year
		}else{ //GET start date is not set, display current month calendar
			$currentTime = time(); //get current unix time
			$date = mktime(0,0,0,date('n',$currentTime),1,date('Y',$currentTime));
			$day = date("j",$currentTime); //day of month
			$month = date("m",$currentTime); //month number
			$month_name = date("F",$currentTime); //month name
			$year = date("Y",$currentTime); //year
			}

        $this_month = getDate(mktime(0, 0, 0, $month, 1, $year)); //this month's date array
        $next_month = getDate(mktime(0, 0, 0, $month + 1, 1, $year)); //next month's date array
		$last_month = getDate(mktime(0, 0, 0, $month - 1, 1, $year)); //last month's date array
		$today = getDate(mktime(0, 0, 0, $month, $day, $year)); //today's date array
		
		 $nextMonth=mktime(0,0,0,$month+1,1,$year); //next month's UNIX timestamp (for next month link)
		 $lastMonth=mktime(0,0,0,$month-1,1,$year); //last month's UNIX timestamp (for last month link)
		 
		 //set vars according to calendar type
         if($this->props == 0){ //full month calendar
			 //Find out when this month starts and ends.
			 $first_week_day = $this_month["wday"]; //day of week of the first of the month
			 $days_in_this_month = round(($next_month[0] - $this_month[0]) / (60 * 60 * 24)); //get number of days in the month
			 $daysInMonth = $days_in_this_month;
		 }else if($this->props == 1){ //one week calendar
			$date += (60*60*24);
			$weekDay = $today['wday']; //today's day of the week
			$first_week_day = 0; //start on a Sunday
			$days_in_this_month = (7); //end 7 days later (Saturday)
			$day = ($today['mday']-$today['wday']) - 1; //day of month - day of week, date of last sunday
			$date = mktime(0,0,0,$month,$day+1,$year);
			$daysInMonth = 7; //go for seven days
			}	
        $week_day = $first_week_day;

		
		if($properties['type'] == 0 || $properties['type'] == 1){
			//set skin vars
			$this->showForm = $this->showForm($this->props); //weather or not to show 'add event form', not in use
			if($this->props == 0){ //full month calendar
				$this->fullCal = TRUE; //set skin if for fullCal to true
				$this->weekCal = FALSE; //set skin if for weekCal to false
			}elseif($this->props == 1){ //week calendar
				$this->fullCal = FALSE; //fullCal if = false
				$this->weekCal = TRUE; //weekCal if = true
			}
			$this->monthName = $month_name; //month name
			$this->year = $year; //year
			$this->nextMonth = $nextMonth; //next month's UNIX time
			$this->lastMonth = $lastMonth; //last month's UNIX time
			$this->calOutput = $this->drawCal($date, $pagePermissions, $daysInMonth, $first_week_day); //draws the calendar, function drawCal
			$this->permissions = userPermissions(1); //does user have permissions?
			$this->addForm = parseSkin(array(),"calAddForm",array()); //add event form
			$this->ical = parseSkin(array(),'cal_ical',array());
			$parseReplace = array("showForm"=>$this->showform, 			//set skin vars for replacement
								  "monthName"=>$this->monthName,
								  "year"=>$this->year, 
								  "nextMonth"=>$this->nextMonth, 
								  "lastMonth"=>$this->lastMonth, 
								  "calendar"=>$this->calOutput, 
								  "modType"=>$this->props, 
								  "daysInMonth"=>$daysInMonth,
								  "addForm"=>$this->addForm,
								  "icalForm"=>$this->ical
								  );
			$parseIfs = array("fullCal"=>$this->fullCal, 				//set parse IF's
							  "weekCal"=>$this->weekCal, 
							  "ADMIN"=>$this->permissions);
			
			//parse calendar skin
			return parseSkin($parseReplace,'calendar',$parseIfs);
		}elseif($properties['type'] == 2){ //list calendar
			$time = time(); //get current unix time
			$currentDate = mktime(0,0,0,date('n',$time),date('j',$time),date('Y',$time));
			$endDate = $currentDate + (60*60*24*$properties['days']);
			$list = "";
			
			if(mysql_num_rows(mysql_query("SELECT * FROM `uberbots_omni`.`calendar` WHERE (`startTime` >= '".$currentDate."' AND `startTime` < '".($endDate + (60*60*24))."')"))!=0){
				
				for($currentDate; $currentDate <= $endDate; $currentDate += (60*60*24)){
					$day = date('j',$currentDate);
					$month = date('n',$currentDate);
					$year = date('Y',$currentDate);
					$thisUnix = mktime( 0 , 0 , 0 , $month , $day , $year ); 
					$oneDay = mysql_query("SELECT * FROM `uberbots_omni`.`calendar` WHERE (`startTime` >= '".$thisUnix."' AND `startTime` < '".($thisUnix + (60*60*24))."') OR (`endTime` >= '".$thisUnix."' AND `endTime` < '".($thisUnix + (60*60*24))."') OR (`startTime` < '".$thisUnix."' AND `endTime` >= '".($thisUnix + (60*60*24))."')");
					$e = FALSE;
					if(mysql_num_rows($oneDay) > 0)
						$list .= "<p style=\"margin-bottom:0;\"><strong>".date("l F j",$currentDate)."</strong></p>";
					while($row = mysql_fetch_array($oneDay)){
						if(!$e)
							$list .= "<ul>";
						$e = TRUE;
						
						$startTime = date("D n-j-y \a\t g:i A",$row['startTime']); //date string of start time 
						$endTime = date('D n-j-y \a\t g:i A', $row['endTime']); //date string end time
						$mentors = "";
						$mentorQuery = mysql_query("SELECT * FROM `calendarMentors` WHERE `eventId` = '".$row['id']."' AND `response` = '1'");
						$mentorNumber = mysql_num_rows($mentorQuery);
						if($user->data["user_id"]!=ANONYMOUS) //if user is logged in, show mentors attending
							$mentors = $mentorNumber;
						
						//set vars for skin
						$query = mysql_query("SELECT * FROM `calendarLinks` WHERE `eventId` = '".$row["id"]."'") or die(mysql_error());
					
					//meeting minutes part
					$minute = "";
					
					//global phpBB database stuff
					global $db;
					
					if(mysql_num_rows($query)>0){
						$minute .= "<ul>";
					}
					while($min = mysql_fetch_assoc($query)){ //returns all linked topics for today for meeting minutes
						$title = $db->sql_query("SELECT `topic_title` FROM `phpbb_topics` WHERE `topic_id` = '".$min["postId"]."' AND (`forum_id` = '".$min["forumId"]."' OR `topic_first_post_id` = '".$min["forumId"]."')");
						$titleRow = $db->sql_fetchrow($title);
						if($titleRow)
							$minute .= "<li><a href='/forums/viewtopic.php?t=".$min["postId"]."&amp;f=".$min["forumId"]."'>".$titleRow["topic_title"]."</a></li>";
						}
					if(mysql_num_rows($query)>0){
						$minute .= "</ul>";
					}
						$this->minutes = !empty($minute);
						$this->minuteURL = urlencode(date("D n-j-y",$row['startTime']));
						$loggedIn = ($user->data['user_id'] != ANONYMOUS);
						$list .= "<li>".parseSkin(array("eventId"=>$row['id'], "eventName"=>stripslashes($row['name']), "eventDescription"=>stripslashes($row['description']), "eventType"=>$row['type'], "startTime"=>$startTime, "endTime"=>$endTime, "eventLocation"=>stripslashes($row['location']), "mentors"=>$mentors, "minutes"=>$minute, "minuteURL"=>$this->minuteURL), "calEvent", array("minutes"=>$this->minutes, "loggedIn"=>$loggedIn, "list"=>TRUE))."</li>";
					}	
					if($e)
						$list .= "</ul>";
				}
			}else{
				$list = "<br/><p>No upcoming events.</p>";
			}
			$repl = array("events"=>$list, "days"=>$properties['days']);
			return parseSkin($repl,'cal_list');
		}
		
	}

	public function renderEdit($properties) {
		
		/* function renderEdit is called when a user selects this module to edit
		 * it displays forms and such for editing the module
		 * renderEdit works with the function 'edit' to update modules
		 * takes the $properties array for an arguement
		 */
		
		switch($properties['type']){
			default:
				$full = "SELECTED ";
				$week = "";
				$list = "";
				break;
			case 0:
				$full = "SELECTED ";
				$week = "";
				$list = "";
				break;
			case 1:
				$full = "";
				$week = "SELECTED ";
				$list = "";
				break;
			case 2:
				$full = "";
				$week = "";
				$list = "SELECTED";
				break;
		}
		
		
		
		return parseSkin(array("pageId"=>$properties['pageId'], "instanceId"=>$properties['instanceId'], "full"=>$full, "week"=>$week, "list"=>$list, "days"=>$properties['days']),"calEdit", array("listCal"=>($properties['type']==2?TRUE:FALSE)));
	}

	public function edit($properties) {
		
		/* edit is what actually updates the 'moduleProps' table to contain new information
		 * it should only have the SQL necessary to update the module, all visual elements should be in the renderEdit function
		 * takes the $properties array for an arguement
		*/
		
		$vars = array();
		$vars['type'] = $properties['type'];
		if($properties['type'] == 2){
			$vars['days'] = $properties['days'];
		}

		setVariables(mysql_real_escape_string($properties['pageId']),mysql_real_escape_string($properties['instanceId']),$vars);
		
		//mysql_query("UPDATE `moduleProps` SET `propValue`='".mysql_real_escape_string($properties["type"])."' WHERE `pageId` = '".mysql_real_escape_string($properties["pageId"])."' AND `instanceId` = '".mysql_real_escape_string($properties["instanceId"])."' AND `propName` = 'type'") or die(mysql_error());
	}
	
	//defines variables for default options (for when a new module is added)
	var $sqlNames, $sqlDefaults;
	
	public function setup() {
		/* the setup function is called after a module is added
		 * these values are inserted into the 'moduleProps' table for default values
		 * the calendar defaults to a one month calendar
		 */
		$this->sqlNames = array("type"); //name of SQL field for a module property
		$this->sqlDefaults = array("0"); //corresponding values for above fields
	}
	
	private function showForm($prop){
		//show or hide add event form - this function is not used
		if($prop == 0){ //full calendar - show
			$showForm = "show()";
		}else if($prop == 1){ //week calendar - hide
			$showForm = "hide()";
		}
		return $showForm;
	}

	
	function drawCal($start, $pagePermissions, $days_in_this_month, $firstDay){
		
		
		
		/* this function is what actually makes the calendar
		 * it uses multiple skin files to make all HTML aspects
		 * only PHP code for making the calendar should be in this function
		 * arguements:
		 * $start - UNIX timestamp of day to start
		 * $pagePermissions - if user is has write permissions for the page (boolean)
		 * $days_in_this_month - number of days in this month
		 * $firstDay = day of the week of the 1st of the month
		 * all of these arguements are set in the renderEdit function
		 */
		
		global $currentSkin, $user; //call global variable current skin for the skin used
		
		$time = $start;
		$output .= "<tr>";
		for($wDay = 0; $wDay < $firstDay; $wDay++){ //this for loop outputs the correct amount of blank days at the start of the month
            		$output .= parseSkin(array(),"calBlankDay"); //returns contents of 'calBlankDay' skin file
																													 
        } 
		for($day_counter = 1; $day_counter <= $days_in_this_month; $day_counter++){ //this for loop outptus days of the month
				
				$day = date('j',$time);
				$month = date('n',$time);
				$year = date('Y',$time);
				
				$event = "";
				$wDay %= 7; //day of week, numerical form

				$thisUnix = mktime( 0 , 0 , 0 , $month , $day , $year ); //UNIX timestamp of current day, used for events
				//check if there's an event on day
				/*$start = mysql_query("SELECT * FROM `uberbots_omni`.`calendar` WHERE `startTime` <= '".$thisUnix."' AND `startTime` > '".($thisUnix + (60*60*24))."'");
				$end = mysql_query("SELECT * FROM `uberbots_omni`.`calendar` WHERE `endTime` <= '".$thisUnix."' AND `endTime` > '".($thisUnix + (60*60*24))."'");
				$thru = mysql_query("SELECT * FROM `uberbots_omni`.`calendar` WHERE `startTime` < '".$thisUnix."' AND `endTime` > '".($thisUnix + (60*60*24))."'");
				*/
				$oneDay = mysql_query("SELECT * FROM `uberbots_omni`.`calendar` WHERE (`startTime` >= '".$thisUnix."' AND `startTime` < '".($thisUnix + (60*60*24))."') OR (`endTime` >= '".$thisUnix."' AND `endTime` < '".($thisUnix + (60*60*24))."') OR (`startTime` < '".$thisUnix."' AND `endTime` >= '".($thisUnix + (60*60*24))."')");
				//above line finds all events that either start, end, or go through the current day
				
				
				$dayClass = "day"; //default class is day
				while($row = mysql_fetch_array($oneDay)){
					if(strlen($row["description"])>0) //if there is a description for event, set it to $description
						$description = "".$row['description']."";
					else
						$description = "";
					
					$startTime = date("D n-j-y \a\t g:i A",$row['startTime']); //date string of start time 
					$endTime = date('D n-j-y \a\t g:i A', $row['endTime']); //date string end time
					//date format:  Thurs 1-1-70 at 12:00 AM
					
					//list event minutes
					$query = mysql_query("SELECT * FROM `calendarLinks` WHERE `eventId` = '".$row["id"]."'") or die(mysql_error());
					
					//meeting minutes part
					$minute = "";
					
					//global phpBB database stuff
					global $db;
					
					if(mysql_num_rows($query)>0){
						$minute .= "<ul>";
					}
					while($min = mysql_fetch_assoc($query)){ //returns all linked topics for today for meeting minutes
						$title = $db->sql_query("SELECT `topic_title` FROM `phpbb_topics` WHERE `topic_id` = '".$min["postId"]."' AND (`forum_id` = '".$min["forumId"]."' OR `topic_first_post_id` = '".$min["forumId"]."')");
						$titleRow = $db->sql_fetchrow($title);
						if($titleRow)
							$minute .= "<li><a href='/forums/viewtopic.php?t=".$min["postId"]."&amp;f=".$min["forumId"]."'>".$titleRow["topic_title"]."</a></li>";
						}
					if(mysql_num_rows($query)>0){
						$minute .= "</ul>";
					}
					//mentor attendance
					$mentors = "";
					$mentorQuery = mysql_query("SELECT * FROM `calendarMentors` WHERE `eventId` = '".$row['id']."' AND `response` = '1'");
					$mentorNumber = mysql_num_rows($mentorQuery);
					if($user->data["user_id"]!=ANONYMOUS) //if user is logged in, show mentors attending
						$mentors = $mentorNumber;
					
					//set vars for skin
					$this->minutes = !empty($minute);
					$this->minuteURL = urlencode(date("D n-j-y",$row['startTime']));
					$loggedIn = ($user->data['user_id'] != ANONYMOUS);
					$event .= parseSkin(array("eventId"=>$row['id'], "eventName"=>stripslashes($row['name']), "eventDescription"=>stripslashes($row['description']), "eventType"=>$row['type'], "startTime"=>$startTime, "endTime"=>$endTime, "eventLocation"=>stripslashes($row['location']), "mentors"=>$mentors, "minutes"=>$minute, "minuteURL"=>$this->minuteURL), "calEvent", array("minutes"=>$this->minutes, "loggedIn"=>$loggedIn, "list"=>FALSE));
					//above line parses skin to get the event listing ('calEvent')
					
					//set day class to event
					$dayClass = "event";
				}
				if($wDay == 0&&$this->props==0) //if end of week, add new week (or table row)
				   $output .= "</tr><tr>\n";
				

				//set more skin vars
				$todayCheck = ($day == date("j") && $month == date("n"))?true:false; //check if date is today
				$click = $pagePermissions?"selectDate(".$month.",".$day.",".$year.");":""; //if admin, select date for adding event
				$output .= parseSkin(array("click"=>$click,"month"=>$month, "day"=>$day, "year"=>$year, "class"=>$dayClass, "event"=>$event), "calDay", array("today"=>$todayCheck, "ADMIN"=>$pagePermissions,"NOT ADMIN"=>!$pagePermissions,"event"=>($dayClass=="event")));
				//above line parses regular day skin, ('calDay')
				
				
				$wDay++; //advance day of week 
				$day++; //advance date
				$time = mktime(0,0,0,$month,$day,$year);
        }
	
		$output .= "</tr>";
		
	return $output;
	}
}
