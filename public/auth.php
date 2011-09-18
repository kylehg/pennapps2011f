<?php 
require_once('db.php');

define('FB_APP_ID', '152699918152812');
define('FB_SECRET', 'f6cb20d2c321943562a3302342c8cfea');
define('FB_REDIRECT_URL', 'http://pm.kylehardgrave.com/');

function create_user($id) {
  $sql = "INSERT INTO users (id) VALUES ($id)";
  run_sql($sql);
}

function has_visited($id) {
  $sql = "SELECT * FROM users WHERE id = $id";
  $result = run_sql($sql);
  if (mysql_num_rows($result) === 1) {
    return true;
  } else {
    return false;
  }
}

session_start();

if ($_SESSION['user']) {
  $user = $_SESSION['user'];
  if (!has_visited($user->id)) {
    create_user($user->id);
  }
  
} else {
  if ($_GET['login'] === 1) {
//$code = $_REQUEST["code"];
//if(empty($code)) {
//  $_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
    $dialog_url = "https://www.facebook.com/dialog/oauth?client_id=" . FB_APP_ID .
      "&redirect_uri=" . urlencode(FB_REDIRECT_URL) . "&state=" . $_SESSION['state'];
    echo("<script> top.location.href='" . $dialog_url . "'</script>");
  }
  
  if ($_GET['code']) {
    $og_code = $_GET['code'];
    $token_url = 'https://graph.facebook.com/oauth/access_token?client_id=' . FB_APP_ID . '&redirect_uri=' . FB_REDIRECT_URL . '&client_secret=' . FB_SECRET . '&code=' . $og_code;
    $response = file_get_contents($token_url);
    $params = null;
    parse_str($response, $params);
    $og_url = 'https://graph.facebook.com/me?access_token=' . $params['access_token'];
    $_SESSION['user'] = json_decode(file_get_contents($og_url));
    $user = $_SESSION['user'];
  }
}


/*
if($_REQUEST['state'] == $_SESSION['state']) {
 $token_url = "https://graph.facebook.com/oauth/access_token?" . "client_id=" .
 FB_APP_ID . "&redirect_uri=" . urlencode(FB_REDIRECT_URL) . "&client_secret=" . 
 FB_SECRET . "&code=" . $code;

 $response = file_get_contents($token_url);
 $params = null;
 parse_str($response, $params);

 $graph_url = "https://graph.facebook.com/me?access_token=" 
   . $params['access_token'];

 $user = json_decode(file_get_contents($graph_url));
 if (!has_visited($user->id)) {
    create_user($user->id);
 }
} else {
 echo("The state does not match. You may be a victim of CSRF.");
}
}

/*
if ($_GET['code']) {
  $og_code = $_GET['code'];
  $token_url = 'https://graph.facebook.com/oauth/access_token?client_id=' . FB_APP_ID . '&redirect_uri=' . FB_REDIRECT_URL . '&client_secret=' . FB_SECRET . '&code=' . $og_code;
  $response = file_get_contents($token_url);
  $params = null;
  parse_str($response, $params);
  $og_url = 'https://graph.facebook.com/me?access_token=' . $params['access_token'];
  $user = json_decode(file_get_contents($og_url));
  echo $user->id;
  }*/
    

?>