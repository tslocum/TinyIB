<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

// Account functions
function accountByID($id) {
	global $db;
	$result = $db->query("SELECT * FROM " . TINYIB_DBACCOUNTS . " WHERE id = '" . $db->escapeString($id) . "' LIMIT 1");
	while ($account = $result->fetchArray()) {
		return $account;
	}
}

function accountByUsername($username) {
	global $db;
	$result = $db->query("SELECT * FROM " . TINYIB_DBACCOUNTS . " WHERE username = '" . $db->escapeString($username) . "' LIMIT 1");
	while ($account = $result->fetchArray()) {
		return $account;
	}
}

function allAccounts() {
	global $db;
	$accounts = array();
	$result = $db->query("SELECT * FROM " . TINYIB_DBACCOUNTS . " ORDER BY role ASC, username ASC");
	while ($account = $result->fetchArray()) {
		$accounts[] = $account;
	}
	return $accounts;
}

function insertAccount($account) {
	global $db;
	$db->exec("INSERT INTO " . TINYIB_DBACCOUNTS . " (username, password, role, lastactive) VALUES ('" . $db->escapeString($account['username']) . "', '" . $db->escapeString(hashData($account['password'])) . "', '" . $db->escapeString($account['role']) . "', '0')");
	return $db->lastInsertRowID();
}

function updateAccount($account) {
	global $db;
	$db->exec("UPDATE " . TINYIB_DBACCOUNTS . " SET username = '" . $db->escapeString($account['username']) . "', password = '" . $db->escapeString(hashData($account['password'])) . "', role = '" . $db->escapeString($account['role']) . "', lastactive = '" . $db->escapeString($account['lastactive']) . "'  WHERE id = '" . $db->escapeString($account['id']) . "'");
	return $db->lastInsertRowID();
}

function deleteAccountByID($id) {
	global $db;
	$db->exec("DELETE FROM " . TINYIB_DBACCOUNTS . " WHERE id = '" . $db->escapeString($id) . "'");
}

// Ban functions
function banByID($id) {
	global $db;
	$result = $db->query("SELECT * FROM " . TINYIB_DBBANS . " WHERE id = '" . $db->escapeString($id) . "' LIMIT 1");
	while ($ban = $result->fetchArray()) {
		return $ban;
	}
}

function banByIP($ip) {
	global $db;
	$result = $db->query("SELECT * FROM " . TINYIB_DBBANS . " WHERE ip = '" . $db->escapeString($ip) . "' OR ip = '" . $db->escapeString(hashData($ip)) . "' LIMIT 1");
	while ($ban = $result->fetchArray()) {
		return $ban;
	}
}

function allBans() {
	global $db;
	$bans = array();
	$result = $db->query("SELECT * FROM " . TINYIB_DBBANS . " ORDER BY timestamp DESC");
	while ($ban = $result->fetchArray()) {
		$bans[] = $ban;
	}
	return $bans;
}

function insertBan($ban) {
	global $db;
	$db->exec("INSERT INTO " . TINYIB_DBBANS . " (ip, timestamp, expire, reason) VALUES ('" . $db->escapeString(hashData($ban['ip'])) . "', " . time() . ", '" . $db->escapeString($ban['expire']) . "', '" . $db->escapeString($ban['reason']) . "')");
	return $db->lastInsertRowID();
}

function clearExpiredBans() {
	global $db;
	$result = $db->query("SELECT * FROM " . TINYIB_DBBANS . " WHERE expire > 0 AND expire <= " . time());
	while ($ban = $result->fetchArray()) {
		$db->exec("DELETE FROM " . TINYIB_DBBANS . " WHERE id = " . $ban['id']);
	}
}

function deleteBanByID($id) {
	global $db;
	$db->exec("DELETE FROM " . TINYIB_DBBANS . " WHERE id = '" . $db->escapeString($id) . "'");
}

// Keyword functions
function keywordByID($id) {
	global $db;
	$result = $db->query("SELECT * FROM " . TINYIB_DBKEYWORDS . " WHERE id = '" . $db->escapeString($id) . "' LIMIT 1");
	while ($keyword = $result->fetchArray()) {
		return $keyword;
	}
	return array();
}

function keywordByText($text) {
	global $db;
	$text = strtolower($text);
	$result = $db->query("SELECT * FROM " . TINYIB_DBKEYWORDS . " WHERE text = '" . $db->escapeString($text) . "'");
	while ($keyword = $result->fetchArray()) {
		if ($keyword['text'] === $text) {
			return $keyword;
		}
	}
	return array();
}

