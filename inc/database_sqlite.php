<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

if (!function_exists('sqlite_open')) {
	fancyDie("SQLite library is not installed");
}

if (!$db = sqlite_open('tinyib.db', 0666, $error)) {
	fancyDie("Could not connect to database: " . $error);
}

// Create the posts table if it does not exist
$result = sqlite_query($db, "SELECT name FROM sqlite_master WHERE type='table' AND name='" . TINYIB_DBPOSTS . "'");
if (sqlite_num_rows($result) == 0) {
	sqlite_query($db, "CREATE TABLE " . TINYIB_DBPOSTS . " (
		id INTEGER PRIMARY KEY,
		parent INTEGER NOT NULL,
		timestamp TIMESTAMP NOT NULL,
		bumped TIMESTAMP NOT NULL,
		ip TEXT NOT NULL,
		name TEXT NOT NULL,
		tripcode TEXT NOT NULL,
		email TEXT NOT NULL,
		nameblock TEXT NOT NULL,
		subject TEXT NOT NULL,
		message TEXT NOT NULL,
		password TEXT NOT NULL,
		file TEXT NOT NULL,
		file_hex TEXT NOT NULL,
		file_original TEXT NOT NULL,
		file_size INTEGER NOT NULL DEFAULT '0',
		file_size_formatted TEXT NOT NULL,
		image_width INTEGER NOT NULL DEFAULT '0',
		image_height INTEGER NOT NULL DEFAULT '0',
		thumb TEXT NOT NULL,
		thumb_width INTEGER NOT NULL DEFAULT '0',
		thumb_height INTEGER NOT NULL DEFAULT '0',
		stickied INTEGER NOT NULL DEFAULT '0'
	)");
}

// Create the bans table if it does not exist
$result = sqlite_query($db, "SELECT name FROM sqlite_master WHERE type='table' AND name='" . TINYIB_DBBANS . "'");
if (sqlite_num_rows($result) == 0) {
	sqlite_query($db, "CREATE TABLE " . TINYIB_DBBANS . " (
		id INTEGER PRIMARY KEY,
		ip TEXT NOT NULL,
		timestamp TIMESTAMP NOT NULL,
		expire TIMESTAMP NOT NULL,
		reason TEXT NOT NULL
	)");
}

// Add stickied column if it isn't present
sqlite_query($db, "ALTER TABLE " . TINYIB_DBPOSTS . " ADD COLUMN stickied INTEGER NOT NULL DEFAULT '0'");

# Post Functions
function uniquePosts() {
	return sqlite_fetch_single(sqlite_query($GLOBALS["db"], "SELECT COUNT(ip) FROM (SELECT DISTINCT ip FROM " . TINYIB_DBPOSTS . ")"));
}

function postByID($id) {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . TINYIB_DBPOSTS . " WHERE id = '" . sqlite_escape_string($id) . "' LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $post) {
		return $post;
	}
}

function threadExistsByID($id) {
	return sqlite_fetch_single(sqlite_query($GLOBALS["db"], "SELECT COUNT(*) FROM " . TINYIB_DBPOSTS . " WHERE id = '" . sqlite_escape_string($id) . "' AND parent = 0 LIMIT 1")) > 0;
}

function insertPost($post) {
	sqlite_query($GLOBALS["db"], "INSERT INTO " . TINYIB_DBPOSTS . " (parent, timestamp, bumped, ip, name, tripcode, email, nameblock, subject, message, password, file, file_hex, file_original, file_size, file_size_formatted, image_width, image_height, thumb, thumb_width, thumb_height) VALUES (" . $post['parent'] . ", " . time() . ", " . time() . ", '" . $_SERVER['REMOTE_ADDR'] . "', '" . sqlite_escape_string($post['name']) . "', '" . sqlite_escape_string($post['tripcode']) . "',	'" . sqlite_escape_string($post['email']) . "',	'" . sqlite_escape_string($post['nameblock']) . "', '" . sqlite_escape_string($post['subject']) . "', '" . sqlite_escape_string($post['message']) . "', '" . sqlite_escape_string($post['password']) . "', '" . $post['file'] . "', '" . $post['file_hex'] . "', '" . sqlite_escape_string($post['file_original']) . "', " . $post['file_size'] . ", '" . $post['file_size_formatted'] . "', " . $post['image_width'] . ", " . $post['image_height'] . ", '" . $post['thumb'] . "', " . $post['thumb_width'] . ", " . $post['thumb_height'] . ")");
	return sqlite_last_insert_rowid($GLOBALS["db"]);
}

function stickyThreadByID($id, $setsticky) {
	sqlite_query($GLOBALS["db"], "UPDATE " . TINYIB_DBPOSTS . " SET stickied = '" . sqlite_escape_string($setsticky) . "' WHERE id = " . $id);
}

