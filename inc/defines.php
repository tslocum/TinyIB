<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

define('TINYIB_NEWTHREAD', '0');
define('TINYIB_INDEXPAGE', false);
define('TINYIB_RESPAGE', true);
define('TINYIB_WORDBREAK_IDENTIFIER', '@!@TINYIB_WORDBREAK@!@');

// The following are provided for backward compatibility and should not be relied upon
// Copy new settings from settings.default.php to settings.php
if (!defined('TINYIB_LOCALE')) {
	define('TINYIB_LOCALE', '');
}
if (!defined('TINYIB_INDEX')) {
	define('TINYIB_INDEX', 'index.html');
}
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
if (!defined('TINYIB_UPLOADVIAURL')) {
    define('TINYIB_UPLOADVIAURL', false);
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
if (!defined('TINYIB_WORDBREAK')) {
	define('TINYIB_WORDBREAK', 0);
}
if (!defined('TINYIB_TIMEZONE')) {
	define('TINYIB_TIMEZONE', '');
}
if (!defined('TINYIB_CATALOG')) {
	define('TINYIB_CATALOG', true);
}
if (!defined('TINYIB_JSON')) {
	define('TINYIB_JSON', true);
}
if (!defined('TINYIB_DATEFMT')) {
	define('TINYIB_DATEFMT', 'y/m/d(D)H:i:s');
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
if (!defined('TINYIB_DBPATH')) {
	define('TINYIB_DBPATH', 'tinyib.db');
}
if (!isset($tinyib_hidefieldsop)) {
	$tinyib_hidefieldsop = array();
}
if (!isset($tinyib_hidefields)) {
	$tinyib_hidefields = array();
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
		$tinyib_uploads['audio/webm'] = array('webm');
	}
}
if (!isset($tinyib_embeds)) {
	$tinyib_embeds = array();
	if (defined('TINYIB_EMBED') && TINYIB_EMBED) {
		$tinyib_embeds['SoundCloud'] = 'https://soundcloud.com/oembed?format=json&url=TINYIBEMBED';
		$tinyib_embeds['Vimeo'] = 'https://vimeo.com/api/oembed.json?url=TINYIBEMBED';
		$tinyib_embeds['YouTube'] = 'https://www.youtube.com/oembed?url=TINYIBEMBED&format=json';
	}
}
if (!isset($tinyib_capcodes)) {
	$tinyib_capcodes = array(array('Admin', 'red'), array('Mod', 'purple'));
}
