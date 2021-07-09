<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

// Account functions
function accountByID($id) {
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBACCOUNTS . "` WHERE `id` = '" . mysql_real_escape_string($id) . "' LIMIT 1");
	if ($result) {
		while ($account = mysql_fetch_assoc($result)) {
			return $account;
		}
	}
}

function accountByUsername($username) {
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBACCOUNTS . "` WHERE `username` = '" . mysql_real_escape_string($username) . "' LIMIT 1");
	if ($result) {
		while ($account = mysql_fetch_assoc($result)) {
			return $account;
		}
	}
}

function allAccounts($username) {
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBACCOUNTS . "` ORDER BY `role` ASC, `username` ASC");
	if ($result) {
		while ($account = mysql_fetch_assoc($result)) {
			return $account;
		}
	}
}

function insertAccount($account) {
	mysql_query("INSERT INTO `" . TINYIB_DBACCOUNTS . "` (`username`, `password`, `role`, `lastactive`) VALUES (" . $account['username'] . ", '" . hashData($account['password']) . "', '" . mysql_real_escape_string($account['role']) . "', '0')");
	return mysql_insert_id();
}

function updateAccount($account) {
	mysql_query("UPDATE `" . TINYIB_DBACCOUNTS . "` SET `username` = " . $account['username'] . ", `password` = '" . hashData($account['password']) . "', `role` = '" . mysql_real_escape_string($account['role']) . "', `lastactive` = " . mysql_real_escape_string($account['lastactive']) . " WHERE `id` = '" . mysql_real_escape_string($account['id']) . "'");
}

function deleteAccountByID($id) {
	mysql_query("DELETE FROM `" . TINYIB_DBACCOUNTS . "` WHERE `id` = '" . mysql_real_escape_string($id) . "'");
}

// Ban functions
function banByID($id) {
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBBANS . "` WHERE `id` = '" . mysql_real_escape_string($id) . "' LIMIT 1");
	if ($result) {
		while ($ban = mysql_fetch_assoc($result)) {
			return $ban;
		}
	}
}

function banByIP($ip) {
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBBANS . "` WHERE `ip` = '" . mysql_real_escape_string($ip) . "' OR `ip` = '" . mysql_real_escape_string(hashData($ip)) . "' LIMIT 1");
	if ($result) {
		while ($ban = mysql_fetch_assoc($result)) {
			return $ban;
		}
	}
}

function allBans() {
	$bans = array();
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBBANS . "` ORDER BY `timestamp` DESC");
	if ($result) {
		while ($ban = mysql_fetch_assoc($result)) {
			$bans[] = $ban;
		}
	}
	return $bans;
}

function insertBan($ban) {
	mysql_query("INSERT INTO `" . TINYIB_DBBANS . "` (`ip`, `timestamp`, `expire`, `reason`) VALUES ('" . mysql_real_escape_string(hashData($ban['ip'])) . "', " . time() . ", '" . mysql_real_escape_string($ban['expire']) . "', '" . mysql_real_escape_string($ban['reason']) . "')");
	return mysql_insert_id();
}

function clearExpiredBans() {
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBBANS . "` WHERE `expire` > 0 AND `expire` <= " . time());
	if ($result) {
		while ($ban = mysql_fetch_assoc($result)) {
			mysql_query("DELETE FROM `" . TINYIB_DBBANS . "` WHERE `id` = " . $ban['id'] . " LIMIT 1");
		}
	}
}

function deleteBanByID($id) {
	mysql_query("DELETE FROM `" . TINYIB_DBBANS . "` WHERE `id` = " . mysql_real_escape_string($id) . " LIMIT 1");
}

// Keyword functions
function keywordByID($id) {
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBKEYWORDS . "` WHERE `id` = '" . mysql_real_escape_string($id) . "' LIMIT 1");
	if ($result) {
		while ($keyword = mysql_fetch_assoc($result)) {
			return $keyword;
		}
	}
}

function keywordByText($text) {
	$text = strtolower($text);
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBKEYWORDS . "` WHERE `text` = '" . mysql_real_escape_string($text) . "'");
	if ($result) {
		while ($keyword = mysql_fetch_assoc($result)) {
			if ($keyword['text'] === $text) {
				return $keyword;
			}
		}
	}
	return array();
}

function allKeywords() {
	$keywords = array();
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBKEYWORDS . "` ORDER BY `text` ASC");
	if ($result) {
		while ($keyword = mysql_fetch_assoc($result)) {
			$keywords[] = $keyword;
		}
	}
	return $keywords;
}

function insertKeyword($keyword) {
	$keyword['text'] = strtolower($keyword['text']);
	mysql_query("INSERT INTO `" . TINYIB_DBKEYWORDS . "` (`text`, `action`) VALUES ('" . mysql_real_escape_string($keyword['text']) . "', '" . mysql_real_escape_string($keyword['action']) . "')");
}