function bumpThreadByID($id) {
	sqlite_query($GLOBALS["db"], "UPDATE " . TINYIB_DBPOSTS . " SET bumped = " . time() . " WHERE id = " . $id);
}

function countThreads() {
	return sqlite_fetch_single(sqlite_query($GLOBALS["db"], "SELECT COUNT(*) FROM " . TINYIB_DBPOSTS . " WHERE parent = 0"));
}

function allThreads() {
	$threads = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . TINYIB_DBPOSTS . " WHERE parent = 0 ORDER BY stickied DESC, bumped DESC"), SQLITE_ASSOC);
	foreach ($result as $thread) {
		$threads[] = $thread;
	}
	return $threads;
}

function numRepliesToThreadByID($id) {
	return sqlite_fetch_single(sqlite_query($GLOBALS["db"], "SELECT COUNT(*) FROM " . TINYIB_DBPOSTS . " WHERE parent = " . $id));
}

function postsInThreadByID($id, $moderated_only = true) {
	$posts = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . TINYIB_DBPOSTS . " WHERE id = " . $id . " OR parent = " . $id . " ORDER BY id ASC"), SQLITE_ASSOC);
	foreach ($result as $post) {
		$posts[] = $post;
	}
	return $posts;
}

function postsByHex($hex) {
	$posts = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT id, parent FROM " . TINYIB_DBPOSTS . " WHERE file_hex = '" . sqlite_escape_string($hex) . "' LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $post) {
		$posts[] = $post;
	}
	return $posts;
}

function latestPosts($moderated = true) {
	$posts = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . TINYIB_DBPOSTS . " ORDER BY timestamp DESC LIMIT 10"), SQLITE_ASSOC);
	foreach ($result as $post) {
		$posts[] = $post;
	}
	return $posts;
}

function deletePostByID($id) {
	$posts = postsInThreadByID($id, false);
	foreach ($posts as $post) {
		if ($post['id'] != $id) {
			deletePostImages($post);
			sqlite_query($GLOBALS["db"], "DELETE FROM " . TINYIB_DBPOSTS . " WHERE id = " . $post['id']);
		} else {
			$thispost = $post;
		}
	}
	if (isset($thispost)) {
		if ($thispost['parent'] == TINYIB_NEWTHREAD) {
			@unlink('res/' . $thispost['id'] . '.html');
		}
		deletePostImages($thispost);
		sqlite_query($GLOBALS["db"], "DELETE FROM " . TINYIB_DBPOSTS . " WHERE id = " . $thispost['id']);
	}
}

function trimThreads() {
	if (TINYIB_MAXTHREADS > 0) {
		$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT id FROM " . TINYIB_DBPOSTS . " WHERE parent = 0 ORDER BY stickied DESC, bumped DESC LIMIT " . TINYIB_MAXTHREADS . ", 10"), SQLITE_ASSOC);
		foreach ($result as $post) {
			deletePostByID($post['id']);
		}
	}
}

function lastPostByIP() {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . TINYIB_DBPOSTS . " WHERE ip = '" . $_SERVER['REMOTE_ADDR'] . "' ORDER BY id DESC LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $post) {
		return $post;
	}
}

# Ban Functions
function banByID($id) {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . TINYIB_DBBANS . " WHERE id = '" . sqlite_escape_string($id) . "' LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $ban) {
		return $ban;
	}
}

function banByIP($ip) {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . TINYIB_DBBANS . " WHERE ip = '" . sqlite_escape_string($ip) . "' LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $ban) {
		return $ban;
	}
}

function allBans() {
	$bans = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . TINYIB_DBBANS . " ORDER BY timestamp DESC"), SQLITE_ASSOC);
	foreach ($result as $ban) {
		$bans[] = $ban;
	}
	return $bans;
}

function insertBan($ban) {
	sqlite_query($GLOBALS["db"], "INSERT INTO " . TINYIB_DBBANS . " (ip, timestamp, expire, reason) VALUES ('" . sqlite_escape_string($ban['ip']) . "', " . time() . ", '" . sqlite_escape_string($ban['expire']) . "', '" . sqlite_escape_string($ban['reason']) . "')");
	return sqlite_last_insert_rowid($GLOBALS["db"]);
}

function clearExpiredBans() {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . TINYIB_DBBANS . " WHERE expire > 0 AND expire <= " . time()), SQLITE_ASSOC);
	foreach ($result as $ban) {
		sqlite_query($GLOBALS["db"], "DELETE FROM " . TINYIB_DBBANS . " WHERE id = " . $ban['id']);
	}
}

function deleteBanByID($id) {
	sqlite_query($GLOBALS["db"], "DELETE FROM " . TINYIB_DBBANS . " WHERE id = " . sqlite_escape_string($id));
}
