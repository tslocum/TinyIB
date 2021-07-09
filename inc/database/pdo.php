<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

// Account functions
function accountByID($id) {
	$result = pdoQuery("SELECT * FROM " . TINYIB_DBACCOUNTS . " WHERE id = ?", array($id));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function accountByUsername($username) {
	$result = pdoQuery("SELECT * FROM " . TINYIB_DBACCOUNTS . " WHERE username = ? LIMIT 1", array($username));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function allAccounts() {
	$accounts = array();
	$results = pdoQuery("SELECT * FROM " . TINYIB_DBACCOUNTS . " ORDER BY role ASC, username ASC");
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$accounts[] = $row;
	}
	return $accounts;
}

function insertAccount($account) {
	global $dbh;
	$stm = $dbh->prepare("INSERT INTO " . TINYIB_DBACCOUNTS . " (username, password, role, lastactive) VALUES (?, ?, ?, ?)");
	$stm->execute(array($account['username'], hashData($account['password']), $account['role'], 0));
	return $dbh->lastInsertId();
}

function updateAccount($account) {
	global $dbh;
	$stm = $dbh->prepare("UPDATE " . TINYIB_DBACCOUNTS . " SET username = ?, password = ?, role = ?, lastactive = ? WHERE id = ?");
	$stm->execute(array($account['username'], hashData($account['password']), $account['role'], $account['lastactive'], $account['id']));
}

function deleteAccountByID($id) {
	pdoQuery("DELETE FROM " . TINYIB_DBACCOUNTS . " WHERE id = ?", array($id));
}

// Ban functions
function banByID($id) {
	$result = pdoQuery("SELECT * FROM " . TINYIB_DBBANS . " WHERE id = ?", array($id));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function banByIP($ip) {
	$result = pdoQuery("SELECT * FROM " . TINYIB_DBBANS . " WHERE ip = ? OR ip = ? LIMIT 1", array($ip, hashData($ip)));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function allBans() {
	$bans = array();
	$results = pdoQuery("SELECT * FROM " . TINYIB_DBBANS . " ORDER BY timestamp DESC");
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$bans[] = $row;
	}
	return $bans;
}

function insertBan($ban) {
	global $dbh;
	$now = time();
	$stm = $dbh->prepare("INSERT INTO " . TINYIB_DBBANS . " (ip, timestamp, expire, reason) VALUES (?, ?, ?, ?)");
	$stm->execute(array(hashData($ban['ip']), $now, $ban['expire'], $ban['reason']));
	return $dbh->lastInsertId();
}

function clearExpiredBans() {
	$now = time();
	pdoQuery("DELETE FROM " . TINYIB_DBBANS . " WHERE expire > 0 AND expire <= ?", array($now));
}

function deleteBanByID($id) {
	pdoQuery("DELETE FROM " . TINYIB_DBBANS . " WHERE id = ?", array($id));
}

// Keyword functions
function keywordByID($id) {
	$result = pdoQuery("SELECT * FROM " . TINYIB_DBKEYWORDS . " WHERE id = ? LIMIT 1", array($id));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function keywordByText($text) {
	$text = strtolower($text);
	$results = pdoQuery("SELECT * FROM " . TINYIB_DBKEYWORDS . " WHERE text = ?", array($text));
	while ($keyword = $results->fetch(PDO::FETCH_ASSOC)) {
		if ($keyword['text'] === $text) {
			return $keyword;
		}
	}
	return array();
}

function allKeywords() {
	$keywords = array();
	$results = pdoQuery("SELECT * FROM " . TINYIB_DBKEYWORDS . " ORDER BY text ASC");
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$keywords[] = $row;
	}
	return $keywords;
}

function insertKeyword($keyword) {
	global $dbh;
	$keyword['text'] = strtolower($keyword['text']);
	$stm = $dbh->prepare("INSERT INTO " . TINYIB_DBKEYWORDS . " (text, action) VALUES (?, ?)");
	$stm->execute(array($keyword['text'], $keyword['action']));
}

function deleteKeyword($id) {
	pdoQuery("DELETE FROM " . TINYIB_DBKEYWORDS . " WHERE id = ?", array($id));
}

// Log functions
function getLogs($offset, $limit) {
	$logs = array();
	$results = pdoQuery("SELECT * FROM " . TINYIB_DBLOGS . " ORDER BY timestamp DESC LIMIT " . intval($offset) . ", " . intval($limit));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$logs[] = $row;
	}
	return $logs;
}

function insertLog($log) {
	global $dbh;
	$stm = $dbh->prepare("INSERT INTO " . TINYIB_DBLOGS . " (timestamp, account, message) VALUES (?, ?, ?)");
	$stm->execute(array($log['timestamp'], $log['account'], $log['message']));
}

// Post functions
function uniquePosts() {
	$result = pdoQuery("SELECT COUNT(DISTINCT(ip)) FROM " . TINYIB_DBPOSTS);
	return (int)$result->fetchColumn();
}

function postByID($id) {
	$result = pdoQuery("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE id = ?", array($id));
	if ($result) {
		return $result->fetch();
	}
}

function threadExistsByID($id) {
	$result = pdoQuery("SELECT COUNT(*) FROM " . TINYIB_DBPOSTS . " WHERE id = ? AND parent = 0 AND moderated > 0", array($id));
	return $result->fetchColumn() != 0;
}

function insertPost($post) {
	global $dbh;
	$now = time();
	$stm = $dbh->prepare("INSERT INTO " . TINYIB_DBPOSTS . " (parent, timestamp, bumped, ip, name, tripcode, email,   nameblock, subject, message, password,   file, file_hex, file_original, file_size, file_size_formatted, image_width, image_height, thumb, thumb_width, thumb_height, moderated) " .
		" VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
	$stm->execute(array($post['parent'], $now, $now, hashData(remoteAddress()), $post['name'], $post['tripcode'], $post['email'],
		$post['nameblock'], $post['subject'], $post['message'], $post['password'],
		$post['file'], $post['file_hex'], $post['file_original'], $post['file_size'], $post['file_size_formatted'],
		$post['image_width'], $post['image_height'], $post['thumb'], $post['thumb_width'], $post['thumb_height'], $post['moderated']));
	return $dbh->lastInsertId();
}

function updatePostMessage($id, $message) {
	pdoQuery("UPDATE " . TINYIB_DBPOSTS . " SET message = ? WHERE id = ?", array($message, $id));
}

function updatePostBumped($id, $bumped) {
	pdoQuery("UPDATE " . TINYIB_DBPOSTS . " SET bumped = ? WHERE id = ?", array($bumped, $id));
}

function approvePostByID($id, $moderated) {
	pdoQuery("UPDATE " . TINYIB_DBPOSTS . " SET moderated = ? WHERE id = ?", array($moderated, $id));
}

function bumpThreadByID($id) {
	$now = time();
	pdoQuery("UPDATE " . TINYIB_DBPOSTS . " SET bumped = ? WHERE id = ?", array($now, $id));
}

function stickyThreadByID($id, $setsticky) {
	pdoQuery("UPDATE " . TINYIB_DBPOSTS . " SET stickied = ? WHERE id = ?", array($setsticky, $id));
}

function lockThreadByID($id, $setlock) {
	pdoQuery("UPDATE " . TINYIB_DBPOSTS . " SET locked = ? WHERE id = ?", array($setlock, $id));
}

function countThreads() {
	$result = pdoQuery("SELECT COUNT(*) FROM " . TINYIB_DBPOSTS . " WHERE parent = 0 AND moderated > 0");
	return (int)$result->fetchColumn();
}

function allThreads($moderated_only = true) {
	$threads = array();
	$results = pdoQuery("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE parent = 0" . ($moderated_only ? " AND moderated > 0" : "") . " ORDER BY stickied DESC, bumped DESC");
	while ($row = $results->fetch()) {
		$threads[] = $row;
	}
	return $threads;
}

function numRepliesToThreadByID($id) {
	$result = pdoQuery("SELECT COUNT(*) FROM " . TINYIB_DBPOSTS . " WHERE parent = ? AND moderated > 0", array($id));
	return (int)$result->fetchColumn();
}

function _postsInThreadByID($id, $moderated_only = true) {
	$posts = array();
	$results = pdoQuery("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE (id = ? OR parent = ?)" . ($moderated_only ? " AND moderated > 0" : "") . " ORDER BY id ASC", array($id, $id));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$posts[] = $row;
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
	$results = pdoQuery("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE file_hex = ? AND moderated > 0 LIMIT 1", array($hex));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$posts[] = $row;
	}
	return $posts;
}

function latestPosts($moderated = true) {
	$posts = array();
	$results = pdoQuery("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE moderated " . ($moderated ? '>' : '=') . " 0 ORDER BY timestamp DESC LIMIT 10");
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$posts[] = $row;
	}
	return $posts;
}

