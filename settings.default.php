<?php
define('TINYIB_BOARD', "b"); // Unique identifier for this board using only letters and numbers
define('TINYIB_BOARDDESC', "TinyIB"); // Displayed at the top of every page
define('TINYIB_THREADSPERPAGE', 10); // Amount of threads shown per index page
define('TINYIB_MAXTHREADS', 100); // Oldest threads are discarded over this limit  [0 to disable]
define('TINYIB_TRUNCATE', 15); // Messages are truncated to this many lines on board index pages  [0 to disable]
define('TINYIB_PREVIEWREPLIES', 3); // Amount of replies previewed on index pages
define('TINYIB_MAXREPLIES', 0); // Maximum replies before a thread stops bumping  [0 to disable]
define('TINYIB_MAXKB', 2048); // Maximum file size in kilobytes  [0 to disable]
define('TINYIB_MAXKBDESC', "2 MB"); // Human-readable representation of the maximum file size
define('TINYIB_PIC', true); // Enable .jpg, .png and .gif image file upload
define('TINYIB_SWF', false); // Enable .swf Flash file upload
define('TINYIB_WEBM', false); // Enable .weba and .webm audio/video file upload  (see README for instructions)
define('TINYIB_MAXW', 250); // Maximum image width (reply) - Images exceeding these sizes will be thumbnailed
define('TINYIB_MAXH', 250); // Maximum image height (reply)
define('TINYIB_MAXWOP', 250); // Maximum image width (new thread)
define('TINYIB_MAXHOP', 250); // Maximum image height (new thread)
define('TINYIB_DELAY', 30); // Delay between posts to help control flooding  [0 to disable]
define('TINYIB_LOGO', ""); // Logo HTML
define('TINYIB_TRIPSEED', ""); // Enter some random text - Used when generating secure tripcodes - Must not change once set
define('TINYIB_ADMINPASS', ""); // Text entered at the manage prompt to gain administrator access
define('TINYIB_MODPASS', ""); // Moderators only have access to delete posts  ["" to disable]
define('TINYIB_REQMOD', "disable"); // Require moderation before displaying posts: disable / files / all  (see README for instructions, only MySQL is supported)
define('TINYIB_DBMODE', "flatfile"); // Choose: flatfile / mysql / mysqli / sqlite / pdo  (flatfile is not recommended for popular sites)
define('TINYIB_DBMIGRATE', false); // Enable database migration tool  (see README for instructions)

// Note: The following only apply when TINYIB_DBMODE is set to mysql, mysqli (recommended over mysql) or pdo with default (blank) TINYIB_DBDSN (this is recommended most)
define('TINYIB_DBHOST', "localhost");
define('TINYIB_DBPORT', 3306); // Set to 0 if you are using a UNIX socket as the host
define('TINYIB_DBUSERNAME', "");
define('TINYIB_DBPASSWORD', "");
define('TINYIB_DBNAME', "");
define('TINYIB_DBPOSTS', TINYIB_BOARD . "_posts");
define('TINYIB_DBBANS', "bans");

// Note: The following only apply when TINYIB_DBMODE is set to pdo  (see README for instructions)
define('TINYIB_DBDRIVER', "mysql"); // PDO driver to use (mysql / sqlite / pgsql / etc.)
define('TINYIB_DBDSN', ""); // Enter a custom DSN to override all of the connection/driver settings above  (see README for instructions)
# You should still set TINYIB_DBDRIVER appropriately when using a custom DSN
