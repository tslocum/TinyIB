<?php
$tinyib = array();
$tinyib['board']            = "b"; // Unique identifier for this board using only letters and numbers
$tinyib['boarddescription'] = "TinyIB"; // Displayed in the logo area
$tinyib['maxthreads']       = 100; // Set this to limit the number of threads allowed before discarding older threads.  0 to disable
$tinyib['logo']             = ""; // Logo HTML
$tinyib['tripseed']         = ""; // Text to use when generating secure tripcodes
$tinyib['adminpassword']    = ""; // Text entered at the manage prompt to gain administrator access
$tinyib['modpassword']      = ""; // Same as above, but only has access to delete posts. Blank ("") to disable
$tinyib['databasemode']     = "flatfile"; // flatfile or mysql

// mysql settings
$mysql_host = "localhost";
$mysql_username = "";
$mysql_password = "";
$mysql_database = "";
$mysql_posts_table = $tinyib['board'] . "_posts";
$mysql_bans_table = "bans";
?>