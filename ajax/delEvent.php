<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************
   
   ******************************************************************************************
   * delEvent.php                                                                       	*
   * This file deletes an event from the calendar											*
   * Developed by Phil Lopreiato                                                            *
   * Version 0.1																			*
   ******************************************************************************************/
 
		include "../includes/common.php";
		mySQLConnect();
 
 //get cal user permissions
 $calPermissions = userPermissions(1,11);
 
if($_POST['delEvent']=="true"){
	if($calPermissions){
		if($calPermissions){
			$id = mysql_real_escape_string($_POST['id']);
			$result = mysql_query("DELETE FROM `calendar` WHERE`id`='".$id."'") or die(mysql_error());
			logEntry("Deleted event with id of ".$_POST['id'].".");
			echo "Success! ";
		}else{
			echo file_get_contents("http://www.uberbots.org/omni/error.php?errorCode=403");
		}			
	}else{
		echo file_get_contents("http://www.uberbots.org/omni/error.php?errorCode=403");
	}
}else{
	echo file_get_contents("http://www.uberbots.org/omni/error.php?errorCode=403");
}
?>