function deleteKeyword($id) {
	mysql_query("DELETE FROM `" . TINYIB_DBKEYWORDS . "` WHERE `id` = " . mysql_real_escape_string($id));
}

// Log functions
function getLogs($offset, $limit) {
	$logs = array();
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBLOGS . "` ORDER BY `timestamp` DESC LIMIT " . intval($offset) . ", " . intval($limit));
	if ($result) {
		while ($log = mysql_fetch_assoc($result)) {
			$logs[] = $log;
		}
	}
	return $logs;
}

function insertLog($log) {
	mysql_query("INSERT INTO `" . TINYIB_DBLOGS . "` (`timestamp`, `account`, `message`) VALUES ('" . mysql_real_escape_string($log['timestamp']) . "', '" . mysql_real_escape_string($log['account']) . "', '" . mysql_real_escape_string($log['message']) . "')");
}

// Post functions
function uniquePosts() {
	$row = mysql_fetch_row(mysql_query("SELECT COUNT(DISTINCT(`ip`)) FROM " . TINYIB_DBPOSTS));
	return $row[0];
}

function postByID($id) {
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBPOSTS . "` WHERE `id` = '" . mysql_real_escape_string($id) . "' LIMIT 1");
	if ($result) {
		while ($post = mysql_fetch_assoc($result)) {
			return $post;
		}
	}
}

function threadExistsByID($id) {
	return mysql_result(mysql_query("SELECT COUNT(*) FROM `" . TINYIB_DBPOSTS . "` WHERE `id` = '" . mysql_real_escape_string($id) . "' AND `parent` = 0 AND `moderated` = 1 LIMIT 1"), 0, 0) > 0;
}

function insertPost($post) {
	mysql_query("INSERT INTO `" . TINYIB_DBPOSTS . "` (`parent`, `timestamp`, `bumped`, `ip`, `name`, `tripcode`, `email`, `nameblock`, `subject`, `message`, `password`, `file`, `file_hex`, `file_original`, `file_size`, `file_size_formatted`, `image_width`, `image_height`, `thumb`, `thumb_width`, `thumb_height`, `moderated`) VALUES (" . $post['parent'] . ", " . time() . ", " . time() . ", '" . hashData(remoteAddress()) . "', '" . mysql_real_escape_string($post['name']) . "', '" . mysql_real_escape_string($post['tripcode']) . "',	'" . mysql_real_escape_string($post['email']) . "',	'" . mysql_real_escape_string($post['nameblock']) . "', '" . mysql_real_escape_string($post['subject']) . "', '" . mysql_real_escape_string($post['message']) . "', '" . mysql_real_escape_string($post['password']) . "', '" . $post['file'] . "', '" . $post['file_hex'] . "', '" . mysql_real_escape_string($post['file_original']) . "', " . $post['file_size'] . ", '" . $post['file_size_formatted'] . "', " . $post['image_width'] . ", " . $post['image_height'] . ", '" . $post['thumb'] . "', " . $post['thumb_width'] . ", " . $post['thumb_height'] . ", " . $post['moderated'] . ")");
	return mysql_insert_id();
}

function updatePostMessage($id, $message) {
	mysql_query("UPDATE `" . TINYIB_DBPOSTS . "` SET `message` = '" . mysql_real_escape_string($message) . "' WHERE `id` = " . $id . " LIMIT 1");
}

function updatePostBumped($id, $bumped) {
	mysql_query("UPDATE `" . TINYIB_DBPOSTS . "` SET `bumped` = '" . mysql_real_escape_string($bumped) . "' WHERE `id` = " . $id . " LIMIT 1");
}

function approvePostByID($id, $moderated) {
	mysql_query("UPDATE `" . TINYIB_DBPOSTS . "` SET `moderated` = $moderated WHERE `id` = " . $id . " LIMIT 1");
}

function bumpThreadByID($id) {
	mysql_query("UPDATE `" . TINYIB_DBPOSTS . "` SET `bumped` = " . time() . " WHERE `id` = " . $id . " LIMIT 1");
}

function stickyThreadByID($id, $setsticky) {
	mysql_query("UPDATE `" . TINYIB_DBPOSTS . "` SET `stickied` = '" . mysql_real_escape_string($setsticky) . "' WHERE `id` = " . $id . " LIMIT 1");
}

function lockThreadByID($id, $setlock) {
	mysql_query("UPDATE `" . TINYIB_DBPOSTS . "` SET `locked` = '" . mysql_real_escape_string($setlock) . "' WHERE `id` = " . $id . " LIMIT 1");
}

