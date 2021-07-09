<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

// Account functions
function accountByID($id) {
	global $link;
	$result = mysqli_query($link, "SELECT * FROM `" . TINYIB_DBACCOUNTS . "` WHERE `id` = '" . mysqli_real_escape_string($link, $id) . "' LIMIT 1");
	if ($result) {
		while ($account = mysqli_fetch_assoc($result)) {
			return $account;
		}
	}
}

function accountByUsername($username) {
	global $link;
	$result = mysqli_query($link, "SELECT * FROM `" . TINYIB_DBACCOUNTS . "` WHERE `username` = '" . mysqli_real_escape_string($link, $username) . "' LIMIT 1");
	if ($result) {
		while ($account = mysqli_fetch_assoc($result)) {
			return $account;
		}
	}
}

function allAccounts() {
	global $link;
	$accounts = array();
	$result = mysqli_query($link, "SELECT * FROM `" . TINYIB_DBACCOUNTS . "` ORDER BY `role` ASC, `username` ASC");
	if ($result) {
		while ($account = mysqli_fetch_assoc($result)) {
			$accounts[] = $account;
		}
	}
	return $accounts;
}

function insertAccount($account) {
	global $link;
	mysqli_query($link, "INSERT INTO `" . TINYIB_DBACCOUNTS . "` (`username`, `password`, `role`, `lastactive`) VALUES ('" . mysqli_real_escape_string($link, $account['username']) . "', '" . mysqli_real_escape_string($link, hashData($account['password'])) . "', '" . mysqli_real_escape_string($link, $account['role']) . "', '0')");
	return mysqli_insert_id($link);
}

function updateAccount($account) {
	global $link;
	mysqli_query($link, "UPDATE `" . TINYIB_DBACCOUNTS . "` SET `username` = '" . mysqli_real_escape_string($link, $account['username']) . "', `password` = '" . mysqli_real_escape_string($link, hashData($account['password'])) . "', `role` = '" . mysqli_real_escape_string($link, $account['role']) . "', `lastactive` = " . mysqli_real_escape_string($link, $account['lastactive'])  . " WHERE `id` = " . mysqli_real_escape_string($link, $account['id']) . " LIMIT 1");
}

function deleteAccountByID($id) {
	global $link;
	mysqli_query($link, "DELETE FROM `" . TINYIB_DBACCOUNTS . "` WHERE `id` = " . mysqli_real_escape_string($link, $id) . " LIMIT 1");
}

// Ban functions
function banByID($id) {
	global $link;
	$result = mysqli_query($link, "SELECT * FROM `" . TINYIB_DBBANS . "` WHERE `id` = '" . mysqli_real_escape_string($link, $id) . "' LIMIT 1");
	if ($result) {
		while ($ban = mysqli_fetch_assoc($result)) {
			return $ban;
		}
	}
}

function banByIP($ip) {
	global $link;
	$result = mysqli_query($link, "SELECT * FROM `" . TINYIB_DBBANS . "` WHERE `ip` = '" . mysqli_real_escape_string($link, $ip) . "' OR `ip` = '" . mysqli_real_escape_string($link, hashData($ip)) . "' LIMIT 1");
	if ($result) {
		while ($ban = mysqli_fetch_assoc($result)) {
			return $ban;
		}
	}
}

function allBans() {
	global $link;
	$bans = array();
	$result = mysqli_query($link, "SELECT * FROM `" . TINYIB_DBBANS . "` ORDER BY `timestamp` DESC");
	if ($result) {
		while ($ban = mysqli_fetch_assoc($result)) {
			$bans[] = $ban;
		}
	}
	return $bans;
}

function insertBan($ban) {
	global $link;
	mysqli_query($link, "INSERT INTO `" . TINYIB_DBBANS . "` (`ip`, `timestamp`, `expire`, `reason`) VALUES ('" . mysqli_real_escape_string($link, hashData($ban['ip'])) . "', '" . time() . "', '" . mysqli_real_escape_string($link, $ban['expire']) . "', '" . mysqli_real_escape_string($link, $ban['reason']) . "')");
	return mysqli_insert_id($link);
}

function clearExpiredBans() {
	global $link;
	$result = mysqli_query($link, "SELECT * FROM `" . TINYIB_DBBANS . "` WHERE `expire` > 0 AND `expire` <= " . time());
	if ($result) {
		while ($ban = mysqli_fetch_assoc($result)) {
			mysqli_query($link, "DELETE FROM `" . TINYIB_DBBANS . "` WHERE `id` = " . $ban['id'] . " LIMIT 1");
		}
	}
}

