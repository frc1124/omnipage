<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************
   
   ******************************************************************************************
   * logArch.php																			*
   * This file clears the security.txt log file and puts the contents into a new file		*
   * Developed by Phil Lopreiato                                                            *
   * Version 0.1																			*
   ******************************************************************************************/

include "../includes/common.php";
global $user;

$isAdmin = $user->data["group_id"]==5?true:false;
$isMan = $user->data["group_id"]==10?true:false;

if(($_SERVER['REMOTE_ADDR']=="") || ((($isMan || $isAdmin) && (isset($_GET['cpanel'])) && userPermissions(0,2)))){

$date = date("m-Y",mktime(0,0,0,date("m")-1,date("d"),date("Y"))); //get date stamp for last month
$logPath = "../logs/security.txt"; //log file path
$newPath = "../logs/".$date.".txt"; //new file path

if(file_exists($newPath))
	$newPath = "../logs/".$date." - ".time().".txt";

$rename = rename($logPath, $newPath); //moves file with new name
if(!$rename){
	echo "Error";
}else{
	logEntry("Logs Archived - ".date(U));
	header("location: http://uberbots.org/o/control_panel");
}
}else{
	echo file_get_contents('http://uberbots.org/omni/error.php?errorCode=401');
}
?>