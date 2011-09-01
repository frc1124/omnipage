<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/*Calendar Module
* version 0.1
* Developed by Phil Lopreiato
* Uses PHP Calendar script from http://scripts.franciscocharrua.com/php-calendar.php
* Includes meeting minutes and mentors integration
* Meeting minutes allow forum topics to be linked to calendar events
* Mentors integration allows mentors to show what meeting they can attend
*/

class mod_calendar {
	
	public $title = 'Calendar';
	public $description = 'creates a one-week or one-month calendar';
	public $path = 'mod_calendar';

	public function render($properties) {

		//mentor sign-in
		global $user,$db;

		//check is user is mentor
		$isMentor = $user->data["group_id"]==12?true:false;

		//add mentor to event
		if(isset($_GET["addMe"])&&$isMentor){
			if(!mysql_fetch_array(mysql_query("SELECT * FROM `calendarMentors` WHERE userId = '".$user->data["user_id"]."' AND eventId = '".mysql_real_escape_string($_GET["mentors"])."'"))) //check that mentor isn't already attending 
				$query = mysql_query("INSERT INTO `calendarMentors` VALUES ('".$user->data["user_id"]."','".mysql_real_escape_string($_GET["mentors"])."','','')") or die(mysql_error());
		}

		if(isset($_GET["mentors"])){ //show signup page
			$eventId = $_GET["mentors"];
			$output .= "<h1>Mentor Sign-in</h1>
			Mentors attending this event:
			<ul>";
			$query = mysql_query("SELECT * FROM `calendarMentors` WHERE `eventId` = '".mysql_real_escape_string($eventId)."'") or die(mysql_error());

			while($row = mysql_fetch_array($query)){
				$userQuery = $db->sql_query("SELECT `username` FROM `phpbb_users` WHERE `user_id`='".$row['userId']."'");
				$userName = $db->sql_fetchrow($userQuery);
				$output .= "<li>".$userName["username"]."</li>";
			}
			if($isMentor){
				$output .= "</ul><p><a href=\"?addMe&mentors=".urlencode($eventId)."\">Add me</a></p>";
			}
			return $output;
		}

		//get pageId/module instance/properties
		$this->pageId = $properties['pageId'];
		$this->instanceId = $properties['instanceId'];
		//module type: 0 is full cal, 1 is week cal
		$this->props = $properties['type'];
		 
		 //check if user is admin
		$pagePermissions = userPermissions(1);
		
		 //draw calender
		if(isset($_GET["date"])){ //check if start date is set
			$date = $_GET["date"];
			$day = date("j",$date);
			$month = date("m",$date);
			$month_name = date("F",$date);
			$year = date("Y",$date);
		}else{
			$date = getDate();
			$day = $date["mday"];
			$month = $date["mon"];
			$month_name = $date["month"];
			$year = $date["year"];
			}

        $this_month = getDate(mktime(0, 0, 0, $month, 1, $year));
        $next_month = getDate(mktime(0, 0, 0, $month + 1, 1, $year));
		$last_month = getDate(mktime(0, 0, 0, $month - 1, 1, $year));
		$today = getDate(mktime(0, 0, 0, $month, $day, $year)); 
		
		 $nextMonth=mktime(0,0,0,$month+1,1,$year);
		 $lastMonth=mktime(0,0,0,$month-1,1,$year);
		 
         if($this->props == 0){ //full month calendar
			 //Find out when this month starts and ends.
			 $first_week_day = $this_month["wday"];
			 $days_in_this_month = round(($next_month[0] - $this_month[0]) / (60 * 60 * 24));
			 $daysInMonth = $days_in_this_month;
		 }else if($this->props == 1){ //one week calendar
			$weekDay = $today['wday'];
			$first_week_day = 0;
			$days_in_this_month = (7);
			$daysInMonth = round(($next_month[0] - $this_month[0]) / (60 * 60 * 24));
		 }
		 
			//old ajax event add
			/*$(\"#id\").val
			$($(\"#startMonth\").val/$(\"#startDay\").val/$(\"#startYear\").val).get().innerHTML+=data;
			eventName: $('#eventName').val(),
			eventType: $('#eventType').val(),
			typeBox: $('#typeBox').val(),
			eventDescription: $('#eventDescription').val(),
			locationSelect: $('#locationSelect').val(),
			locationBox: $('#locationBox').val(),
			startDay: $('#startDay').val(),
			startHour: $('#startHour').val(),
			startMin: $('#startMin').val(),
			endDay: $('#endDay').val(),
			endHour: $('#endHour').val(),
			endMin: $('#endMin').val(),
			startPM: $('#startPM').val(),
			endPM: $('#endPM').val(),
			startMonth: $('#startMonth').val(),
			startYear: $('#startYear').val(),
			endMonth: $('#endMonth').val(),
			endYear: $('#endYear').val()
			*/
		
		//show or hide add event form
		if($this->props == 0){ //full calendar - show
			$showForm = "show()";
		}else if($this->props == 1){ //week calendar - hide
			$showForm = "hide()";
		}
	
		//administartive javascript stuff
		if($pagePermissions){
			$adminScript = 
			   "function selectDate(month,day,year){
					document.getElementById(\"startMonth\").value = month;
					document.getElementById(\"startDay\").value = day;
					document.getElementById(\"startYear\").value = year;
					
					document.getElementById(\"endMonth\").value = month;
					document.getElementById(\"endDay\").value = day;
					document.getElementById(\"endYear\").value = year;
			}

			function deleteEvent(obj,id){
				
				confi = confirm(\"Are you sure you want to delete this event?\");
				if(confi){
				$.post(\"/omni/ajax/delEvent.php\",
				{ delEvent: \"true\", id: id},
				function(data,textStatus){
					if(data==\"Success! \"){
						if($(obj).parent().parent().find('.eventLink').length==1)
						$(obj).parent().parent().attr('class','day');
						$(obj).parent().remove();
					}
					else
						alert(data+'\\n'+textStatus);
				});
				
				}}
			function addEvent(){
			$.post('/omni/ajax/addEvent.php',$('#addEventForm').serialize(),
			function(data){
					if(data.slice(0,4)==\"<spa\"){
						\$('#day_'+$('#startMonth').val()+'_'+$('#startDay').val()+'_'+$('#startYear').val()).append(data).attr('class','event');
					}else{
						alert('Returned error:'+data);
					}
				});
			return false;
			}

			$(\".eventLink\").dblclick(function(){deleteEvent(this,$(this).attr(\"id\"))});
			";
		}else{
			$adminScript = "";
		}	

		if($pagePermissions){ //admin document.ready() functions
			$jscript = "
				<script type=\"text/javascript\" src=\"http://uberbots.org/omni/skins/classic/scripts/jquery-validator.js\"></script>
				<script type=\"text/javascript\">
				$(document).ready(function(){
					$(\".eventTip\").hide();
					$(\".eventLink\").dblclick(function(){deleteEvent(this,$(this).attr(\"id\"))}
					).parent().hover(
					function(){
						$(this).children('div').show();
						},
					function(){
						$(this).children('div').hide();
					}
					);
					$('#addEventForm').submit(addEvent);
					$(\"#addEventForm\").".$showForm.";
					
					$('#addEventForm').validate({
						rules:{
							eventName: {required:true},
							eventDescriptiopn: {required:true},
							startMonth: {requried: true, digits: true, max: 12, min: 1},
							startDay: {requried: true, digits: true, max: $daysInMonth, min: 1},
							startYear: {requried: true, digits: true, maxlength: 4, min: 2009},
							endMonth: {requried: true, digits: true, max: 12, min: 1},
							endDay: {requried: true, digits: true, max: $daysInMonth, min: 1},
							endYear: {requried: true, digits: true, maxlength: 4, min: 2009},
							startMin: {required: true, digits: true, max: 59, min: 0},
							startHour: {required: true, digits: true, max: 12, min: 1}
						},
						messages: {
							eventName: {required: 'Please enter an event name'},
							eventDescription: {required: 'Please enter an event description'},
							startMonth: {requried: 'Please enter a start month', digits: 'Month has to be a number between one and 12', max: 'Month has to be a number between and and 12', min: 'Value must be 1 or greater'},
							startDay: {requried: 'Please enter a start day', digits: 'Day has to be a number between one and ".$daysInMonth."', max: 'Month has to be a number between and and ".$daysInMonth."', min: 'Value must be 1 or greater'},
							startYear: {requried: 'Please enter a start year', digits: 'Year has to be a four number long digit' , maxlength: 'Year must be 4 digits long or less', min: 'Value must be 1 or greater'},
							endMonth: {requried: 'Please enter a start month', digits: 'Month has to be a number between one and 12', max: 'Month has to be a number between and and 12', min: 'Value must be 1 or greater'},
							endDay: {requried: 'Please enter a start day', digits: 'Day has to be a number between one and ".$daysInMonth."', max: 'Month has to be a number between and and ".$daysInMonth."', min: 'Value must be 1 or greater'},
							endYear: {requried: 'Please enter a start year', digits: 'Year has to be a four number long digit' , maxlength: 'Year must be 4 digits long or less', min: 'Value must be 1 or greater'},
							startMin: {required: 'Please enter a start minute', digits: 'Minute has to be a number', max: 'Minute value can not be greater than 59', min: 'Minute value can not be less than 1'},
							startHour: {required: 'Please enter a start  hour', digits: 'Value must be a number', max: 'Value can not be greater than 12', min: 'Value can not be less than 1'}
						},
						errorPlacement: function(error, element) { 
							if ( element.is('.addMonth') ) 
								error.appendTo( element.parent().next().next().next() ); 
							else if ( element.is('.addDay') ) 
								error.appendTo ( element.next().next() ); 
							else 
								error.appendTo( element.parent() ); 
						}
					})
					});
				$adminScript
				</script>
			";
		}else{
			$jscript = "
				<script type='text/javascript'>
				$(document).ready(function(){
					$(\".eventTip\").hide();
					$(\".eventLink\").dblclick(function(){deleteEvent(this,$(this).attr(\"id\"))}
						).parent().hover(
						function(){
							$(this).children('div').show();
							},
						function(){
							$(this).children('div').hide();
						});
						});
				</script>
			";	
		}	
			
			$isAdmin = $pagePermissions?"true":"false";
			$calendar_html .= "
			<style type=\"text/css\" scoped>
			#calendar {width:100%;}
			#calendar TD {height:70px;text-align:left;vertical-align:top;color:white;padding:5px;width:14%;}
			#calendar A {color:white;text-indent:-5px;padding-left:5px;display:block;}
			#calendar A IMG {border:0px;}
			.event {background-color:#ff3333;}
			.day {background-color:#6685c2;}
			.eventTip {position:absolute;background-color:#ff6666;padding:5px;border:1px solid #bf0000;z-index: 3;text-decoration:none;color:white;max-width:300px;}
			.eventTip P {margin:0px;}
			#calendar .eventTip A {display:inline;margin:0px;text-indent:0px;}
			#headerRow TD {background-color:#6685c2;text-align:center;height:20px;font-weight:bold;vertical-align:middle;}
			</style>
			";
			
			$calendar_html .= $jscript;

		if($this->props == 0){
			$calendar_html .= "<h1>".$month_name." ".$year."</h1>
			<table id=\"monthChange\" style=\"width:100%\">
			<tr>
			<td style=\"text-align:left;width:50%;\"><a href='?date=".$lastMonth."' >Last Month</a></td>
			<td style=\"text-align:right;width:50%;\"><a href='?date=".$nextMonth."' >Next Month</a></td>
			</tr>
			</table>";
		}
		
		if($this->props == 1){ //if week cal, add link to full calendar page
			$calendar_html .= "<a href='http://www.uberbots.org/o/calendar' style='float:right;padding-top:1em;'>Go to Full Calendar</a><h1>Events this Week</h1>";
		}
		 
        $calendar_html .= "<table id=\"calendar\">\n";

		$calendar_html .= "<tr id=\"headerRow\"><td>Sunday</td><td>Monday</td><td>Tuesday</td><td>Wednesday</td><td>Thursday</td><td>Friday</td><td>Saturday</td></tr>";

        $calendar_html .= "<tr>";

        $week_day = $first_week_day;
         
		if($this->props == 0){
			$day = 1;
			//Fill the first week of the month with the appropriate number of blanks.
         		for($week_day = 0; $week_day < $first_week_day; $week_day++)
            	{
            		$calendar_html .= "<td class=\"day\"></td>\n";
            	}
		}
		if($this->props == 1){
			$day = $today['mday']-$today['wday'];
		}
		
		for($day_counter = 1; $day_counter <= $days_in_this_month; $day_counter++){
				
				$event = "";
				$week_day %= 7;

				$thisUnix = mktime( 0 , 0 , 0 , $month , $day , $year );
				//check if there's an event on day
				/*$start = mysql_query("SELECT * FROM `uberbots_omni`.`calendar` WHERE `startTime` <= '".$thisUnix."' AND `startTime` > '".($thisUnix + (60*60*24))."'");
				$end = mysql_query("SELECT * FROM `uberbots_omni`.`calendar` WHERE `endTime` <= '".$thisUnix."' AND `endTime` > '".($thisUnix + (60*60*24))."'");
				$thru = mysql_query("SELECT * FROM `uberbots_omni`.`calendar` WHERE `startTime` < '".$thisUnix."' AND `endTime` > '".($thisUnix + (60*60*24))."'");
				*/
				$oneDay = mysql_query("SELECT * FROM `uberbots_omni`.`calendar` WHERE (`startTime` >= '".$thisUnix."' AND `startTime` < '".($thisUnix + (60*60*24))."') OR (`endTime` >= '".$thisUnix."' AND `endTime` < '".($thisUnix + (60*60*24))."') OR (`startTime` < '".$thisUnix."' AND `endTime` >= '".($thisUnix + (60*60*24))."')");
				
				
				
				$dayClass = "day";
				
				while($row = mysql_fetch_array($oneDay)){
					if(strlen($row["description"])>0)
						$description = "".$row['description']."";
					else
						$description = "";
					
					$startTime = date("D n-j-y \a\t g:i A",$row['startTime']);
					$endTime = date('D n-j-y \a\t g:i A', $row['endTime']);
					
					//list event minutes
					$query = mysql_query("SELECT * FROM `calendarLinks` WHERE `eventId` = '".$row["id"]."'") or die(mysql_error());
					
					//meeting minutes part
					$minute = "";
					
					global $db;
					
					while($min = mysql_fetch_assoc($query)){
						$title = $db->sql_query("SELECT `topic_title` FROM `phpbb_topics` WHERE `topic_id` = '".$min["postId"]."' AND (`forum_id` = '".$min["forumId"]."' OR `topic_first_post_id` = '".$min["forumId"]."')");
						$titleRow = $db->sql_fetchrow($title);
						if($titleRow)
							$minute .= "<p><a href='/forums/viewtopic.php?t=".$min["postId"]."&amp;f=".$min["forumId"]."'>".$titleRow["topic_title"]."</a></p>";
						}

					$mentors = "";
					$mentorQuery = mysql_query("SELECT * FROM `calendarMentors` WHERE `eventId` = ".$row['id']."");
					$mentorNumber = mysql_num_rows($mentorQuery);
					if($user->data["user_id"]!=ANONYMOUS)
						$mentors = "<p><a href=\"/o/calendar?mentors=".$row['id']."\">Mentors (".$mentorNumber.")</a></p>";

					$event .= "<div><a href=\"javascript:void(0);\" id=\"".$row['id']."\" class=\"eventLink\">
								".(empty($minute)?"":"<img height=\"12\" src=\"/omni/skins/classic/images/minutes-small.png\">").$row['name']."</a>
								<div id=\"event_".$row['id']."\" class=\"eventTip ui-corner-all\">".$row['name']."<br>".$row['description']."<br>
								<p><b>Event Type:</b> ".$row['type']."</p>
								<p><b>Start:</b> ".$startTime."</p>
								<p><b>End:</b> ".$endTime."</p>
								<p><b>Location:</b> ".$row['location']."</p>
								$mentors
								$minute
								".($pagePermissions?"<a href=\"http://uberbots.org/forums/posting.php?mode=post&amp;f=58&amp;calendarEvent=".$row["id"]."&amp;subject=".urlencode(date("D n-j-y",$row['startTime'])." Minutes")."\">+ Add Meeting Minutes</a>":"")."</div></div>";
					
					
					$dayClass = "event";
				}
				if($week_day == 0&&$this->props!=1)
				   $calendar_html .= "</tr><tr>\n";
				

				//Do something different for the current day.
				if($day == date("j") && $month == date("n")){
					$calendar_html .= "<td id='day_".$month."_".$day."_".$year."' class=\"".$dayClass."\"";
				if($pagePermissions)
					$calendar_html .= "onclick=\"selectDate(".$month.",".$day.",".$year.");\"";
				   
				$calendar_html .= "><b>" . $day . " - Today </b><br>" . $event . "</td>\n";
				}
				//And for every other day.
				else{
				   $calendar_html .= "<td id='day_".$month."_".$day."_".$year."' class=\"".$dayClass."\"";
				if($pagePermissions)
				   $calendar_html .= "onclick='selectDate(".$month.",".$day.",".$year.")'";
				   
				   
				$calendar_html .= ">".$day." <br> ".$event." </td>\n";
				}
				$week_day++;
				$day++;
        }

        $calendar_html .= "</tr>\n";
        $calendar_html .= "</table>\n<br/>";
		if($this->props ==1){
			if($pagePermissions){
				$calendar_html .= "<a href='javascript:void(0)' onclick='$(\"#addEventForm\").toggle()' id='addFormToggle'>Add an Event</a>";
			}
		}
		//if admin, add addEvent form
		 if($pagePermissions){
			
			$calendar_html .= "
			<form id=\"addEventForm\" name=\"addEventForm\"><fieldset>
			<h3>Add Event</h3>
			<p>Click on a day to select it.<br>
			All fields are required.</p>
			</fieldset><fieldset>
			<p>
			<!--Event name text box-->
			<label for=\"eventName\" style=\"width:150px;display:inline-block;\">Event Name:</label><input type=\"text\" name=\"eventName\" id=\"eventName\"></p>

			<!--Event type select-->
			<p>
			<label for=\"eventType\" style=\"width:150px;display:inline-block;\">Event Type:</label><select name=\"eventType\" id=\"eventType\" style=\"min-width:150px;\" onchange=\"if(this.value=='other')document.getElementById('typeBox').style.display=''; else document.getElementById('typeBox').style.display='none';\">
			<option value=\"\"></option>
			<option value=\"Meeting\">Meeting</option>
			<option value=\"Competition\">Competition</option>
			<option value=\"Fundraiser\">Fundraiser</option>
			<option value=\"other\">Other Event</option>
			</select> <input id=\"typeBox\" name=\"typeBox\" type=\"text\" style=\"display:none\" value=\"Enter Event Type\">
			</p>

			<!--Event Description-->
			<p>
			<label for=\"eventDescription\" style=\"width:150px;display:inline-block;\">Event Description: </label><input type=\"text\" name=\"eventDescription\" id=\"eventDescription\">
			</p>

			<!--Event Location Select-->
			<p>
			<label for=\"eventLocation\" style=\"width:150px;display:inline-block;\">Event Location:</label><select name=\"locationSelect\" id=\"locationSelect\" onchange=\"if(this.value=='other')document.getElementById('locationBox').style.display=''; else document.getElementById('locationBox').style.display='none';\">
			<option value=\"Avon High School\">Avon High School</option>
			<option value=\"other\">Other</option>
			</select> <label for=\"locationBox\"></label><input name=\"locationBox\" id=\"locationBox\" type=\"text\" style=\"display:none\" value=\"Enter location\"/>
			</p>


			<!--Event Date-->
			<br>
			<div id=\"startDate\">
			<label for=\"startDate\" style=\"width:150px;display:inline-block;\">Start Date:</label><input type=\"text\" name=\"startMonth\" id=\"startMonth\" class = \"addMonth\" size=\"1\" maxlength=\"2\" value=\"MM\"> <input type=\"text\" name=\"startDay\" id=\"startDay\" class=\"addDay\" size=\"1\" maxlength=\"2\" value=\"DD\"> <input type=\"text\" name=\"startYear\" id=\"startYear\" size=\"2\" maxlength=\"4\" value=\"YYYY\"></div> <div id=\"endDate\"><label for=\"endDate\" style=\"width:150px; display:inline-block;\">End Date: </label><input type=\"text\" name=\"endMonth\" id=\"endMonth\" class=\"addMonth\" size=\"1\" maxlength=\"2\" value=\"MM\"> <input type=\"text\" name=\"endDay\" id=\"endDay\" class=\"addDay\" size=\"1\" maxlength=\"2\" value=\"DD\"> <input type=\"text\" name=\"endYear\" id=\"endYear\" size=\"2\" maxlength=\"4\" value=\"YYYY\"></div>
			<br>

			<!--Event Times-->
			<p>
			<label for=\"startTime\" style=\"width:150px;display:inline-block;\">Start Time:</label><input type=\"text\" onfocus=\"if(this.value=='HH')this.value=''\" name=\"startHour\" id=\"startHour\" size=\"1\" maxlength=\"2\" value=\"HH\"> : 
			<input type=\"text\" name=\"startMin\" id=\"endMin\" size=\"1\" maxlength=\"2\" onfocus=\"if(this.value=='MM')this.value=''\" value=\"MM\"> <input type=\"checkbox\" name=\"startPM\" id=\"startPM\" value=\"1\"> PM<br/>
			<label for=\"endTime\" style=\"width:150px;display:inline-block;\">End Time: </label><input type=\"text\" name=\"endHour\" id=\"endHour\" onfocus=\"if(this.value=='HH')this.value=''\" size=\"1\" maxlength=\"2\" value=\"HH\"> : 
			<input type=\"text\" name=\"endMin\" id=\"endMin\" size=\"1\" maxlength=\"2\" onfocus=\"if(this.value=='MM')this.value=''\" value=\"MM\"> <input type=\"checkbox\" name=\"endPM\" id=\"endPM\" value=\"1\"> PM
			</p>
			<p>
			<input type=\"hidden\" name=\"type\" value=\"addEvent\">
			<input type=\"hidden\" name=\"modType\" value=\"".$this->props."\" id=\"modType\">
			<input type=\"hidden\" name=\"addEvent\" id=\"addEvent\" value=\"true\">
			<input type=\"hidden\" name=\"days_in_this_month\" name=\"days_in_this_month\" value=\"".$days_in_this_month."\">
			<input type=\"submit\" name=\"submitButton\" id=\"submitButton\" value=\"Add Event\">

			</p>
			</fieldset>
			<!--<input type=\"button\" value=\"Add Event\" name=\"addEventButton\" id=\"addEventButton\" onclick=\"addEvent()\">--></form>
			";
		
		}
		 
		//output calendar
        return($calendar_html);

		
	}

	public function renderEdit($properties) {
		if($properties['type'] == 0){
			$full = "SELECTED ";
			$week = "";
		}else{
			$full = "";
			$week = "SELECTED ";
		}
		$editMod = "<h2>Select Calendar Type</h2><br>
		<select id='calSelect_".$properties["pageId"]."_".$properties["instanceId"]."'><option ".$full."value='0'>Full Month Calendar</option><option ".$week."value='1'>One Week Calendar</option></select><br>
		<button onclick=\"saveMod(".$properties["pageId"].",".$properties["instanceId"].",{type:$('#calSelect_".$properties["pageId"]."_".$properties["instanceId"]."').val()})\">Save</button>";
		return $editMod;
	}

	public function edit($properties) {
		mysql_query("UPDATE `moduleProps` SET `propValue`='".mysql_real_escape_string($properties["type"])."' WHERE `pageId` = '".mysql_real_escape_string($properties["pageId"])."' AND `instanceId` = '".mysql_real_escape_string($properties["instanceId"])."' AND `propName` = 'type'") or die(mysql_error());
	}
	
	
	var $sqlNames, $sqlDefaults;
	
	public function setup() {
		$this->sqlNames = array("type");
		$this->sqlDefaults = array("0");
	}
}
