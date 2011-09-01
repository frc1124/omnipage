<?
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************
   
   ******************************************************************************************
   * addDirectory.php                                                                       *
   * This file creates a directory in a specified place on the server (used for file module *
   * Developed by Matt Howard	                                                            *
   * Version 0.1																			*
   ******************************************************************************************/

include "../includes/common.php";
mySQLConnect();

if(!userPermissions(1,12))
exit;

mkdir($_POST["location"]."/New Folder");

logEntry("Added directory in ".$_POST["location"]);
?>