function deletePostByID($id) {
	pdoQuery("DELETE FROM " . TINYIB_DBPOSTS . " WHERE id = ?", array($id));
}

function trimThreads() {
	$limit = (int)TINYIB_MAXTHREADS;
	if ($limit > 0) {
		$results = pdoQuery("SELECT id FROM " . TINYIB_DBPOSTS . " WHERE parent = 0 AND moderated > 0 ORDER BY stickied DESC, bumped DESC LIMIT 100 OFFSET " . $limit);
		/*
		old mysql, sqlite3: SELECT id FROM $table ORDER BY bumped LIMIT $limit,100
		mysql, postgresql, sqlite3: SELECT id FROM $table ORDER BY bumped LIMIT 100 OFFSET $limit
		oracle: SELECT id FROM ( SELECT id, rownum FROM $table ORDER BY bumped) WHERE rownum >= $limit
		MSSQL: WITH ts AS (SELECT ROWNUMBER() OVER (ORDER BY bumped) AS 'rownum', * FROM $table) SELECT id FROM ts WHERE rownum >= $limit
		*/
		foreach ($results as $post) {
			deletePost($post['id']);
		}
	}
}

function lastPostByIP() {
	$result = pdoQuery("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE ip = ? OR ip = ? ORDER BY id DESC LIMIT 1", array(remoteAddress(), hashData(remoteAddress())));
	return $result->fetch(PDO::FETCH_ASSOC);
}

