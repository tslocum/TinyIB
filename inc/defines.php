<?php
if (!defined('TINYIB_BOARD')) { die(''); }

define('TINYIB_NEWTHREAD', '0');
define('TINYIB_INDEXPAGE', false);
define('TINYIB_RESPAGE', true);

// The following are provided for backward compatibility and should not be relied upon
// Copy new settings from settings.default.php to settings.php
if (!defined('TINYIB_MAXREPLIES')) { define('TINYIB_MAXREPLIES', 0); }
if (!defined('TINYIB_MAXWOP')) { define('TINYIB_MAXWOP', TINYIB_MAXW); }
if (!defined('TINYIB_MAXHOP')) { define('TINYIB_MAXHOP', TINYIB_MAXH); }
?>