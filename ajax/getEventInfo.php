<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/
   
/* /ajax/getEventInfo.php
 * version 0.1
 * Developed by Phil Lopreiato
 * Gets infomation about an event and returns that to calendar javascript. used in event editing
 */

$root_path = "/home1/uberbots/public_html/omni";
include "$root_path/includes/common.php";
mySQLConnect();

$calPermissions = userPermissions(1,11);
if($calPermissions && $_POST['getInfo'] == "true"){
	getInfo($_POST['id']);
}
else{
	echo file_get_contents("http://www.uberbots.org/omni/error.php?errorCode=403");
}

function getInfo($id){
	$data = "";
	$query = mysql_query("SELECT * FROM `calendar` WHERE `id` = '".mysql_real_escape_string($id)."'");
	while($row = mysql_fetch_assoc($query)){
		foreach($row as $key => $value){
			switch($key){
				case "name":
					$data .= "#eventName: ".$value.",";
					break;
				case "description":
					$data .= "#eventDescription: ".$value.",";
					break;
				case "type":
					$data .= "#typeBox: ".$value.",";
					break;
				case "startTime";
					$sD = date("j",$value);
					$sM = date("m",$value);
					$sY = date("Y",$value);
					$sMin = date("i",$value);
					$sH = date("g",$value);
					$s24 = date("H",$value);
					$sPM = $e24>=12?"true":"false";
					$data .= "#startMonth: ".$sM.",#startDay: ".$sD.",#startYear: ".$sY.",#startHour: ".$sH.",#startMin: ".$sMin.",#startPM: ".$sPM.",";
					break;
				case "endTime":
					$eD = date("j",$value);
					$eM = date("m",$value);
					$eY = date("Y",$value);
					$eMin = date("i",$value);
					$eH = date("g",$value);
					$e24 = date("H",$value);
					$ePM = $e24>=12?"true":"false";
					$data .= "#endMonth: ".$eM.",#endDay: ".$eD.",#endYear: ".$eY.",#endHour: ".$eH.",#endMin: ".$eMin.",#endPM: ".$ePM.",";
					break;
				case "location": 
					$data .= "#locationBox: ".$value.",";
					break;
			}
		}
	}
	$data .= "#locationSelect: other,";
	$data .= "#eventType: other";
	echo $data;
}

?>