<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

$root_path = "/home1/uberbots/public_html/omni";
include "$root_path/includes/common.php";

mySQLConnect();

switch($_GET['mode']){
case "pageHistory":
	echo pageHistory($_GET['page']);
	break;
case "moduleHistory":
	echo getEditHistory($_GET['page'],$_GET['mod']);
	break;
case "restorePage":
	echo restorePage($_GET['page']);
	break;
}
?>