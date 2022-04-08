<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

define('TINYIB_NEWTHREAD', '0');
define('TINYIB_INDEXPAGE', false);
define('TINYIB_RESPAGE', true);
define('TINYIB_LOCKFILE', 'tinyib.lock');
define('TINYIB_WORDBREAK_IDENTIFIER', '@!@TINYIB_WORDBREAK@!@');

// Account roles
define('TINYIB_SUPER_ADMINISTRATOR', 1);
define('TINYIB_ADMINISTRATOR', 2);
define('TINYIB_MODERATOR', 3);
define('TINYIB_DISABLED', 99);

// The following are provided for backward compatibility and should not be relied upon
// Copy new settings from settings.default.php to settings.php
if (!defined('TINYIB_LOCALE')) {
	define('TINYIB_LOCALE', '');
}
if (!defined('TINYIB_BOARDTITLE')) {
	define('TINYIB_BOARDTITLE', '');
}
if (!defined('TINYIB_MANAGEKEY')) {
	define('TINYIB_MANAGEKEY', '');
}
if (!defined('TINYIB_INDEX')) {
	define('TINYIB_INDEX', 'index.html');
}
if (!defined('TINYIB_MAXREPLIES')) {
	define('TINYIB_MAXREPLIES', 0);
}
if (!defined('TINYIB_MAXNAME')) {
	define('TINYIB_MAXNAME', 75);
}
if (!defined('TINYIB_MAXEMAIL')) {
	define('TINYIB_MAXEMAIL', 320);
}
if (!defined('TINYIB_MAXSUBJECT')) {
	define('TINYIB_MAXSUBJECT', 75);
}
if (!defined('TINYIB_MAXMESSAGE')) {
	define('TINYIB_MAXMESSAGE', 8000);
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
if (!defined('TINYIB_STRIPMETADATA')) {
	define('TINYIB_STRIPMETADATA', false);
}
if (!defined('TINYIB_NOFILEOK')) {
	define('TINYIB_NOFILEOK', false);
}
if (!defined('TINYIB_CAPTCHA')) {
	define('TINYIB_CAPTCHA', '');
}
if (!defined('TINYIB_REPLYCAPTCHA')) {
	define('TINYIB_REPLYCAPTCHA', TINYIB_CAPTCHA);
}
if (!defined('TINYIB_REPORTCAPTCHA')) {
	define('TINYIB_REPORTCAPTCHA', '');
}
if (!defined('TINYIB_MANAGECAPTCHA')) {
	define('TINYIB_MANAGECAPTCHA', '');
}
if (!defined('TINYIB_REPORT')) {
	define('TINYIB_REPORT', false);
}
if (!defined('TINYIB_AUTOHIDE')) {
	define('TINYIB_AUTOHIDE', 0);
}
if (!defined('TINYIB_REQMOD')) {
	define('TINYIB_REQMOD', '');
}
if (!defined('TINYIB_BANMESSAGE')) {
	define('TINYIB_BANMESSAGE', true);
}
if (!defined('TINYIB_UPDATEBUMPED')) {
	define('TINYIB_UPDATEBUMPED', true);
}
if (!defined('TINYIB_SPOILERTEXT')) {
	define('TINYIB_SPOILERTEXT', false);
}
if (!defined('TINYIB_SPOILERIMAGE')) {
	define('TINYIB_SPOILERIMAGE', false);
}
if (!defined('TINYIB_AUTOREFRESH')) {
	define('TINYIB_AUTOREFRESH', 30);
}
if (!defined('TINYIB_CLOUDFLARE')) {
	define('TINYIB_CLOUDFLARE', false);
}
if (!defined('TINYIB_DISALLOWTHREADS')) {
	define('TINYIB_DISALLOWTHREADS', '');
}
if (!defined('TINYIB_DISALLOWREPLIES')) {
	define('TINYIB_DISALLOWREPLIES', '');
}
if (!defined('TINYIB_ALWAYSNOKO')) {
	define('TINYIB_ALWAYSNOKO', false);
}
if (!defined('TINYIB_WORDBREAK')) {
	define('TINYIB_WORDBREAK', 0);
}
if (!defined('TINYIB_EXPANDWIDTH')) {
	define('TINYIB_EXPANDWIDTH', 85);
}
if (!defined('TINYIB_TIMEZONE')) {
	define('TINYIB_TIMEZONE', '');
}
if (!defined('TINYIB_BACKLINKS')) {
	define('TINYIB_BACKLINKS', true);
}
if (!defined('TINYIB_CATALOG')) {
	define('TINYIB_CATALOG', true);
}
if (!defined('TINYIB_JSON')) {
	define('TINYIB_JSON', true);
}
if (!defined('TINYIB_DATEFMT')) {
	define('TINYIB_DATEFMT', '%g/%m/%d(%a)%H:%M:%S');
}
if (!defined('TINYIB_DBMIGRATE')) {
	define('TINYIB_DBMIGRATE', false);
}
if (!defined('TINYIB_DBACCOUNTS')) {
	define('TINYIB_DBACCOUNTS', 'accounts');
}
if (!defined('TINYIB_DBREPORTS')) {
	define('TINYIB_DBREPORTS', TINYIB_BOARD . '_reports');
}
if (!defined('TINYIB_DBKEYWORDS')) {
	define('TINYIB_DBKEYWORDS', TINYIB_BOARD . '_keywords');
}
if (!defined('TINYIB_DBLOGS')) {
	define('TINYIB_DBLOGS', 'logs');
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
	if (file_exists('tinyib.db')) {
		define('TINYIB_DBPATH', 'tinyib.db');
	} else {
		define('TINYIB_DBPATH', '.tinyib.db');
	}
}
if (!defined('TINYIB_DEFAULTSTYLE')) {
	define('TINYIB_DEFAULTSTYLE', 'futaba');
}
if (!isset($tinyib_stylesheets)) {
	$tinyib_stylesheets = array(
		'futaba' => 'Futaba',
		'burichan' => 'Burichan'
	);
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
if (!isset($tinyib_anonymous)) {
	$tinyib_anonymous = array('Anonymous');
}
if (!isset($tinyib_capcodes)) {
	$tinyib_capcodes = array(array('Admin', 'red'), array('Mod', 'purple'));
}
