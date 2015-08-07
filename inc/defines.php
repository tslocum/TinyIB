<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

define('TINYIB_NEWTHREAD', '0');
define('TINYIB_INDEXPAGE', false);
define('TINYIB_RESPAGE', true);

// The following are provided for backward compatibility and should not be relied upon
// Copy new settings from settings.default.php to settings.php
if (!defined('TINYIB_MAXREPLIES')) {
	define('TINYIB_MAXREPLIES', 0);
}
if (!defined('TINYIB_MAXWOP')) {
	define('TINYIB_MAXWOP', TINYIB_MAXW);
}
if (!defined('TINYIB_MAXHOP')) {
	define('TINYIB_MAXHOP', TINYIB_MAXH);
}
if (!defined('TINYIB_PIC')) {
	define('TINYIB_PIC', true);
}
if (!defined('TINYIB_SWF')) {
	define('TINYIB_SWF', false);
}
if (!defined('TINYIB_WEBM')) {
	define('TINYIB_WEBM', false);
}
if (!defined('TINYIB_EMBED')) {
	define('TINYIB_EMBED', false);
}
if (!defined('TINYIB_THUMBNAIL')) {
	define('TINYIB_THUMBNAIL', 'gd');
}
if (!defined('TINYIB_NOFILEOK')) {
	define('TINYIB_NOFILEOK', false);
}
if (!defined('TINYIB_CAPTCHA')) {
	define('TINYIB_CAPTCHA', false);
}
if (!defined('TINYIB_REQMOD')) {
	define('TINYIB_REQMOD', 'disable');
}
if (!defined('TINYIB_DBMIGRATE')) {
	define('TINYIB_DBMIGRATE', false);
}
if (!defined('TINYIB_DBPORT')) {
	define('TINYIB_DBPORT', 3306);
}
if (!defined('TINYIB_DBDRIVER')) {
	define('TINYIB_DBDRIVER', 'pdo');
}
if (!defined('TINYIB_DBDSN')) {
	define('TINYIB_DBDSN', '');
}
