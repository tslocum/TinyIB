<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

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

// Bans table
define('BANS_FILE', '.bans');
define('BAN_ID', 0);
define('BAN_IP', 1);
define('BAN_TIMESTAMP', 2);
define('BAN_EXPIRE', 3);
define('BAN_REASON', 4);

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
	function migratePost($newpost) {
		$post = array();
		$post[POST_ID] = $newpost['id'];
		$post[POST_PARENT] = $newpost['parent'];
		$post[POST_TIMESTAMP] = $newpost['timestamp'];
		$post[POST_BUMPED] = $newpost['bumped'];
		$post[POST_IP] = $newpost['ip'];
		$post[POST_NAME] = $newpost['name'];
		$post[POST_TRIPCODE] = $newpost['tripcode'];
		$post[POST_EMAIL] = $newpost['email'];
		$post[POST_NAMEBLOCK] = $newpost['nameblock'];
		$post[POST_SUBJECT] = $newpost['subject'];
		$post[POST_MESSAGE] = $newpost['message'];
		$post[POST_PASSWORD] = $newpost['password'];
		$post[POST_FILE] = $newpost['file'];
		$post[POST_FILE_HEX] = $newpost['file_hex'];
		$post[POST_FILE_ORIGINAL] = $newpost['file_original'];
		$post[POST_FILE_SIZE] = $newpost['file_size'];
		$post[POST_FILE_SIZE_FORMATTED] = $newpost['file_size_formatted'];
		$post[POST_IMAGE_WIDTH] = $newpost['image_width'];
		$post[POST_IMAGE_HEIGHT] = $newpost['image_height'];
		$post[POST_THUMB] = $newpost['thumb'];
		$post[POST_THUMB_WIDTH] = $newpost['thumb_width'];
		$post[POST_THUMB_HEIGHT] = $newpost['thumb_height'];
		$post[POST_MODERATED] = $newpost['moderated'];
		$post[POST_STICKIED] = $newpost['stickied'];
		$post[POST_LOCKED] = $newpost['locked'];
		$GLOBALS['db']->insertWithAutoId(POSTS_FILE, POST_ID, $post);
	}

	function migrateBan($newban) {
		$ban = array();
		$ban[BAN_ID] = $newban['id'];
		$ban[BAN_IP] = $newban['ip'];
		$ban[BAN_TIMESTAMP] = $newban['timestamp'];
		$ban[BAN_EXPIRE] = $newban['expire'];
		$ban[BAN_REASON] = $newban['reason'];
		$GLOBALS['db']->insertWithAutoId(BANS_FILE, BAN_ID, $ban);
	}

	function migrateReport($newreport) {
		$report = array();
		$report[REPORT_ID] = $newreport['id'];
		$report[REPORT_IP] = $newreport['ip'];
		$report[REPORT_POST] = $newreport['post'];
		$GLOBALS['db']->insertWithAutoId(REPORTS_FILE, REPORT_ID, $report);
	}
}
