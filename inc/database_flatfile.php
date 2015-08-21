<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

# Post Structure
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

# Ban Structure
define('BANS_FILE', '.bans');
define('BAN_ID', 0);
define('BAN_IP', 1);
define('BAN_TIMESTAMP', 2);
define('BAN_EXPIRE', 3);
define('BAN_REASON', 4);

require_once 'flatfile/flatfile.php';
$db = new Flatfile();
$db->datadir = 'inc/flatfile/';

# Post Functions
function uniquePosts() {
	return 0; // Unsupported by this database option
}

function postByID($id) {
	return convertPostsToSQLStyle($GLOBALS['db']->selectWhere(POSTS_FILE, new SimpleWhereClause(POST_ID, '=', $id, INTEGER_COMPARISON), 1), true);
}

function threadExistsByID($id) {
	$compClause = new AndWhereClause();
	$compClause->add(new SimpleWhereClause(POST_ID, '=', $id, INTEGER_COMPARISON));
	$compClause->add(new SimpleWhereClause(POST_PARENT, '=', 0, INTEGER_COMPARISON));

	return count($GLOBALS['db']->selectWhere(POSTS_FILE, $compClause, 1)) > 0;
}

function insertPost($newpost) {
	$post = array();
	$post[POST_ID]                  = '0';
	$post[POST_PARENT]              = $newpost['parent'];
	$post[POST_TIMESTAMP]           = time();
	$post[POST_BUMPED]              = time();
	$post[POST_IP]                  = $newpost['ip'];
	$post[POST_NAME]                = $newpost['name'];
	$post[POST_TRIPCODE]            = $newpost['tripcode'];
	$post[POST_EMAIL]               = $newpost['email'];
	$post[POST_NAMEBLOCK]           = $newpost['nameblock'];
	$post[POST_SUBJECT]             = $newpost['subject'];
	$post[POST_MESSAGE]             = $newpost['message'];
	$post[POST_PASSWORD]            = $newpost['password'];
	$post[POST_FILE]                = $newpost['file'];
	$post[POST_FILE_HEX]            = $newpost['file_hex'];
	$post[POST_FILE_ORIGINAL]       = $newpost['file_original'];
	$post[POST_FILE_SIZE]           = $newpost['file_size'];
	$post[POST_FILE_SIZE_FORMATTED] = $newpost['file_size_formatted'];
	$post[POST_IMAGE_WIDTH]         = $newpost['image_width'];
	$post[POST_IMAGE_HEIGHT]        = $newpost['image_height'];
	$post[POST_THUMB]               = $newpost['thumb'];
	$post[POST_THUMB_WIDTH]         = $newpost['thumb_width'];
	$post[POST_THUMB_HEIGHT]        = $newpost['thumb_height'];
	$post[POST_STICKIED]            = $newpost['stickied'];

	return $GLOBALS['db']->insertWithAutoId(POSTS_FILE, POST_ID, $post);
}

function stickyThreadByID($id, $setsticky) {
	$rows = $GLOBALS['db']->selectWhere(POSTS_FILE, new SimpleWhereClause(POST_ID, '=', $id, INTEGER_COMPARISON), 1);
	if (count($rows) > 0) {
		foreach ($rows as $post) {
			$post[POST_STICKIED] = intval($setsticky);
			$GLOBALS['db']->updateRowById(POSTS_FILE, POST_ID, $post);
		}
	}
}

function bumpThreadByID($id) {
	$rows = $GLOBALS['db']->selectWhere(POSTS_FILE, new SimpleWhereClause(POST_ID, '=', $id, INTEGER_COMPARISON), 1);
	if (count($rows) > 0) {
		foreach ($rows as $post) {
			$post[POST_BUMPED] = time();
			$GLOBALS['db']->updateRowById(POSTS_FILE, POST_ID, $post);
		}
	}
}

function countThreads() {
	$rows = $GLOBALS['db']->selectWhere(POSTS_FILE, new SimpleWhereClause(POST_PARENT, '=', 0, INTEGER_COMPARISON));
	return count($rows);
}

