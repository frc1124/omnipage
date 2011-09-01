<?PHP
/* ******************************************************************************************
   * This code is licensed under the MIT License                                            *
   * Please see the license.txt file in the /omni directory for the full text               *
   * License text can also be found at: http://www.opensource.org/licenses/mit-license.php  *
   * Copyright (c) 2011 Avon Robotics                                                       *
   ******************************************************************************************/

/* Error page, based off of OmniCore
*  Version 0.1
*  Developed by Matt Howard & Phil Lopreiato
*/

//include 'common'.'Common' contains all necessary includes.
include "includes/common.php";

//connect to mySQL
mySQLConnect();

$menu = drawMenu();

switch($_GET["errorCode"]){
	default:
		$errorTitle = "Unknown error.";
		$errorDescription = "Frankly, we don't know the problem. We're just as confused as you are. Sorry. We're working as hard as we can to fix it. We promise.";
	break;
	case 400:
		$errorTitle = "400 Bad Request";
		$errorDescription = "<p>The request cannot be fulfilled due to bad syntax. Try refreshing, and if that doesn't work, it's probably our fault. Our bad. 
		:-O</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#4xx_Client_Error' target='_new'>here</a></p>";
		break;
	case 401:
		$errorTitle = "401 Unauthorized";
		$errorDescription = "<p>Either you shouldn't be here, we think you shouldn't be here, you haven't shown yourself worthy to be here, or we forgot you actually should be allowed here. Authorize yourself and try again, maybe it will work. Better to see this message than to get your legs cut on a barb wire fence and set off the motion detecter. Then we'll really have a problem.</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#4xx_Client_Error' target='_new'>here</a></p>";
		break;
	case 403:
		$errorTitle = "403 Forbidden";
		$errorDescription = "<p>Either you shouldn't be here or we think you shouldn't be here. Better to see this message than to get your legs cut on a barb wire fence. It would be best to leave this page before someone notices what's going on and hits the big red button. We wouldn't want that, now would we?</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/HTTP_403' target='_new'>here</a></p>";
		break;
	case 404:
		$errorTitle = "404 Not Found";
		$errorDescription = "<p>We're sorry, but we couldn't find the page you were looking for. You must have made a wrong turn somewhere or missed your exit, but you're lost. The conglomeration of tubes that is the internet can be a scary place, but don't panic! If you clicked a dead link on our website, please <a href=\"/o/about/contact_us\">contact</a> our webmaster. Until then, you can use the <a href=\"/o/sitemap\">Sitemap</a> to try and find the page you're looking for. Please just don't tell us you got this page after trying to go to the sitemap. That's quite a catch-22! In this event, it's the end of the world or little chipmunks (or mini-bears as they're often called by those who watch them for fun) have chewed through your ISP's undergroud cable, severing the once-steady flow of packets between your computer and our server. Now, think of all those sad packets causing their hosts to time out all over the interwebs. In either event, you're going to want to start stockpiling electrical tape for the bandwith war ahead. Be prepared to defend your bits and bytes for your right to surf. Wow, are you still reading this?</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/HTTP_404' target='_new'>here</a></p>";
		break;
	case 405:
		$errorTitle = "405 Method Not Allowed";
		$errorDescription= "<p>A method has beed used somewhere on this page that is not allowed. This is most likely out fault, and we're working to fix it. Keep jabbing F5 until something works. Thanks for your patience.</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#4xx_Client_Error' target='_new'>here</a></p>";
		break;
	case 406:
		$errorTitle = "406 Not Acceptable";
		$errorDescription = "<p>The requested page had some issue with accepting headers in the requres. Our fault.</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#4xx_Client_Error' target='_new'>here</a></p>";		
		break;
	case 409:
		$errorTitle = "409 Conflict";
		$errorDescription = "<p>There is some arguement going on in the code, and our server is angry. Best hunker down until the webmasters figure this out and call for reenforcements.</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#4xx_Client_Error' target='_new'>here</a></p>";
		break;
	case 410:
		$errorTitle = "410 Gone";
		$errorDescription = "<p>The requested page has been intentionally removed. It will not be back, so don't complain. We took it away for a reason.</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#4xx_Client_Error' target='_new'>here</a></p>";
		break;
	case 418:
		$errorTitle = "418 I'm a Teapot";
		$errorDescription = "<p>I'm a little teapot, short and stout. Here is my handle, here is my spout. When I get all steamed I will shout: Tip me over and pour me out.<br/>...<br/>We're sorry, we think the the website is going insane. This is either the end of the world, linux's uprising agains other computers, or April Fools Day. We really have no idea which.</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#4xx_Client_Error' target='_new'>here</a></p>";
		break;
	case 450:
		$errorTitle = "450 Blocked by Windows Parental Controls";
		$errorDescription = "<p>Someone on your computer thinks you shouldn't be seeing this page, so Windows Parental Controls has blocked access to it. We're not sure why you would block this site, but thats what the HTTP error code says, so that's what happens. We're greatly sorry.</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#4xx_Client_Error' target='_new'>here</a></p>";
		break;
	case 500:
	case 501:
	case 502:
	case 503:
	case 504:
		$errorTitle = "500 Internal Server Error";
		$errorDescription = "<p>Don't worry! You didn't do anything wrong. 99.99% (coincidentally, the same percentage of germs and evil cowboys that Purell  and Chuck Norris kill, respectively) of the time, it's our fault, or the site host's fault. Don't worry, we're working on it. The other 0.01% occurs when the hamsters in your computer get too tired to turn their wheels any longer. This will break the equations that make Windows Pinball work and things will suddenly go either a dark shade of black or very blue. In either case, <a href=\"/o/about/contact_us\">contact</a> our webmaster for assistance. Just don't shoot the messanger.</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#5xx_Server_Error' target='_new'>here</a></p>";
		break;
	case "hardware":
		$errorTitle = "Hardware Problem";
		$errorDescription = "We're sorry, there seems to be a hardware problem somewhere along the line of getting this page. There is nothing you can do about a hardware problem, and there's nothing we can do either, since the software is always right, so it therefore must be the hardware's fault. That's a shame, isn't it?";
		break;
	}

//render final output
$skinVars = array();
$skinVars["additionalHead"] = "";
$skinVars["mainColumn"] = "<h1>".$errorTitle."</h1>\n<p style=\"margin-top:1em;line-height:150%;\">".$errorDescription."</p>";
$skinVars["secondColumn"] = renderModules(0);
$skinVars["title"] = $errorTitle;
$skinVars["sessionId"] = $user->data["session_id"];
$skinVars["topMenu"] = $menu[0];
$skinVars["secondMenu"] = $menu[1];

$html = parseSkin($skinVars,"main",array("ISHOME"=>true));

echo $html;
?>