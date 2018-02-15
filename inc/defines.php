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
if (!defined('TINYIB_THUMBNAIL')) {
	define('TINYIB_THUMBNAIL', 'gd');
}
if (!defined('TINYIB_NOFILEOK')) {
	define('TINYIB_NOFILEOK', false);
}
if (!defined('TINYIB_CAPTCHA')) {
	define('TINYIB_CAPTCHA', '');
}
if (!defined('TINYIB_REQMOD')) {
	define('TINYIB_REQMOD', '');
}
if (!defined('TINYIB_ALWAYSNOKO')) {
	define('TINYIB_ALWAYSNOKO', false);
}
if (!defined('TINYIB_TIMEZONE')) {
	define('TINYIB_TIMEZONE', '');
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
if (!isset($tinyib_uploads)) {
	$tinyib_uploads = array();
	if (defined('TINYIB_PIC') && TINYIB_PIC) {
		$tinyib_uploads['image/jpeg'] = array('jpg');
		$tinyib_uploads['image/pjpeg'] = array('jpg');
		$tinyib_uploads['image/png'] = array('png');
		$tinyib_uploads['image/gif'] = array('gif');
	}
	if (defined('TINYIB_SWF') && TINYIB_SWF) {
		$tinyib_uploads['application/x-shockwave-flash'] = array('swf', 'swf_thumbnail.png');
	}
	if (defined('TINYIB_WEBM') && TINYIB_WEBM) {
		$tinyib_uploads['video/webm'] = array('webm');
		$tinyib_uploads['adio/webm'] = array('webm');
	}
}
if (!isset($tinyib_embeds)) {
	$tinyib_embeds = array();
	if (defined('TINYIB_EMBED') && TINYIB_EMBED) {
		$tinyib_embeds['SoundCloud'] = 'http://soundcloud.com/oembed?format=json&url=TINYIBEMBED';
		$tinyib_embeds['Vimeo'] = 'http://vimeo.com/api/oembed.json?url=TINYIBEMBED';
		$tinyib_embeds['YouTube'] = 'http://www.youtube.com/oembed?url=TINYIBEMBED&format=json';
	}
}