function countThreads() {
	return mysql_result(mysql_query("SELECT COUNT(*) FROM `" . TINYIB_DBPOSTS . "` WHERE `parent` = 0 AND `moderated` = 1"), 0, 0);
}

function allThreads($moderated_only = true) {
	$threads = array();
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBPOSTS . "` WHERE `parent` = 0" . ($moderated_only ? " AND moderated > 0" : "") . " ORDER BY `stickied` DESC, `bumped` DESC");
	if ($result) {
		while ($thread = mysql_fetch_assoc($result)) {
			$threads[] = $thread;
		}
	}
	return $threads;
}

function numRepliesToThreadByID($id) {
	return mysql_result(mysql_query("SELECT COUNT(*) FROM `" . TINYIB_DBPOSTS . "` WHERE `parent` = " . $id . " AND `moderated` = 1"), 0, 0);
}

function _postsInThreadByID($id, $moderated_only = true) {
	$posts = array();
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBPOSTS . "` WHERE (`id` = " . $id . " OR `parent` = " . $id . ")" . ($moderated_only ? " AND `moderated` = 1" : "") . " ORDER BY `id` ASC");
	if ($result) {
		while ($post = mysql_fetch_assoc($result)) {
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
	$posts = array();
	$result = mysql_query("SELECT `id`, `parent` FROM `" . TINYIB_DBPOSTS . "` WHERE `file_hex` = '" . mysql_real_escape_string($hex) . "' AND `moderated` = 1 LIMIT 1");
	if ($result) {
		while ($post = mysql_fetch_assoc($result)) {
			$posts[] = $post;
		}
	}
	return $posts;
}

function latestPosts($moderated = true) {
	$posts = array();
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBPOSTS . "` WHERE `moderated` " . ($moderated ? '>' : '=') . " 0 ORDER BY `timestamp` DESC LIMIT 10");
	if ($result) {
		while ($post = mysql_fetch_assoc($result)) {
			$posts[] = $post;
		}
	}
	return $posts;
}

function deletePostByID($id) {
	mysql_query("DELETE FROM `" . TINYIB_DBPOSTS . "` WHERE `id` = " . mysql_real_escape_string($id) . " LIMIT 1");
}

function trimThreads() {
	if (TINYIB_MAXTHREADS > 0) {
		$result = mysql_query("SELECT `id` FROM `" . TINYIB_DBPOSTS . "` WHERE `parent` = 0 AND `moderated` = 1 ORDER BY `stickied` DESC, `bumped` DESC LIMIT " . TINYIB_MAXTHREADS . ", 10");
		if ($result) {
			while ($post = mysql_fetch_assoc($result)) {
				deletePost($post['id']);
			}
		}
	}
}

function lastPostByIP() {
	$replies = mysql_query("SELECT * FROM `" . TINYIB_DBPOSTS . "` WHERE `ip` = '" . mysql_real_escape_string(remoteAddress()) . "' OR `ip` = '" . mysql_real_escape_string(hashData(remoteAddress())) . "' ORDER BY `id` DESC LIMIT 1");
	if ($replies) {
		while ($post = mysql_fetch_assoc($replies)) {
			return $post;
		}
	}
}

// Report functions
function reportByIP($post, $ip) {
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBREPORTS . "` WHERE `post` = '" . mysql_real_escape_string($post) . "' AND (`ip` = '" . mysql_real_escape_string($ip) . "' OR `ip` = '" . mysql_real_escape_string(hashData($ip)) . "') LIMIT 1");
	if ($result) {
		while ($report = mysql_fetch_assoc($result)) {
			return $report;
		}
	}
}

function reportsByPost($post) {
	$reports = array();
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBREPORTS . "` WHERE `post` = '" . mysql_real_escape_string($post) . "'");
	if ($result) {
		while ($report = mysql_fetch_assoc($result)) {
			$reports[] = $report;
		}
	}
	return $reports;
}

function allReports() {
	$reports = array();
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBREPORTS . "` ORDER BY `post` ASC");
	if ($result) {
		while ($report = mysql_fetch_assoc($result)) {
			$reports[] = $report;
		}
	}
	return $reports;
}

function insertReport($report) {
	mysql_query("INSERT INTO `" . TINYIB_DBREPORTS . "` (`ip`, `post`) VALUES ('" . mysql_real_escape_string(hashData($report['ip'])) . "', '" . mysql_real_escape_string($report['post']) . "')");
}

function deleteReportsByPost($post) {
	mysql_query("DELETE FROM `" . TINYIB_DBREPORTS . "` WHERE `post` = " . mysql_real_escape_string($post));
}

function deleteReportsByIP($ip) {
	mysql_query("DELETE FROM `" . TINYIB_DBREPORTS . "` WHERE `ip` = " . mysql_real_escape_string($ip) . " OR  `ip` = " . mysql_real_escape_string(hashData($ip)));
}
