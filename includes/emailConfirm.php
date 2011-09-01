<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************
 
   ****************************************************************************************** 
   * emailConform.php                                                                       *
   * This file defines functions for the double-opt-in email list                           *
   * Developed by Phil Lopreiato                                                            *
   * Version 0.1                                                                            *
   ******************************************************************************************/
 
function sendConfirmation($email){
	//this function sends a confirmation email for a given email address
	$subject = "Please Confirm Your Email Address"; //email subject
	$headers = 'MIME-Version: 1.0' . "\r\n" .							//set other email headers (MIME version)
			   'Content-type: text/html; charset=iso-8859-1'."\r\n".	//content type
			   'X-Mailer: PHP/' . phpversion() ."\r\n".					//set PHP mailer
			   'From: noreply@uberbots.org'."\r\n";						//set 'from' email address
	$message = "Thank you for signing up for the &Uuml;berBots' mass email list. In order to activate your email address, please click the following link: <br/>".
				"<a href='http://uberbots.org/omni/actions/emailList.php?confirm&email=".$email."'>Click to Confirm Your Email Address</a><br/>".
				"<br/><p>This is an automated email message sent because you have signed up for &Uuml;berBots email updates. If you later wish to stop recieving mass emails, please email <a href='mailto:unsuscribe@uberbots.org?Subject=Unsuscribe'>unsuscribe@uberbots.org</a>. If you have other questions or comments, please email the <a href='mailto:webmaster@uberbots.org'>webmaster</a>. Please do not reply to this message.</p>";		   
	
	mail($email,$subject,$message,$headers);
}
?>