function deleteBanByID($id) {
	global $link;
	mysqli_query($link, "DELETE FROM `" . TINYIB_DBBANS . "` WHERE `id` = " . mysqli_real_escape_string($link, $id) . " LIMIT 1");
}

// Keyword functions
function keywordByID($id) {
	global $link;
	$result = mysqli_query($link, "SELECT * FROM `" . TINYIB_DBKEYWORDS . "` WHERE `id` = '" . mysqli_real_escape_string($link, $id) . "' LIMIT 1");
	if ($result) {
		while ($keyword = mysqli_fetch_assoc($result)) {
			return $keyword;
		}
	}
	return array();
}

function keywordByText($text) {
	global $link;
	$text = strtolower($text);
	$result = mysqli_query($link, "SELECT * FROM `" . TINYIB_DBKEYWORDS . "` WHERE `text` = '" . mysqli_real_escape_string($link, $text) . "'");
	if ($result) {
		while ($keyword = mysqli_fetch_assoc($result)) {
			if ($keyword['text'] === $text) {
				return $keyword;
			}
		}
	}
	return array();
}

function allKeywords() {
	global $link;
	$keywords = array();
	$result = mysqli_query($link, "SELECT * FROM `" . TINYIB_DBKEYWORDS . "` ORDER BY `text` ASC");
	if ($result) {
		while ($keyword = mysqli_fetch_assoc($result)) {
			$keywords[] = $keyword;
		}
	}
	return $keywords;
}

function insertKeyword($keyword) {
	global $link;
	$keyword['text'] = strtolower($keyword['text']);
	mysqli_query($link, "INSERT INTO `" . TINYIB_DBKEYWORDS . "` (`text`, `action`) VALUES ('" . mysqli_real_escape_string($link, $keyword['text']) . "', '" . mysqli_real_escape_string($link, $keyword['action']) . "')");
}

function deleteKeyword($id) {
	global $link;
	mysqli_query($link, "DELETE FROM `" . TINYIB_DBKEYWORDS . "` WHERE `id` = '" . mysqli_real_escape_string($link, $id) . "'");
}

// Log functions
function getLogs($offset, $limit) {
	global $link;
	$logs = array();
	$result = mysqli_query($link, "SELECT * FROM `" . TINYIB_DBLOGS . "` ORDER BY `timestamp` DESC LIMIT " . intval($offset) . ", " . intval($limit));
	if ($result) {
		while ($log = mysqli_fetch_assoc($result)) {
			$logs[] = $log;
		}
	}
	return $logs;
}

function insertLog($log) {
	global $link;
	mysqli_query($link, "INSERT INTO `" . TINYIB_DBLOGS . "` (`timestamp`, `account`, `message`) VALUES ('" . mysqli_real_escape_string($link, $log['timestamp']) . "', '" . mysqli_real_escape_string($link, $log['account']) . "', '" . mysqli_real_escape_string($link, $log['message']) . "')");
}

// Post functions
function uniquePosts() {
	global $link;
	$row = mysqli_fetch_row(mysqli_query($link, "SELECT COUNT(DISTINCT(`ip`)) FROM " . TINYIB_DBPOSTS));
	return $row[0];
}

function postByID($id) {
	global $link;
	$result = mysqli_query($link, "SELECT * FROM `" . TINYIB_DBPOSTS . "` WHERE `id` = '" . mysqli_real_escape_string($link, $id) . "' LIMIT 1");
	if ($result) {
		while ($post = mysqli_fetch_assoc($result)) {
			return $post;
		}
	}
}

function threadExistsByID($id) {
	global $link;
	return mysqli_result(mysqli_query($link, "SELECT COUNT(*) FROM `" . TINYIB_DBPOSTS . "` WHERE `id` = '" . mysqli_real_escape_string($link, $id) . "' AND `parent` = 0 AND `moderated` = 1 LIMIT 1"), 0, 0) > 0;
}

