<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

// Accounts table
define('ACCOUNTS_FILE', '.accounts');
define('ACCOUNT_ID', 0);
define('ACCOUNT_USERNAME', 1);
define('ACCOUNT_PASSWORD', 2);
define('ACCOUNT_ROLE', 3);
define('ACCOUNT_LASTACTIVE', 4);

// Bans table
define('BANS_FILE', '.bans');
define('BAN_ID', 0);
define('BAN_IP', 1);
define('BAN_TIMESTAMP', 2);
define('BAN_EXPIRE', 3);
define('BAN_REASON', 4);

// Keywords table
define('KEYWORDS_FILE', '.keywords');
define('KEYWORD_ID', 0);
define('KEYWORD_TEXT', 1);
define('KEYWORD_ACTION', 2);

// Log table
define('LOGS_FILE', '.logs');
define('LOG_ID', 0);
define('LOG_TIMESTAMP', 1);
define('LOG_ACCOUNT', 2);
define('LOG_MESSAGE', 3);

// Posts table
define('POSTS_FILE', '.posts');
define('POST_ID', 0);
define('POST_PARENT', 1);
define('POST_TIMESTAMP', 2);
define('POST_BUMPED', 3);
define('POST_IP', 4);
define('POST_NAME', 5);
define('POST_TRIPCODE', 6);
define('POST_EMAIL', 7);
define('POST_NAMEBLOCK', 8);
define('POST_SUBJECT', 9);
define('POST_MESSAGE', 10);
define('POST_PASSWORD', 11);
define('POST_FILE', 12);
define('POST_FILE_HEX', 13);
define('POST_FILE_ORIGINAL', 14);
define('POST_FILE_SIZE', 15);
define('POST_FILE_SIZE_FORMATTED', 16);
define('POST_IMAGE_WIDTH', 17);
define('POST_IMAGE_HEIGHT', 18);
define('POST_THUMB', 19);
define('POST_THUMB_WIDTH', 20);
define('POST_THUMB_HEIGHT', 21);
define('POST_STICKIED', 22);
define('POST_LOCKED', 23);
define('POST_MODERATED', 24);

// Reports table
define('REPORTS_FILE', '.reports');
define('REPORT_ID', 0);
define('REPORT_IP', 1);
define('REPORT_POST', 2);

require_once 'flatfile/flatfile.php';
$db = new Flatfile();
$db->datadir = 'inc/database/flatfile/';
// Search past default database path
if (file_exists('inc/flatfile/' . POSTS_FILE)) {
	$db->datadir = 'inc/flatfile/';
}

if (function_exists('insertPost')) {
	function migrateAccount($a) {
		$account = array();
		$account[ACCOUNT_ID] = $a['id'];
		$account[ACCOUNT_USERNAME] = $a['username'];
		$account[ACCOUNT_PASSWORD] = $a['password'];
		$account[ACCOUNT_ROLE] = $a['role'];
		$account[ACCOUNT_LASTACTIVE] = $a['lastactive'];
		$GLOBALS['db']->insertWithAutoId(ACCOUNTS_FILE, ACCOUNT_ID, $account);
	}

	function migrateBan($b) {
		$ban = array();
		$ban[BAN_ID] = $b['id'];
		$ban[BAN_IP] = $b['ip'];
		$ban[BAN_TIMESTAMP] = $b['timestamp'];
		$ban[BAN_EXPIRE] = $b['expire'];
		$ban[BAN_REASON] = $b['reason'];
		$GLOBALS['db']->insertWithAutoId(BANS_FILE, BAN_ID, $ban);
	}

	function migrateKeyword($k) {
		$keyword = array();
		$keyword[KEYWORD_ID] = $k['id'];
		$keyword[KEYWORD_TEXT] = $k['text'];
		$keyword[KEYWORD_ACTION] = $k['action'];
		$GLOBALS['db']->insertWithAutoId(KEYWORDS_FILE, KEYWORD_ID, $keyword);
	}

	function migrateLog($l) {
		$log = array();
		$log[LOG_ID] = $l['id'];
		$log[LOG_TIMESTAMP] = $l['timestamp'];
		$log[LOG_ACCOUNT] = $l['account'];
		$log[LOG_MESSAGE] = $l['message'];
		$GLOBALS['db']->insertWithAutoId(LOGS_FILE, LOG_ID, $log);
	}

	function migratePost($p) {
		$post = array();
		$post[POST_ID] = $p['id'];
		$post[POST_PARENT] = $p['parent'];
		$post[POST_TIMESTAMP] = $p['timestamp'];
		$post[POST_BUMPED] = $p['bumped'];
		$post[POST_IP] = $p['ip'];
		$post[POST_NAME] = $p['name'];
		$post[POST_TRIPCODE] = $p['tripcode'];
		$post[POST_EMAIL] = $p['email'];
		$post[POST_NAMEBLOCK] = $p['nameblock'];
		$post[POST_SUBJECT] = $p['subject'];
		$post[POST_MESSAGE] = $p['message'];
		$post[POST_PASSWORD] = $p['password'];
		$post[POST_FILE] = $p['file'];
		$post[POST_FILE_HEX] = $p['file_hex'];
		$post[POST_FILE_ORIGINAL] = $p['file_original'];
		$post[POST_FILE_SIZE] = $p['file_size'];
		$post[POST_FILE_SIZE_FORMATTED] = $p['file_size_formatted'];
		$post[POST_IMAGE_WIDTH] = $p['image_width'];
		$post[POST_IMAGE_HEIGHT] = $p['image_height'];
		$post[POST_THUMB] = $p['thumb'];
		$post[POST_THUMB_WIDTH] = $p['thumb_width'];
		$post[POST_THUMB_HEIGHT] = $p['thumb_height'];
		$post[POST_MODERATED] = $p['moderated'];
		$post[POST_STICKIED] = $p['stickied'];
		$post[POST_LOCKED] = $p['locked'];
		$GLOBALS['db']->insertWithAutoId(POSTS_FILE, POST_ID, $post);
	}

	function migrateReport($r) {
		$report = array();
		$report[REPORT_ID] = $r['id'];
		$report[REPORT_IP] = $r['ip'];
		$report[REPORT_POST] = $r['post'];
		$GLOBALS['db']->insertWithAutoId(REPORTS_FILE, REPORT_ID, $report);
	}
}
