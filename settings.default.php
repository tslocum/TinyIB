<?php
define('TINYIB_BOARD', "b"); // Unique identifier for this board using only letters and numbers
define('TINYIB_BOARDDESC', "TinyIB"); // Displayed in the logo area
define('TINYIB_MAXTHREADS', 100); // Set this to limit the number of threads allowed before discarding older threads.  0 to disable
define('TINYIB_LOGO', ""); // Logo HTML
define('TINYIB_TRIPSEED', ""); // Text to use when generating secure tripcodes
define('TINYIB_ADMINPASS', ""); // Text entered at the manage prompt to gain administrator access
define('TINYIB_MODPASS', ""); // Same as above, but only has access to delete posts. Blank ("") to disable
define('TINYIB_DBMODE', "flatfile"); // flatfile / mysql / sqlite

// mysql settings - only edit if using mysql
define('TINYIB_DBHOST', "localhost");
define('TINYIB_DBUSERNAME', "");
define('TINYIB_DBPASSWORD', "");
define('TINYIB_DBNAME', "");
define('TINYIB_DBPOSTS', TINYIB_BOARD . "_posts");
define('TINYIB_DBBANS', "bans");
?>