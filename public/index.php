<?php

include_once('auth.php');

function print_tags($tags) {
  $str = '';
  $length = count($tags);
  for ($i = 0; $i < $length; $i++) {
    if ($tags[$i] == 'beer') 
      continue;
    $str .= $tags[$i];
    if ($i == $length - 2)
      $str .= ', and ';
    elseif ($i < $length - 2)
      $str .= ', ';
  }
  return $str;
}

function hunch_query($query) {
  return json_decode(file_get_contents($query))->recommendations[0];
}

function get_user_history($id) {
  $sql = 'SELECT * FROM preferences WHERE user_id = '. $id;
  $result = run_sql($sql);
  $user_history = array();
  $num_rows = mysql_num_rows($result);
  for ($i = 0; $i < $num_rows; $i++) {
    $history_item = array(
      'beer' => mysql_result($result, $i, 'beer_id'),
      'rel'  => mysql_result($result, $i, 'rel')
    );
    $user_history[] =  $history_item;
  }
//    print '<pre>'; print_r($user_history); print '</pre>';
  return $user_history;
}

function build_user_query($user_history) {
  $likes = '';
  $dislikes = '';
  $exclude = '';  //Things we've seen already.
  for ($i = 0; $i < count($user_history); $i++) {
     $history_item = $user_history[$i];
/*    echo '<pre>';
    print_r($history_item);
    echo '</pre>';*/
    if ($history_item['rel'] == 1) {
      $likes .= $history_item['beer'] . ',';
    } elseif ($history_item['rel'] == -1) {
      $dislikes .= $history_item['beer'] . ',';
    } elseif ($history_item['rel'] == 0) {
      $exclude .= $history_item['beer'] . ',';
    }
  }
  $likes = substr($likes,0,-1);
  $dislikes = substr($dislikes,0,-1);
  $exclude = substr($exclude,0,-1);
  $str =  'exclude_likes=1&exclude_dislikes&blocked_result_ids=' . $exclude .
         '&likes=' . $likes . '&dislikes=' . $dislikes;
//  echo $str . '<br/>';
  return $str;
}

$hunch_root_url = 'http://api.hunch.com/api/v1/get-recommendations/?topic_ids=list_beer&limit=1&';

if ($user) {
  $user_id = 'fb_' . $user->id;
}

if ($_GET['action']) {
  
  if ($_GET['action'] == 'up') {
    $rel = 1;
  } elseif ($_GET['action'] == 'down') {
    $rel = -1;
  } elseif ($_GET['action'] == 'next') {
    $rel = 0;
  }
  
  $sql ='INSERT INTO preferences (user_id, beer_id, rel) VALUES (' . $user->id . 
        ', "' . $_GET['last'] . '", ' . $rel . ');';
//  echo $sql;
  run_sql($sql);

}
  
if ($user_id) {
// If someone's logged in:
  $user_hist = get_user_history($user->id);
//  print_r($user_hist);
  $user_query = $hunch_root_url . 'user_id=' . $user_id . '&' . 
                build_user_query($user_hist);
//  echo $user_query;
  $result = hunch_query($user_query);
  
} else {

  $result = hunch_query($hunch_root_url);      

}
  

$beer = array(
  'name'        => $result->name,
  'img_src'     => $result->image_url,
  'description' => $result->description,
  'tags'        => $result->tags,
  'h_id'         => $result->result_id
);



?>


<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!-- Consider adding a manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
  <meta charset="utf-8">

  <!-- Use the .htaccess and remove these lines to avoid edge case issues.
       More info: h5bp.com/b/378 -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title>Beer Snob</title>
  <meta name="description" content="">
  <meta name="author" content="">

  <!-- Mobile viewport optimized: j.mp/bplateviewport -->
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

  <link rel="stylesheet" href="css/style.css">
  
  <!-- More ideas for your <head> here: h5bp.com/d/head-Tips -->

  <!-- All JavaScript at the bottom, except this Modernizr build incl. Respond.js
       Respond is a polyfill for min/max-width media queries. Modernizr enables HTML5 elements & feature detects; 
       for optimal performance, create your own custom Modernizr build: www.modernizr.com/download/ -->
  <script src="js/libs/modernizr-2.0.6.min.js"></script>
  <script type="text/javascript" src="http://use.typekit.com/lua6zxk.js"></script>
  <script type="text/javascript">try{Typekit.load();}catch(e){}</script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.3/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="js/libs/jquery-1.6.3.min.js"><\/script>')</script>

