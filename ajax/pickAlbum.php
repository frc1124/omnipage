<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* lists and adds modules AJAX support
*  version 0.1
*  developed by Matt Howard, Phil Lopreiato
*/

//include common
include "../includes/common.php";

mySQLConnect();

$_SESSION["albumName"] = $_POST["albumName"];
$_SESSION["description"] = $_POST["description"];
$_SESSION["year"] = $_POST["year"];

?>
success