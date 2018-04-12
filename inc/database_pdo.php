<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

if (TINYIB_DBDSN == '') { // Build a default (likely MySQL) DSN
	$dsn = TINYIB_DBDRIVER . ":host=" . TINYIB_DBHOST;
	if (TINYIB_DBPORT > 0) {
		$dsn .= ";port=" . TINYIB_DBPORT;
	}
	$dsn .= ";dbname=" . TINYIB_DBNAME;
} else { // Use a custom DSN
	$dsn = TINYIB_DBDSN;
}

if (TINYIB_DBDRIVER === 'pgsql') {
	$options = array(PDO::ATTR_PERSISTENT => true,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
} else {
	$options = array(PDO::ATTR_PERSISTENT => true,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
}

try {
	$dbh = new PDO($dsn, TINYIB_DBUSERNAME, TINYIB_DBPASSWORD, $options);
} catch (PDOException $e) {
	fancyDie("Failed to connect to the database: " . $e->getMessage());
}

// Create the posts table if it does not exist
if (TINYIB_DBDRIVER === 'pgsql') {
	$query = "SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " . $dbh->quote(TINYIB_DBPOSTS);
	$posts_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(TINYIB_DBPOSTS));
	$posts_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}

if (!$posts_exists) {
	$dbh->exec($posts_sql);
}

// Create the bans table if it does not exist
if (TINYIB_DBDRIVER === 'pgsql') {
	$query = "SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " . $dbh->quote(TINYIB_DBBANS);
	$bans_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(TINYIB_DBBANS));
	$bans_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}

if (!$bans_exists) {
	$dbh->exec($bans_sql);
}

# Utililty
function pdoQuery($sql, $params = false) {
	global $dbh;

	if ($params) {
		$statement = $dbh->prepare($sql);
		$statement->execute($params);
	} else {
		$statement = $dbh->query($sql);
	}

	return $statement;
}

# Post Functions
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
	$result = pdoQuery("SELECT COUNT(*) FROM " . TINYIB_DBPOSTS . " WHERE id = ? AND parent = 0 AND moderated = 1", array($id));
	return $result->fetchColumn() != 0;
}

function insertPost($post) {
	global $dbh;
	$now = time();
	$stm = $dbh->prepare("INSERT INTO " . TINYIB_DBPOSTS . " (parent, timestamp, bumped, ip, name, tripcode, email,   nameblock, subject, message, password,   file, file_hex, file_original, file_size, file_size_formatted, image_width, image_height, thumb, thumb_width, thumb_height, moderated) " .
		" VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
	$stm->execute(array($post['parent'], $now, $now, $_SERVER['REMOTE_ADDR'], $post['name'], $post['tripcode'], $post['email'],
		$post['nameblock'], $post['subject'], $post['message'], $post['password'],
		$post['file'], $post['file_hex'], $post['file_original'], $post['file_size'], $post['file_size_formatted'],
		$post['image_width'], $post['image_height'], $post['thumb'], $post['thumb_width'], $post['thumb_height'], $post['moderated']));
	return $dbh->lastInsertId();
}

function approvePostByID($id) {
	pdoQuery("UPDATE " . TINYIB_DBPOSTS . " SET moderated = ? WHERE id = ?", array('1', $id));
}

function stickyThreadByID($id, $setsticky) {
	pdoQuery("UPDATE " . TINYIB_DBPOSTS . " SET stickied = ? WHERE id = ?", array($setsticky, $id));
}

function bumpThreadByID($id) {
	$now = time();
	pdoQuery("UPDATE " . TINYIB_DBPOSTS . " SET bumped = ? WHERE id = ?", array($now, $id));
}

function countThreads() {
	$result = pdoQuery("SELECT COUNT(*) FROM " . TINYIB_DBPOSTS . " WHERE parent = 0 AND moderated = 1");
	return (int)$result->fetchColumn();
}

function allThreads() {
	$threads = array();
	$results = pdoQuery("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE parent = 0 AND moderated = 1 ORDER BY stickied DESC, bumped DESC");
	while ($row = $results->fetch()) {
		$threads[] = $row;
	}
	return $threads;
}

function numRepliesToThreadByID($id) {
	$result = pdoQuery("SELECT COUNT(*) FROM " . TINYIB_DBPOSTS . " WHERE parent = ? AND moderated = 1", array($id));
	return (int)$result->fetchColumn();
}

function postsInThreadByID($id, $moderated_only = true) {
	$posts = array();
	$results = pdoQuery("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE (id = ? OR parent = ?)" . ($moderated_only ? " AND moderated = 1" : "") . " ORDER BY id ASC", array($id, $id));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$posts[] = $row;
	}
	return $posts;
}

