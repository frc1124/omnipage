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
		$errorDescription = "<p>The request cannot be fulfilled due to bad syntax</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#4xx_Client_Error' target='_new'>here</a></p>";
		break;
	case 401:
		$errorTitle = "401 Unauthorized";
		$errorDescription = "<p>You're not authorized to see this page. Authorize yourself and try again, maybe it will work.</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#4xx_Client_Error' target='_new'>here</a></p>";
		break;
	case 403:
		$errorTitle = "403 Forbidden";
		$errorDescription = "<p>Our server doesn't allow you to visit this page.</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/HTTP_403' target='_new'>here</a></p>";
		break;
	case 404:
		$errorTitle = "404 Not Found";
		$errorDescription = "<p>The page you are looking for cannot be found. Try to find it in the <a href=\"/o/sitemap\">Sitemap</a>.</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/HTTP_404' target='_new'>here</a></p>";
		break;
	case 405:
		$errorTitle = "405 Method Not Allowed";
		$errorDescription= "<p>A method has beed used somewhere on this page that is not allowed.</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#4xx_Client_Error' target='_new'>here</a></p>";
		break;
	case 406:
		$errorTitle = "406 Not Acceptable";
		$errorDescription = "<p>The requested page had some issue with accepting headers in the requres.</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#4xx_Client_Error' target='_new'>here</a></p>";		
		break;
	case 409:
		$errorTitle = "409 Conflict";
		$errorDescription = "<p>The request could not be processed due to a conflict.</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#4xx_Client_Error' target='_new'>here</a></p>";
		break;
	case 410:
		$errorTitle = "410 Gone";
		$errorDescription = "<p>The requested page has been intentionally removed.</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#4xx_Client_Error' target='_new'>here</a></p>";
		break;
	case 500:
	case 501:
	case 502:
	case 503:
	case 504:
		$errorTitle = "500 Internal Server Error";
		$errorDescription = "<p>There has been an errro somewhere on our server. We're working to fix it.</p><br/><p>More information on this error can be found <a href='http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#5xx_Server_Error' target='_new'>here</a></p>";
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