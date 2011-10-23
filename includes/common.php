<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* Common
*  version 0.1
*  developed by Matt Howard, Phil Lopreiato
*/

//root path
$root_path = "your/root/path/omni";

//domain
$domain = "http://yourDomain.com";

//includes
include "$root_path/includes/mysql.php";
include "$root_path/includes/module.php";
include "$root_path/includes/skinParser.php";
include "$root_path/includes/page.php";
include "$root_path/includes/security.php";
include "$root_path/includes/menu.php";
include "$root_path/includes/modHistory.php";

//initalize phpBB session
include "phpBBsession.php";


//set current skin or use default
$currentSkin = isset($_GET["skin"])?$_GET["skin"]:"classic";

//set default timezone
date_default_timezone_set("America/New_York");

//set char encoding
header("Content-Type: text/html;charset=UTF-8");
mysql_set_charset("utf8");

?>