function convertPostsToSQLStyle($posts, $singlepost = false) {
	$newposts = array();
	foreach ($posts as $oldpost) {
		$post = newPost();
		$post['id']                  = $oldpost[POST_ID];
		$post['parent']              = $oldpost[POST_PARENT];
		$post['timestamp']           = $oldpost[POST_TIMESTAMP];
		$post['bumped']              = $oldpost[POST_BUMPED];
		$post['ip']                  = $oldpost[POST_IP];
		$post['name']                = $oldpost[POST_NAME];
		$post['tripcode']            = $oldpost[POST_TRIPCODE];
		$post['email']               = $oldpost[POST_EMAIL];
		$post['nameblock']           = $oldpost[POST_NAMEBLOCK];
		$post['subject']             = $oldpost[POST_SUBJECT];
		$post['message']             = $oldpost[POST_MESSAGE];
		$post['password']            = $oldpost[POST_PASSWORD];
		$post['file']                = $oldpost[POST_FILE];
		$post['file_hex']            = $oldpost[POST_FILE_HEX];
		$post['file_original']       = $oldpost[POST_FILE_ORIGINAL];
		$post['file_size']           = $oldpost[POST_FILE_SIZE];
		$post['file_size_formatted'] = $oldpost[POST_FILE_SIZE_FORMATTED];
		$post['image_width']         = $oldpost[POST_IMAGE_WIDTH];
		$post['image_height']        = $oldpost[POST_IMAGE_HEIGHT];
		$post['thumb']               = $oldpost[POST_THUMB];
		$post['thumb_width']         = $oldpost[POST_THUMB_WIDTH];
		$post['thumb_height']        = $oldpost[POST_THUMB_HEIGHT];
		$post['stickied']            = isset($oldpost[POST_STICKIED]) ? $oldpost[POST_STICKIED] : 0;

		if ($post['parent'] == '') {
			$post['parent'] = TINYIB_NEWTHREAD;
		}

		if ($singlepost) {
			return $post;
		}
		$newposts[] = $post;
	}
	return $newposts;
}

function allThreads() {
	$rows = $GLOBALS['db']->selectWhere(POSTS_FILE, new SimpleWhereClause(POST_PARENT, '=', 0, INTEGER_COMPARISON), -1, array(new OrderBy(POST_STICKIED, DESCENDING, INTEGER_COMPARISON), new OrderBy(POST_BUMPED, DESCENDING, INTEGER_COMPARISON)));
	return convertPostsToSQLStyle($rows);
}

function numRepliesToThreadByID($id) {
	$rows = $GLOBALS['db']->selectWhere(POSTS_FILE, new SimpleWhereClause(POST_PARENT, '=', $id, INTEGER_COMPARISON));
	return count($rows);
}

function postsInThreadByID($id, $moderated_only = true) {
	$compClause = new OrWhereClause();
	$compClause->add(new SimpleWhereClause(POST_ID, '=', $id, INTEGER_COMPARISON));
	$compClause->add(new SimpleWhereClause(POST_PARENT, '=', $id, INTEGER_COMPARISON));

	$rows = $GLOBALS['db']->selectWhere(POSTS_FILE, $compClause, -1, new OrderBy(POST_ID, ASCENDING, INTEGER_COMPARISON));
	return convertPostsToSQLStyle($rows);
}

function postsByHex($hex) {
	$rows = $GLOBALS['db']->selectWhere(POSTS_FILE, new SimpleWhereClause(POST_FILE_HEX, '=', $hex, STRING_COMPARISON), 1);
	return convertPostsToSQLStyle($rows);
}

function latestPosts($moderated = true) {
	$rows = $GLOBALS['db']->selectWhere(POSTS_FILE, NULL, 10, new OrderBy(POST_TIMESTAMP, DESCENDING, INTEGER_COMPARISON));
	return convertPostsToSQLStyle($rows);
}

