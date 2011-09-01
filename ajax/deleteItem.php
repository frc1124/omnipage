<?
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************
   
   ******************************************************************************************
   * deleteItem.php                                                                       	*
   * This file deletes a given file or directory from a given location on the server		*
   * Developed by Matt Howard	                                                            *
   * Version 0.1																			*
   ******************************************************************************************/

include "../includes/common.php";
mySQLConnect();

if(!userPermissions(1,12))
exit;

if(is_dir($_POST["location"]))
rmdir($_POST["location"]);
else
unlink($_POST["location"]);

logEntry("Deleted file ".$_POST["location"]);
?>