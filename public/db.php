<?php
/**
 * Database Tools
**/

define('DB_HN', 'mysql.kylehardgrave.com');
define('DB_DB', 'pa_beersnob');
define('DB_UN', 'pennapps2011');
define('DB_PW', 'allidoiswin');

function run_sql($query) {
  $connection = mysql_connect(DB_HN, DB_UN, DB_PW);
  mysql_select_db(DB_DB) or die( "Unable to select database");
  if (!($result = mysql_query($query, $connection))) {
    echo '<p><strong>MySQL Error:</strong>'.mysql_error().'</p>';
    echo "<p>Query: $query</p>";
    exit(1);
  }
  mysql_close();
  return $result;
}

?>