function allKeywords() {
	global $db;
	$keywords = array();
	$result = $db->query("SELECT * FROM " . TINYIB_DBKEYWORDS . " ORDER BY text ASC");
	while ($keyword = $result->fetchArray()) {
		$keywords[] = $keyword;
	}
	return $keywords;
}

function insertKeyword($keyword) {
	global $db;
	$keyword['text'] = strtolower($keyword['text']);
	$db->exec("INSERT INTO " . TINYIB_DBKEYWORDS . " (text, action) VALUES ('" . $db->escapeString($keyword['text']) . "', '" . $db->escapeString($keyword['action']) . "')");
}

function deleteKeyword($id) {
	global $db;
	$db->exec("DELETE FROM " . TINYIB_DBKEYWORDS . " WHERE id = '" . $db->escapeString($id) . "'");
}

// Log functions
function getLogs($offset, $limit) {
	global $db;
	$logs = array();
	$result = $db->query("SELECT * FROM " . TINYIB_DBLOGS . " ORDER BY timestamp DESC LIMIT " . intval($offset) . ", " . intval($limit));
	while ($log = $result->fetchArray()) {
		$logs[] = $log;
	}
	return $logs;
}

function allLogs() {
	global $db;
	$logs = array();
	$result = $db->query("SELECT * FROM " . TINYIB_DBLOGS . " ORDER BY timestamp ASC");
	while ($log = $result->fetchArray()) {
		$logs[] = $log;
	}
	return $logs;
}

function insertLog($log) {
	global $db;
	$db->exec("INSERT INTO " . TINYIB_DBLOGS . " (timestamp, account, message) VALUES ('" . $db->escapeString($log['timestamp']) . "', '" . $db->escapeString($log['account']) . "', '" . $db->escapeString($log['message']) . "')");
}

// Post functions
function uniquePosts() {
	global $db;
	return $db->querySingle("SELECT COUNT(ip) FROM (SELECT DISTINCT ip FROM " . TINYIB_DBPOSTS . ")");
}

function postByID($id) {
	global $db;
	$result = $db->query("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE id = '" . $db->escapeString($id) . "' LIMIT 1");
	while ($post = $result->fetchArray()) {
		return $post;
	}
}

function threadExistsByID($id) {
	global $db;
	return $db->querySingle("SELECT COUNT(*) FROM " . TINYIB_DBPOSTS . " WHERE id = '" . $db->escapeString($id) . "' AND parent = 0 LIMIT 1") > 0;
}

function insertPost($post) {
	global $db;
	$db->exec("INSERT INTO " . TINYIB_DBPOSTS . " (parent, timestamp, bumped, ip, name, tripcode, email, nameblock, subject, message, password, file, file_hex, file_original, file_size, file_size_formatted, image_width, image_height, thumb, thumb_width, thumb_height, moderated) VALUES (" . $post['parent'] . ", " . time() . ", " . time() . ", '" . hashData(remoteAddress()) . "', '" . $db->escapeString($post['name']) . "', '" . $db->escapeString($post['tripcode']) . "',	'" . $db->escapeString($post['email']) . "',	'" . $db->escapeString($post['nameblock']) . "', '" . $db->escapeString($post['subject']) . "', '" . $db->escapeString($post['message']) . "', '" . $db->escapeString($post['password']) . "', '" . $post['file'] . "', '" . $post['file_hex'] . "', '" . $db->escapeString($post['file_original']) . "', " . $post['file_size'] . ", '" . $post['file_size_formatted'] . "', " . $post['image_width'] . ", " . $post['image_height'] . ", '" . $post['thumb'] . "', " . $post['thumb_width'] . ", " . $post['thumb_height'] . ", " . $post['moderated'] . ")");
	return $db->lastInsertRowID();
}

function updatePostMessage($id, $message) {
	global $db;
	$db->exec("UPDATE " . TINYIB_DBPOSTS . " SET message = '" . $db->escapeString($message) . "' WHERE id = " . $id);
}

function updatePostBumped($id, $bumped) {
	global $db;
	$db->exec("UPDATE " . TINYIB_DBPOSTS . " SET bumped = '" . $db->escapeString($bumped) . "' WHERE id = " . $id);
}

function approvePostByID($id, $moderated) {
	global $db;
	$db->exec("UPDATE " . TINYIB_DBPOSTS . " SET moderated = " . $moderated . " WHERE id = " . $id);
}

function bumpThreadByID($id) {
	global $db;
	$db->exec("UPDATE " . TINYIB_DBPOSTS . " SET bumped = " . time() . " WHERE id = " . $id);
}

function stickyThreadByID($id, $setsticky) {
	global $db;
	$db->exec("UPDATE " . TINYIB_DBPOSTS . " SET stickied = '" . $db->escapeString($setsticky) . "' WHERE id = " . $id);
}