function insertPost($post) {
	global $link;
	mysqli_query($link, "INSERT INTO `" . TINYIB_DBPOSTS . "` (`parent`, `timestamp`, `bumped`, `ip`, `name`, `tripcode`, `email`, `nameblock`, `subject`, `message`, `password`, `file`, `file_hex`, `file_original`, `file_size`, `file_size_formatted`, `image_width`, `image_height`, `thumb`, `thumb_width`, `thumb_height`, `moderated`) VALUES (" . $post['parent'] . ", " . time() . ", " . time() . ", '" . hashData(remoteAddress()) . "', '" . mysqli_real_escape_string($link, $post['name']) . "', '" . mysqli_real_escape_string($link, $post['tripcode']) . "',	'" . mysqli_real_escape_string($link, $post['email']) . "',	'" . mysqli_real_escape_string($link, $post['nameblock']) . "', '" . mysqli_real_escape_string($link, $post['subject']) . "', '" . mysqli_real_escape_string($link, $post['message']) . "', '" . mysqli_real_escape_string($link, $post['password']) . "', '" . $post['file'] . "', '" . $post['file_hex'] . "', '" . mysqli_real_escape_string($link, $post['file_original']) . "', " . $post['file_size'] . ", '" . $post['file_size_formatted'] . "', " . $post['image_width'] . ", " . $post['image_height'] . ", '" . $post['thumb'] . "', " . $post['thumb_width'] . ", " . $post['thumb_height'] . ", " . $post['moderated'] . ")");
	return mysqli_insert_id($link);
}

function updatePostMessage($id, $message) {
	global $link;
	mysqli_query($link, "UPDATE `" . TINYIB_DBPOSTS . "` SET `message` = '" .  mysqli_real_escape_string($link, $message) . "' WHERE `id` = " . $id . " LIMIT 1");
}

function updatePostBumped($id, $bumped) {
	global $link;
	mysqli_query($link, "UPDATE `" . TINYIB_DBPOSTS . "` SET `bumped` = '" .  mysqli_real_escape_string($link, $bumped) . "' WHERE `id` = " . $id . " LIMIT 1");
}

function approvePostByID($id, $moderated) {
	global $link;
	mysqli_query($link, "UPDATE `" . TINYIB_DBPOSTS . "` SET `moderated` = " . $moderated . " WHERE `id` = " . $id . " LIMIT 1");
}

function bumpThreadByID($id) {
	global $link;
	mysqli_query($link, "UPDATE `" . TINYIB_DBPOSTS . "` SET `bumped` = " . time() . " WHERE `id` = " . $id . " LIMIT 1");
}

function stickyThreadByID($id, $setsticky) {
	global $link;
	mysqli_query($link, "UPDATE `" . TINYIB_DBPOSTS . "` SET `stickied` = '" . mysqli_real_escape_string($link, $setsticky) . "' WHERE `id` = " . $id . " LIMIT 1");
}

function lockThreadByID($id, $setlock) {
	global $link;
	mysqli_query($link, "UPDATE `" . TINYIB_DBPOSTS . "` SET `locked` = '" . mysqli_real_escape_string($link, $setlock) . "' WHERE `id` = " . $id . " LIMIT 1");
}

function countThreads() {
	global $link;
	return mysqli_result(mysqli_query($link, "SELECT COUNT(*) FROM `" . TINYIB_DBPOSTS . "` WHERE `parent` = 0 AND `moderated` = 1"), 0, 0);
}

function allThreads($moderated_only = true) {
	global $link;
	$threads = array();
	$result = mysqli_query($link, "SELECT * FROM `" . TINYIB_DBPOSTS . "` WHERE `parent` = 0" . ($moderated_only ? " AND moderated > 0" : "") . " ORDER BY `stickied` DESC, `bumped` DESC");
	if ($result) {
		while ($thread = mysqli_fetch_assoc($result)) {
			$threads[] = $thread;
		}
	}
	return $threads;
}

function numRepliesToThreadByID($id) {
	global $link;
	return mysqli_result(mysqli_query($link, "SELECT COUNT(*) FROM `" . TINYIB_DBPOSTS . "` WHERE `parent` = " . $id . " AND `moderated` = 1"), 0, 0);
}

function _postsInThreadByID($id, $moderated_only = true) {
	global $link;
	$posts = array();
	$result = mysqli_query($link, "SELECT * FROM `" . TINYIB_DBPOSTS . "` WHERE (`id` = " . $id . " OR `parent` = " . $id . ")" . ($moderated_only ? " AND `moderated` = 1" : "") . " ORDER BY `id` ASC");
	if ($result) {
		while ($post = mysqli_fetch_assoc($result)) {
			$posts[] = $post;
		}
	}
	return $posts;
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
	global $link;
	$posts = array();
	$result = mysqli_query($link, "SELECT `id`, `parent` FROM `" . TINYIB_DBPOSTS . "` WHERE `file_hex` = '" . mysqli_real_escape_string($link, $hex) . "' AND `moderated` = 1 LIMIT 1");
	if ($result) {
		while ($post = mysqli_fetch_assoc($result)) {
			$posts[] = $post;
		}
	}
	return $posts;
}