// Report functions
function reportByIP($post, $ip) {
	$result = pdoQuery("SELECT * FROM " . TINYIB_DBREPORTS . " WHERE post = ? AND (ip = ? OR ip = ?) LIMIT 1", array($post, $ip, hashData($ip)));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function reportsByPost($post) {
	$reports = array();
	$results = pdoQuery("SELECT * FROM " . TINYIB_DBREPORTS . " WHERE post = ?", array($post));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$reports[] = $row;
	}
	return $reports;
}

function allReports() {
	$reports = array();
	$results = pdoQuery("SELECT * FROM " . TINYIB_DBREPORTS . " ORDER BY post ASC");
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$reports[] = $row;
	}
	return $reports;
}

function insertReport($report) {
	global $dbh;
	$stm = $dbh->prepare("INSERT INTO " . TINYIB_DBREPORTS . " (ip, post) VALUES (?, ?)");
	$stm->execute(array(hashData($report['ip']), $report['post']));
}

function deleteReportsByPost($post) {
	pdoQuery("DELETE FROM " . TINYIB_DBREPORTS . " WHERE post = ?", array($post));
}

function deleteReportsByIP($ip) {
	pdoQuery("DELETE FROM " . TINYIB_DBREPORTS . " WHERE ip = ? OR ip = ?", array($ip, hashData($ip)));
}