function lockThreadByID($id, $setlock) {
	global $db;
	$db->exec("UPDATE " . TINYIB_DBPOSTS . " SET locked = '" . $db->escapeString($setlock) . "' WHERE id = " . $id);
}

function countThreads() {
	global $db;
	return $db->querySingle("SELECT COUNT(*) FROM " . TINYIB_DBPOSTS . " WHERE parent = 0");
}

function allThreads($moderated_only = true) {
	global $db;
	$threads = array();
	$result = $db->query("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE parent = 0" . ($moderated_only ? " AND moderated > 0" : "") . " ORDER BY stickied DESC, bumped DESC");
	while ($thread = $result->fetchArray()) {
		$threads[] = $thread;
	}
	return $threads;
}

function numRepliesToThreadByID($id) {
	global $db;
	return $db->querySingle("SELECT COUNT(*) FROM " . TINYIB_DBPOSTS . " WHERE parent = " . $id);
}

function _postsInThreadByID($id, $moderated_only = true) {
	global $db;
	$posts = array();
	$result = $db->query("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE (id = " . $id . " OR parent = " . $id . ")" . ($moderated_only ? " AND moderated > 0" : "") . " ORDER BY id ASC");
	while ($post = $result->fetchArray()) {
		$posts[] = $post;
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
	global $db;
	$posts = array();
	$result = $db->query("SELECT id, parent FROM " . TINYIB_DBPOSTS . " WHERE file_hex = '" . $db->escapeString($hex) . "' LIMIT 1");
	while ($post = $result->fetchArray()) {
		$posts[] = $post;
	}
	return $posts;
}

function latestPosts($moderated = true) {
	global $db;
	$posts = array();
	$result = $db->query("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE `moderated` " . ($moderated ? '>' : '=') . " 0 ORDER BY timestamp DESC LIMIT 10");
	while ($post = $result->fetchArray()) {
		$posts[] = $post;
	}
	return $posts;
}

function deletePostByID($id) {
	global $db;
	$db->exec("DELETE FROM " . TINYIB_DBPOSTS . " WHERE id = '" . $db->escapeString($id) . "'");
}

function trimThreads() {
	global $db;
	if (TINYIB_MAXTHREADS > 0) {
		$result = $db->query("SELECT id FROM " . TINYIB_DBPOSTS . " WHERE parent = 0 ORDER BY stickied DESC, bumped DESC LIMIT " . TINYIB_MAXTHREADS . ", 10");
		while ($post = $result->fetchArray()) {
			deletePost($post['id']);
		}
	}
}

function lastPostByIP() {
	global $db;
	$result = $db->query("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE ip = '" . $db->escapeString(remoteAddress()) . "' OR ip = '" . $db->escapeString(hashData(remoteAddress())) . "' ORDER BY id DESC LIMIT 1");
	while ($post = $result->fetchArray()) {
		return $post;
	}
}

// Report functions
function reportByIP($post, $ip) {
	global $db;
	$result = $db->query("SELECT * FROM " . TINYIB_DBREPORTS . " WHERE post = '" . $db->escapeString($post) . "' AND (ip = '" . $db->escapeString($ip) . "' OR ip = '" . $db->escapeString(hashData($ip)) . "') LIMIT 1");
	while ($report = $result->fetchArray()) {
		return $report;
	}
}

function reportsByPost($post) {
	global $db;
	$reports = array();
	$result = $db->query("SELECT * FROM " . TINYIB_DBREPORTS . " WHERE post = '" . $db->escapeString($post) . "'");
	while ($report = $result->fetchArray()) {
		$reports[] = $report;
	}
	return $reports;
}

function allReports() {
	global $db;
	$reports = array();
	$result = $db->query("SELECT * FROM " . TINYIB_DBREPORTS . " ORDER BY post ASC");
	while ($report = $result->fetchArray()) {
		$reports[] = $report;
	}
	return $reports;
}

function insertReport($report) {
	global $db;
	$db->exec("INSERT INTO " . TINYIB_DBREPORTS . " (ip, post) VALUES ('" . $db->escapeString(hashData($report['ip'])) . "', '" . $db->escapeString($report['post']) . "')");
}

function deleteReportsByPost($post) {
	global $db;
	$db->exec("DELETE FROM " . TINYIB_DBREPORTS . " WHERE post = '" . $db->escapeString($post) . "'");
}

function deleteReportsByIP($ip) {
	global $db;
	$db->exec("DELETE FROM " . TINYIB_DBREPORTS . " WHERE ip = '" . $db->escapeString($ip) . "' OR ip = '" . $db->escapeString(hashData($ip)) . "'");
}