function latestPosts($moderated = true) {
	global $link;
	$posts = array();
	$result = mysqli_query($link, "SELECT * FROM `" . TINYIB_DBPOSTS . "` WHERE `moderated` " . ($moderated ? '>' : '=') . " 0 ORDER BY `timestamp` DESC LIMIT 10");
	if ($result) {
		while ($post = mysqli_fetch_assoc($result)) {
			$posts[] = $post;
		}
	}
	return $posts;
}

function deletePostByID($id) {
	global $link;
	mysqli_query($link, "DELETE FROM `" . TINYIB_DBPOSTS . "` WHERE `id` = " . mysqli_real_escape_string($link, $id) . " LIMIT 1");
}

function trimThreads() {
	global $link;
	if (TINYIB_MAXTHREADS > 0) {
		$result = mysqli_query($link, "SELECT `id` FROM `" . TINYIB_DBPOSTS . "` WHERE `parent` = 0 AND `moderated` = 1 ORDER BY `stickied` DESC, `bumped` DESC LIMIT " . TINYIB_MAXTHREADS . ", 10");
		if ($result) {
			while ($post = mysqli_fetch_assoc($result)) {
				deletePost($post['id']);
			}
		}
	}
}

function lastPostByIP() {
	global $link;
	$replies = mysqli_query($link, "SELECT * FROM `" . TINYIB_DBPOSTS . "` WHERE `ip` = '" . mysqli_real_escape_string($link, remoteAddress()) . "' OR `ip` = '" . mysqli_real_escape_string($link, hashData(remoteAddress())) . "' ORDER BY `id` DESC LIMIT 1");
	if ($replies) {
		while ($post = mysqli_fetch_assoc($replies)) {
			return $post;
		}
	}
}

// Report functions
function reportByIP($post, $ip) {
	global $link;
	$result = mysqli_query($link, "SELECT * FROM `" . TINYIB_DBREPORTS . "` WHERE `post` = '" . mysqli_real_escape_string($link, $post) . "' AND (`ip` = '" . mysqli_real_escape_string($link, $ip) . "' OR `ip` = '" . mysqli_real_escape_string($link, hashData($ip)) . "') LIMIT 1");
	if ($result) {
		while ($report = mysqli_fetch_assoc($result)) {
			return $report;
		}
	}
}

function reportsByPost($post) {
	global $link;
	$reports = array();
	$result = mysqli_query($link, "SELECT * FROM `" . TINYIB_DBREPORTS . "` WHERE `post` = '" . mysqli_real_escape_string($link, $post) . "'");
	if ($result) {
		while ($report = mysqli_fetch_assoc($result)) {
			$reports[] = $report;
		}
	}
	return $reports;
}

function allReports() {
	global $link;
	$reports = array();
	$result = mysqli_query($link, "SELECT * FROM `" . TINYIB_DBREPORTS . "` ORDER BY `post` ASC");
	if ($result) {
		while ($report = mysqli_fetch_assoc($result)) {
			$reports[] = $report;
		}
	}
	return $reports;
}

function insertReport($report) {
	global $link;
	mysqli_query($link, "INSERT INTO `" . TINYIB_DBREPORTS . "` (`ip`, `post`) VALUES ('" . mysqli_real_escape_string($link, hashData($report['ip'])) . "', '" . mysqli_real_escape_string($link, $report['post']) . "')");
}

function deleteReportsByPost($post) {
	global $link;
	mysqli_query($link, "DELETE FROM `" . TINYIB_DBREPORTS . "` WHERE `post` = '" . mysqli_real_escape_string($link, $post) . "'");
}

function deleteReportsByIP($ip) {
	global $link;
	mysqli_query($link, "DELETE FROM `" . TINYIB_DBREPORTS . "` WHERE `ip` = '" . mysqli_real_escape_string($link, $ip) . "' OR `ip` = '" . mysqli_real_escape_string($link, hashData($ip)) . "'");
}

// Utility functions
function mysqli_result($res, $row, $field = 0) {
	$res->data_seek($row);
	$datarow = $res->fetch_array();
	return $datarow[$field];
}