function deletePostByID($id) {
	$posts = postsInThreadByID($id, false);
	foreach ($posts as $post) {
		if ($post['id'] != $id) {
			deletePostImages($post);
			$GLOBALS['db']->deleteWhere(POSTS_FILE, new SimpleWhereClause(POST_ID, '=', $post['id'], INTEGER_COMPARISON));
		} else {
			$thispost = $post;
		}
	}

	if (isset($thispost)) {
		if ($thispost['parent'] == 0) {
			@unlink('res/' . $thispost['id'] . '.html');
		}
		deletePostImages($thispost);
		$GLOBALS['db']->deleteWhere(POSTS_FILE, new SimpleWhereClause(POST_ID, '=', $thispost['id'], INTEGER_COMPARISON));
	}
}

function trimThreads() {
	if (TINYIB_MAXTHREADS > 0) {
		$numthreads = countThreads();
		if ($numthreads > TINYIB_MAXTHREADS) {
			$allthreads = allThreads();
			for ($i = TINYIB_MAXTHREADS; $i < $numthreads; $i++) {
				deletePostByID($allthreads[$i]['id']);
			}
		}
	}
}

function lastPostByIP() {
	$rows = $GLOBALS['db']->selectWhere(POSTS_FILE, new SimpleWhereClause(POST_IP, '=', $_SERVER['REMOTE_ADDR'], STRING_COMPARISON), 1, new OrderBy(POST_ID, DESCENDING, INTEGER_COMPARISON));
	return convertPostsToSQLStyle($rows, true);
}

# Ban Functions
function banByID($id) {
	return convertBansToSQLStyle($GLOBALS['db']->selectWhere(BANS_FILE, new SimpleWhereClause(BAN_ID, '=', $id, INTEGER_COMPARISON), 1), true);
}

function banByIP($ip) {
	return convertBansToSQLStyle($GLOBALS['db']->selectWhere(BANS_FILE, new SimpleWhereClause(BAN_IP, '=', $ip, STRING_COMPARISON), 1), true);
}

function allBans() {
	$rows = $GLOBALS['db']->selectWhere(BANS_FILE, NULL, -1, new OrderBy(BAN_TIMESTAMP, DESCENDING, INTEGER_COMPARISON));
	return convertBansToSQLStyle($rows);
}

function convertBansToSQLStyle($bans, $singleban = false) {
	$newbans = array();
	foreach ($bans as $oldban) {
		$ban = array();
		$ban['id'] = $oldban[BAN_ID];
		$ban['ip'] = $oldban[BAN_IP];
		$ban['timestamp'] = $oldban[BAN_TIMESTAMP];
		$ban['expire'] = $oldban[BAN_EXPIRE];
		$ban['reason'] = $oldban[BAN_REASON];

		if ($singleban) {
			return $ban;
		}
		$newbans[] = $ban;
	}
	return $newbans;
}

function insertBan($newban) {
	$ban = array();
	$ban[BAN_ID] = '0';
	$ban[BAN_IP] = $newban['ip'];
	$ban[BAN_TIMESTAMP] = time();
	$ban[BAN_EXPIRE] = $newban['expire'];
	$ban[BAN_REASON] = $newban['reason'];

	return $GLOBALS['db']->insertWithAutoId(BANS_FILE, BAN_ID, $ban);
}

function clearExpiredBans() {
	$compClause = new AndWhereClause();
	$compClause->add(new SimpleWhereClause(BAN_EXPIRE, '>', 0, INTEGER_COMPARISON));
	$compClause->add(new SimpleWhereClause(BAN_EXPIRE, '<=', time(), INTEGER_COMPARISON));

	$bans = $GLOBALS['db']->selectWhere(BANS_FILE, $compClause, -1);
	foreach ($bans as $ban) {
		deleteBanByID($ban[BAN_ID]);
	}
}

function deleteBanByID($id) {
	$GLOBALS['db']->deleteWhere(BANS_FILE, new SimpleWhereClause(BAN_ID, '=', $id, INTEGER_COMPARISON));
}
