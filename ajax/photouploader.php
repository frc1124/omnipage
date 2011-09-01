<?php
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

//----------------------------------------------
//    partitioned upload file handler script
//----------------------------------------------

include "../includes/common.php";

mySQLConnect();

ob_start();

//check if has permission to upload
//if(!userPermissions(0,14)
//exit;

//
//    specify upload directory - storage 
//    for reconstructed uploaded files
$upload_dir = "../media/photos/";

//
//    specify stage directory - temporary storage 
//    for uploaded partitions
$stage_dir = "../media/photos/";

//
//    retrieve request parameters
$file_param_name = 'file';
$file_name = $_FILES[ $file_param_name ][ 'name' ];
$file_id = $_POST[ 'fileId' ];
$partition_index = $_POST[ 'partitionIndex' ];
$partition_count = $_POST[ 'partitionCount' ];
$file_length = $_POST[ 'fileLength' ];

//
//    the $client_id is an essential variable, 
//    this is used to generate uploaded partitions file prefix, 
//    because we can not rely on 'fileId' uniqueness in a 
//    concurrent environment - 2 different clients (applets) 
//    may submit duplicate fileId. thus, this is responsibility 
//    of a server to distribute unique clientId values
//    (or other variable, for example this could be GET id) 
//    for instantiated applets.
$client_id = $_GET[ 'clientId' ];

//
//    move uploaded partition to the staging folder 
//    using following name pattern:
//    ${clientId}.${fileId}.${partitionIndex}
$source_file_path = $_FILES[ $file_param_name ][ 'tmp_name' ];
$target_file_path = $stage_dir . $client_id . "." . $file_id . 
    "." . $partition_index;
if( !move_uploaded_file( $source_file_path, $target_file_path ) ) {
    echo "Error:Can't move uploaded file";
    return;
}

//
//    check if we have collected all partitions properly
$all_in_place = true;
$partitions_length = 0;
for( $i = 0; $all_in_place && $i < $partition_count; $i++ ) {
    $partition_file = $stage_dir . $client_id . "." . $file_id . "." . $i;
    if( file_exists( $partition_file ) ) {
        $partitions_length += filesize( $partition_file );
    } else {
        $all_in_place = false;
    }
}

//
//    issue error if last partition uploaded, but partitions validation failed
if( $partition_index == $partition_count - 1 &&
        ( !$all_in_place || $partitions_length != intval( $file_length ) ) ) {
    echo "Error:Upload validation error";
    return;
}

//
//    reconstruct original file if all ok
if( $all_in_place ) {
    $file = $upload_dir . $client_id . "." . $file_id;
    $file_handle = fopen( $file, 'w' );
    for( $i = 0; $all_in_place && $i < $partition_count; $i++ ) {
        //
        //    read partition file
        $partition_file = $stage_dir . $client_id . "." . $file_id . "." . $i;
        $partition_file_handle = fopen( $partition_file, "rb" );
        $contents = fread( $partition_file_handle, filesize( $partition_file ) );
        fclose( $partition_file_handle );
        //
        //    write to reconstruct file
        fwrite( $file_handle, $contents );
        //
        //    remove partition file
        unlink( $partition_file );		
		
    }
    fclose( $file_handle );
    //
    // rename to original file
    // NB! This may overwrite existing file
    $filename = $upload_dir . $file_name;
    rename($file,$filename);
	
		//add photo sql entries
		
	//check if new album
	if($_GET['albumName']=="new"){
		$albumName = mysql_real_escape_string($_GET['newName']);
		$year = mysql_real_escape_string($_GET["year"]);
	}else{
		$spl = explode("_",mysql_real_escape_string($_GET['albumName']));
		$albumName = $spl[0];
		$year = $spl[1];
	}
	
	//first, check if an album exists and if so get the id
	$query = mysql_query("SELECT * FROM `photos` WHERE `title` = '".$albumName."' AND `year` = '".$year."'");
	if(!($row = mysql_fetch_array($query))){
		$newId = mysql_fetch_array(mysql_query("SELECT * FROM `photos` ORDER BY `photoId` DESC"));
		$newId = $newId["photoId"]+1;
		//if not, create one
		
		mysql_query("INSERT INTO  `uberbots_omni`.`photos` VALUES ('',  '".$newId."',  '0',  '1',  '".$albumName."','".mysql_real_escape_string($_GET["description"])."',  '".$year."')") or die(mysql_error());
		$row = array("photoId"=>$newId);
	}
	
	echo $albumName;
	
	$q = mysql_query("SELECT * FROM `photos` WHERE `title` = '".$albumName."' AND `year` = '".$year."'");
	$ar = mysql_fetch_array($q);
	
	$parent = $ar['photoId'];
	
	$newId = mysql_fetch_array(mysql_query("SELECT * FROM `photos` ORDER BY `photoId` DESC"));
	$newId = $newId["photoId"]+1;
	
	logEntry("uploaded picture '".$file_name."' to album '".$albumName."' from ".$year."");
	
	mysql_query("INSERT INTO  `uberbots_omni`.`photos` VALUES ('".mysql_real_escape_string($file_name)."',  '".$newId."',  '".$parent."',  '0',  '','',  '')") or die(mysql_error());

	
}
//
//	below is trace of request variables
?>
<html>
<body>
	<h1>GET content</h1>
	<pre><?print_r( $_GET );?></pre>
	<h1>POST content</h1>
	<pre><?print_r( $_POST );?></pre>
	<h1>FILES content</h1>
	<pre><?print_r( $_FILES );?></pre>
</body>
</html>
<?
ob_end_flush();
?>