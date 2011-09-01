<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************
   
   ******************************************************************************************
   * ical.php                                                                               *
   * This file creates a .ics (iCal file) for exprort via the calendar                      *
   * Developed by Phil Lopreiato                                                            *
   * Version 0.1																			*
   ******************************************************************************************/

header( 'Content-Type: text/calendar; charset=utf-8' );
header( 'Content-Disposition: attachment; filename=uberbotsCal.ics' );
header( 'Cache-Control: max-age=10' );

include "../includes/common.php";

mySQLConnect();

//begin ical
echo "BEGIN:VCALENDAR
METHOD:PUBLISH
VERSION:2.0
PRODID:-//Avon Robotics//OmniCore//EN
X-WR-TIMEZONE:America/New_York
X-WR-CALNAME:UberBots Calendar
CALSCALE:GREGORIAN";
if($_POST['exportType'] == "all")
	$query = mysql_query("SELECT * FROM `calendar`") or die(mysql_error());

if($_POST['exportType'] == "future")
	$query = mysql_query("SELECT * FROM `calendar` WHERE startTime >= ".time()) or die(mysql_error());
	
if($_POST['exportType'] == "range"){
	$start = mktime(0,0,0,mysql_real_escape_string($_POST['sMonth']),mysql_real_escape_string($_POST['sDay']),mysql_real_escape_string($_POST['sYear']));
	$end = mktime(0,0,0,mysql_real_escape_string($_POST['eMonth']),mysql_real_escape_string($_POST['eDay']),mysql_real_escape_string($_POST['eYear']));
	$query = mysql_query("SELECT * FROM `calendar` WHERE startTime >= ".$start." AND endTime <= ".$end) or die(mysql_error());
	}

if($_POST['exportType'] == "single")
	$query = mysql_query("SELECT * FROM `calendar` WHERE id = ".mysql_real_escape_string($_POST['eventID'])) or die(mysql_error());
	
if($_GET['type']=="single")
	$query = mysql_query("SELECT * FROM `calendar` WHERE id = ".mysql_real_escape_string($_GET['id'])) or die(mysql_error());
	
	//add info for each event
	while($row = mysql_fetch_array($query)){

	echo "\nBEGIN:VEVENT
UID:".date("Ymd\THi00",$row["startTime"])."-".$row["id"]."@uberbots.org
DTSTAMP:".date("Ymd\THi00",$row["startTime"])."
DTSTART;VALUE=DATE:".date("Ymd\THi00",$row["startTime"])."
DTEND;VALUE=DATE:".date("Ymd\THi00",$row["endTime"])."
SUMMARY:".$row['name']."
URL:http://uberbots.org/o/calendar?viewEvent&amp;eventId=".$row['id']."	
LOCATION:".$row['location']."
X-GOOGLE-CALENDAR-CONTENT-TITLE:".$row['name']."
X-GOOGLE-CALENDAR-CONTENT-URL:http://www.uberbots.org/o/calendar?viewEvent&amp;eventId=".$row['id']."
X-GOOGLE-CALENDAR-CONTENT-TYPE:text/html
END:VEVENT";

		}
	echo "\n";


?>
END:VCALENDAR