function postsByHex($hex) {
	$posts = array();
	$results = pdoQuery("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE file_hex = ? AND moderated = 1 LIMIT 1", array($hex));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$posts[] = $row;
	}
	return $posts;
}

function latestPosts($moderated = true) {
	$posts = array();
	$results = pdoQuery("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE moderated = ? ORDER BY timestamp DESC LIMIT 10", array($moderated ? '1' : '0'));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$posts[] = $row;
	}
	return $posts;
}

function deletePostByID($id) {
	$posts = postsInThreadByID($id, false);
	foreach ($posts as $post) {
		if ($post['id'] != $id) {
			deletePostImages($post);
			pdoQuery("DELETE FROM " . TINYIB_DBPOSTS . " WHERE id = ?", array($id));
		} else {
			$thispost = $post;
		}
	}
	if (isset($thispost)) {
		if ($thispost['parent'] == TINYIB_NEWTHREAD) {
			@unlink('res/' . $thispost['id'] . '.html');
		}
		deletePostImages($thispost);
		pdoQuery("DELETE FROM " . TINYIB_DBPOSTS . " WHERE id = ?", array($thispost['id']));
	}
}

function trimThreads() {
	$limit = (int)TINYIB_MAXTHREADS;
	if ($limit > 0) {
		$results = pdoQuery("SELECT id FROM " . TINYIB_DBPOSTS . " WHERE parent = 0 AND moderated = 1 ORDER BY stickied DESC, bumped DESC LIMIT 100 OFFSET " . $limit
		);
		# old mysql, sqlite3: SELECT id FROM $table ORDER BY bumped LIMIT $limit,100
		# mysql, postgresql, sqlite3: SELECT id FROM $table ORDER BY bumped LIMIT 100 OFFSET $limit
		# oracle: SELECT id FROM ( SELECT id, rownum FROM $table ORDER BY bumped) WHERE rownum >= $limit
		# MSSQL: WITH ts AS (SELECT ROWNUMBER() OVER (ORDER BY bumped) AS 'rownum', * FROM $table) SELECT id FROM ts WHERE rownum >= $limit
		foreach ($results as $post) {
			deletePostByID($post['id']);
		}
	}
}

function lastPostByIP() {
	$result = pdoQuery("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE ip = ? ORDER BY id DESC LIMIT 1", array($_SERVER['REMOTE_ADDR']));
	return $result->fetch(PDO::FETCH_ASSOC);
}

# Ban Functions
function banByID($id) {
	$result = pdoQuery("SELECT * FROM " . TINYIB_DBBANS . " WHERE id = ?", array($id));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function banByIP($ip) {
	$result = pdoQuery("SELECT * FROM " . TINYIB_DBBANS . " WHERE ip = ? LIMIT 1", array($ip));
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
	$stm->execute(array($ban['ip'], $now, $ban['expire'], $ban['reason']));
	return $dbh->lastInsertId();
}

function clearExpiredBans() {
	$now = time();
	pdoQuery("DELETE FROM " . TINYIB_DBBANS . " WHERE expire > 0 AND expire <= ?", array($now));
}

function deleteBanByID($id) {
	pdoQuery("DELETE FROM " . TINYIB_DBBANS . " WHERE id = ?", array($id));
}
