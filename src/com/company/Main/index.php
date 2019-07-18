<? php

//parte 1: definizione

require_once('lib/OAuth.php');
require_once('lib/twitteroauth.php');

define('CONSUMER_KEY', '4mEq9HTFWJIsVDAWnh0rOPPaj');
define('CONSUMER_SECRET', 'DhZlMbuMWYlSZZoM636CShzCC4JW5DyXLkVDkql84rCAamTwWJ');
define('OAUTH_CALLBACK', 'https://www.facebook.com/biagio.dipalma96');

session_start();

//parte 2: processo
//1. gestire il logout
if(isset($_GET['logout'])){
	session_unset();
	$redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
	header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}

//2. gestire la sessione utente: se la sessione non è abilitata, prendi la url del login
if(!isset($SESSION['data']) && !isset($_GET('oauth_token'])) {
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
	$request_token = $connection->getRequestToken(OAUTH_CALLBACK);
	
	if($request_token){
		$token = $request_token['oauth_token'];
		$_SESSION['request_token'] = $token;
		$_SESSION['request_token_secret'] = $request_token['oauth_token_secret'];
		
		$login_url = $connection->getAuthorizeURL($token);
	}
}

//3. gestire la callback
if(isset($_GET['oauth_token'])){
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['request_token'], $_SESSION['request_token_secret']);

	$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
	if($access_token){
		$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
		$params = array('include_entities'=>'false');
		$data = $connection->get('account/verify_credentials',$params);
		if($data){
			$_SESSION['data']=$data;
			$redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
			header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
		}
	}
}
	
//parte 3: front end: bottone login con twitter, mostra il nome, username e la foto, poi bottone per il logout
if(isset($login_url) && !isset($_SESSION['data'])){
	echo "<a href='$login_url'><button>Login with twitter </button></a>";
}else{
	$data = $_SESSION['data'];
	echo "Name: ".$data->name."<br>";
	echo "Username: ".$data->screen_name."<br>";
	echo "Photo : <img src='".$data->profile_image_url."'/><br><br>";
	
	echo "<a href='?Logout=true'><button>Logout</button></a>";
}

?>