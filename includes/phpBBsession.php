<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* phpBB Session Intergration
*  version 0.1
*  developed by Matt Howard, Phil Lopreiato
*  for reference: http://www.phpbb.com/kb/article/phpbb3-sessions-integration/
*/

	define('IN_PHPBB', true);
	$phpbb_root_path = "$root_path/../forums/";
	$phpEx = substr(strrchr(__FILE__, '.'), 1);
	include($phpbb_root_path . 'common.' . $phpEx);
	
	//make local variables global
	global $user, $auth;
	
	// Start session management
	$user->session_begin();
	$auth->acl($user->data);
	$user->setup();

function parseBB($text){
	$uid = $bitfield = $options = ''; // will be modified by generate_text_for_storage
	$allow_bbcode = $allow_urls = $allow_smilies = true;
	generate_text_for_storage($text, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);
	$text = generate_text_for_display($text, $uid, $bitfield, $options);
	return $text;
	}

?>