</head>
<!-- Bacon ipsum dolor sit amet sirloin pancetta beef ribs ball tip, shoulder hamburger tri-tip short ribs. Short loin shank meatball, jowl bresaola ribeye meatloaf filet mignon turducken drumstick pig. Pork loin tail pork chop, tri-tip ribeye corned beef spare ribs jowl ham shankle. Pork loin ham beef ribs andouille turkey.-->
<body>

<div id="all">
  <header id="masthead">
    <hgroup id="branding">
      <!--<img src="img/headerbar.png"/>-->
    <?php if (!$user) { ?>
    <a id="signinbutton" href="https://www.facebook.com/dialog/oauth?client_id=<?= FB_APP_ID ?>&redirect_uri=<?= FB_REDIRECT_URL ?>">
	    <img src="img/signinbutton.png"/>
	  </a>
	  <?php } else {?>
	    <p id="signinbutton">Welcome, <?= $user->name ?></p>
    <?php } ?>
	  <a id="history" href="history.html">History</a>
    </hgroup><!--#branding-->
    <h1><strong><?php if($user->name) { echo $user->name; } else {?>Your<?php }?></strong><?php if($user->name) {?>&rsquo;s<?php } ?> Beers</h1>
    <div id="user-tab">
      <!-- TODO: -->
    </div><!--/#user-tab-->
  </header><!--/#masthead-->
  <!--<div id="main">-->
	<div id="container">
		<nav id="controls">
		  <ul>
<?php if (!$user_id) { ?>
<script>
$(document).ready(function() {
$("#controls li a").click(function(e) {
  e.preventDefault();
  alert("You must be logged into Facebook!");
});
});
</script>
<?php } ?>
			<li id="up-btn"><a href="/?action=up&last=<?= $beer['h_id'] ?>"><img alt="Cheers!" src="img/up.png"/></a></li>
			<li id="next-btn"><a href="/?action=next&last=<?= $beer['h_id'] ?>"><img alt="Next &raquo;" src="img/arrow.png"/></a></li>
			<li id="dn-btn"><a href="/?action=down&last=<?= $beer['h_id'] ?>"><img alt="Blech!" src="img/down.png"/></a></li>
		  </ul>
		</nav><!--/#controls-->
		<div id="beer-view">
		  <div id="namebox">
		    <div class="img box_round box_shadow">
			  <img src="<?= $beer['img_src'] ?>" height="130" width="130" alt="Beer Image"/>
		    </div><!--/.img-->
			<h2><?= $beer['name'] ?></h2>
		  </div><!--/#namebox-->
		  <div class="description box_round box_shadow">
			<img id="glass" src="img/med.png"/>
			<p><?= $beer['description']; ?></p>
			<p>This beer is: <?= print_tags($beer['tags']) ?></p>
		  </div><!--/.description-->
		</div><!--/#beer-view-->
	</div><!--/#container-->
  <!--</div>--><!--/#main-->
  <footer id="all-the-rest">
    <p id="copyright">Copyright &copy; 2011 Kyle Hardgrave, Jennie Shapira, Dave Sharples, <i class="amp">&amp;</i> Kevin Shen. We ♡ <a href="http://pennapps.com/">PennApps</a>.</p>
  </footer>
</div><!--/#all-->


<!-- SCRIPTS -->


<script defer src="js/plugins.js"></script>
<script defer src="js/script.js"></script>


<script type="text/javascript">
  var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview'],['_trackPageLoadTime']];
  (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
  g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
  s.parentNode.insertBefore(g,s)}(document,'script'));
</script>

<!--[if lt IE 7 ]>
  <script defer src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
  <script defer>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
<![endif]-->

</body>
</html>
