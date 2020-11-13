<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

// Post functions
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
	$post[POST_ID] = '0';
	$post[POST_PARENT] = $newpost['parent'];
	$post[POST_TIMESTAMP] = time();
	$post[POST_BUMPED] = time();
	$post[POST_IP] = hashData($newpost['ip']);
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
	$post[POST_STICKIED] = $newpost['stickied'];
	$post[POST_LOCKED] = $newpost['locked'];
	$post[POST_MODERATED] = $newpost['moderated'];

	return $GLOBALS['db']->insertWithAutoId(POSTS_FILE, POST_ID, $post);
}

function approvePostByID($id) {
	$rows = $GLOBALS['db']->selectWhere(POSTS_FILE, new SimpleWhereClause(POST_ID, '=', $id, INTEGER_COMPARISON), 1);
	if (count($rows) > 0) {
		foreach ($rows as $post) {
			$post[POST_MODERATED] = 1;
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

function stickyThreadByID($id, $setsticky) {
	$rows = $GLOBALS['db']->selectWhere(POSTS_FILE, new SimpleWhereClause(POST_ID, '=', $id, INTEGER_COMPARISON), 1);
	if (count($rows) > 0) {
		foreach ($rows as $post) {
			$post[POST_STICKIED] = intval($setsticky);
			$GLOBALS['db']->updateRowById(POSTS_FILE, POST_ID, $post);
		}
	}
}

function lockThreadByID($id, $setlock) {
	$rows = $GLOBALS['db']->selectWhere(POSTS_FILE, new SimpleWhereClause(POST_ID, '=', $id, INTEGER_COMPARISON), 1);
	if (count($rows) > 0) {
		foreach ($rows as $post) {
			$post[POST_LOCKED] = intval($setlock);
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
		$post['id'] = $oldpost[POST_ID];
		$post['parent'] = $oldpost[POST_PARENT];
		$post['timestamp'] = $oldpost[POST_TIMESTAMP];
		$post['bumped'] = $oldpost[POST_BUMPED];
		$post['ip'] = $oldpost[POST_IP];
		$post['name'] = $oldpost[POST_NAME];
		$post['tripcode'] = $oldpost[POST_TRIPCODE];
		$post['email'] = $oldpost[POST_EMAIL];
		$post['nameblock'] = $oldpost[POST_NAMEBLOCK];
		$post['subject'] = $oldpost[POST_SUBJECT];
		$post['message'] = $oldpost[POST_MESSAGE];
		$post['password'] = $oldpost[POST_PASSWORD];
		$post['file'] = $oldpost[POST_FILE];
		$post['file_hex'] = $oldpost[POST_FILE_HEX];
		$post['file_original'] = $oldpost[POST_FILE_ORIGINAL];
		$post['file_size'] = $oldpost[POST_FILE_SIZE];
		$post['file_size_formatted'] = $oldpost[POST_FILE_SIZE_FORMATTED];
		$post['image_width'] = $oldpost[POST_IMAGE_WIDTH];
		$post['image_height'] = $oldpost[POST_IMAGE_HEIGHT];
		$post['thumb'] = $oldpost[POST_THUMB];
		$post['thumb_width'] = $oldpost[POST_THUMB_WIDTH];
		$post['thumb_height'] = $oldpost[POST_THUMB_HEIGHT];
		$post['stickied'] = isset($oldpost[POST_STICKIED]) ? $oldpost[POST_STICKIED] : 0;
		$post['locked'] = isset($oldpost[POST_LOCKED]) ? $oldpost[POST_LOCKED] : 0;

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

function imagesInThreadByID($id, $moderated_only = true) {
	$images = 0;
	$posts = postsInThreadByID($id, false);
	foreach ($posts as $post) {
		if ($post['file'] != '') {
			$images++;
		}
	}
	return $images;
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
	$GLOBALS['db']->deleteWhere(POSTS_FILE, new SimpleWhereClause(POST_ID, '=', $id, INTEGER_COMPARISON));
}

function trimThreads() {
	if (TINYIB_MAXTHREADS > 0) {
		$numthreads = countThreads();
		if ($numthreads > TINYIB_MAXTHREADS) {
			$allthreads = allThreads();
			for ($i = TINYIB_MAXTHREADS; $i < $numthreads; $i++) {
				deletePost($allthreads[$i]['id']);
			}
		}
	}
}

function lastPostByIP() {
	$compClause = new OrWhereClause();
	$compClause->add(new SimpleWhereClause(POST_IP, '=', $_SERVER['REMOTE_ADDR'], STRING_COMPARISON));
	$compClause->add(new SimpleWhereClause(POST_IP, '=', hashData($_SERVER['REMOTE_ADDR']), STRING_COMPARISON));
	$rows = $GLOBALS['db']->selectWhere(POSTS_FILE, $compClause, 1, new OrderBy(POST_ID, DESCENDING, INTEGER_COMPARISON));
	return convertPostsToSQLStyle($rows, true);
}

// Ban functions
function banByID($id) {
	return convertBansToSQLStyle($GLOBALS['db']->selectWhere(BANS_FILE, new SimpleWhereClause(BAN_ID, '=', $id, INTEGER_COMPARISON), 1), true);
}

function banByIP($ip) {
	$compClause = new OrWhereClause();
	$compClause->add(new SimpleWhereClause(BAN_IP, '=', $ip, STRING_COMPARISON));
	$compClause->add(new SimpleWhereClause(BAN_IP, '=', hashData($ip), STRING_COMPARISON));
	return convertBansToSQLStyle($GLOBALS['db']->selectWhere(BANS_FILE, $compClause, 1), true);
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
	$ban[BAN_IP] = hashData($newban['ip']);
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

// Report functions
function reportByIP($post, $ip) {
	$ipClause = new OrWhereClause();
	$ipClause->add(new SimpleWhereClause(REPORT_IP, '=', $ip, STRING_COMPARISON));
	$ipClause->add(new SimpleWhereClause(REPORT_IP, '=', hashData($ip), STRING_COMPARISON));

	$andClause = new AndWhereClause();
	$andClause->add(new SimpleWhereClause(REPORT_POST, '=', $post, INTEGER_COMPARISON));
	$andClause->add($ipClause);

	return convertReportsToSQLStyle($GLOBALS['db']->selectWhere(REPORTS_FILE, $andClause, 1), true);
}

function reportsByPost($post) {
	return convertReportsToSQLStyle($GLOBALS['db']->selectWhere(REPORTS_FILE, new SimpleWhereClause(REPORT_POST, '=', $post, INTEGER_COMPARISON), 1), true);
}

function allReports() {
	$rows = $GLOBALS['db']->selectWhere(REPORTS_FILE, NULL, -1, new OrderBy(REPORT_POST, ASCENDING, INTEGER_COMPARISON));
	return convertReportsToSQLStyle($rows);
}

function convertReportsToSQLStyle($reports, $singlereport = false) {
	$newreports = array();
	foreach ($reports as $oldreport) {
		$report = array();
		$report['id'] = $oldreport[REPORT_ID];
		$report['ip'] = $oldreport[REPORT_IP];
		$report['post'] = $oldreport[REPORT_POST];

		if ($singlereport) {
			return $report;
		}
		$newreports[] = $report;
	}
	return $newreports;
}

function insertReport($newreport) {
	$report = array();
	$report[REPORT_ID] = '0';
	$report[REPORT_IP] = hashData($newreport['ip']);
	$report[REPORT_POST] = $newreport['post'];

	$GLOBALS['db']->insertWithAutoId(REPORTS_FILE, REPORT_ID, $report);
}

function deleteReportsByPost($post) {
	$GLOBALS['db']->deleteWhere(REPORTS_FILE, new SimpleWhereClause(REPORT_POST, '=', $post, INTEGER_COMPARISON));
}

function deleteReportsByIP($ip) {
	$ipClause = new OrWhereClause();
	$ipClause->add(new SimpleWhereClause(REPORT_IP, '=', $ip, STRING_COMPARISON));
	$ipClause->add(new SimpleWhereClause(REPORT_IP, '=', hashData($ip), STRING_COMPARISON));

	$GLOBALS['db']->deleteWhere(REPORTS_FILE, $ipClause);
}
