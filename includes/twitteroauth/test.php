<?
include "../common.php";
require_once( $root_path.'/includes/twitteroauth/twitteroauth.php' ); 	
		
define("CONSUMER_KEY", "bgviT8n3zlLDf1T8mzkIUg");
define("CONSUMER_SECRET", "4f1ay4eYAZJ8EUuGb0me7jipE4VJu4ggojxy3y9rc");
define("OAUTH_TOKEN", "134827411-2CeWeWLdmVzGXuRMbNBrW7zgbzEIOED4JBCIm23k");
define("OAUTH_SECRET", "aJLPywofIgRrl0pDL0Vuk5AOdzxpJJYQnNVCleSRyDg");
 
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_SECRET);
$content = $connection->get('account/verify_credentials');

$content = $connection->get('account/rate_limit_status');
echo "Current API hits remaining: {$content->remaining_hits}.";

?>