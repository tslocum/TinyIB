<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

if (!function_exists('sqlite_open')) {
	fancyDie("SQLite library is not installed. Try the sqlite3 database mode.");
}

if (!$db = sqlite_open(TINYIB_DBPATH, 0666, $error)) {
	fancyDie("Could not connect to database: " . $error);
}

// Create tables (when necessary)
$result = sqlite_query($db, "SELECT name FROM sqlite_master WHERE type='table' AND name='" . TINYIB_DBACCOUNTS . "'");
if (sqlite_num_rows($result) == 0) {
	sqlite_query($db, "CREATE TABLE " . TINYIB_DBACCOUNTS . " (
		id INTEGER PRIMARY KEY,
		username TEXT NOT NULL,
		password TEXT NOT NULL,
		role INTEGER NOT NULL,
		lastactive TIMESTAMP NOT NULL
	)");
}

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

$result = sqlite_query($db, "SELECT name FROM sqlite_master WHERE type='table' AND name='" . TINYIB_DBKEYWORDS . "'");
if (sqlite_num_rows($result) == 0) {
	sqlite_query($db, "CREATE TABLE " . TINYIB_DBKEYWORDS . " (
		id INTEGER PRIMARY KEY,
		text TEXT NOT NULL,
		action TEXT NOT NULL
	)");
}

$result = sqlite_query($db, "SELECT name FROM sqlite_master WHERE type='table' AND name='" . TINYIB_DBLOGS . "'");
if (sqlite_num_rows($result) == 0) {
	sqlite_query($db, "CREATE TABLE " . TINYIB_DBLOGS . " (
		id INTEGER PRIMARY KEY,
		timestamp TIMESTAMP NOT NULL,
		account INTEGER NOT NULL,
		message TEXT NOT NULL
	)");
}

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
		moderated INTEGER NOT NULL DEFAULT '0',
		stickied INTEGER NOT NULL DEFAULT '0',
		locked INTEGER NOT NULL DEFAULT '0'
	)");
}
$result = sqlite_query($db, "SELECT name FROM sqlite_master WHERE type='table' AND name='" . TINYIB_DBREPORTS . "'");
if (sqlite_num_rows($result) == 0) {
	sqlite_query($db, "CREATE TABLE " . TINYIB_DBREPORTS . " (
		id INTEGER PRIMARY KEY,
		ip TEXT NOT NULL,
		post INTEGER NOT NULL
	)");
}

// Add moderated column if it isn't present
sqlite_query($db, "ALTER TABLE " . TINYIB_DBPOSTS . " ADD COLUMN moderated INTEGER NOT NULL DEFAULT '0'");

// Add stickied column if it isn't present
sqlite_query($db, "ALTER TABLE " . TINYIB_DBPOSTS . " ADD COLUMN stickied INTEGER NOT NULL DEFAULT '0'");

// Add locked column if it isn't present
sqlite_query($db, "ALTER TABLE " . TINYIB_DBPOSTS . " ADD COLUMN locked INTEGER NOT NULL DEFAULT '0'");

if (function_exists('insertPost')) {
	function migrateAccount($account) {
		sqlite_query($GLOBALS["db"], "INSERT INTO " . TINYIB_DBACCOUNTS . " (id, username, password, role, lastactive) VALUES (" . sqlite_escape_string($account['id']) . "', '" . sqlite_escape_string($account['username']) . "', '" . sqlite_escape_string($account['password']) . "', '" . sqlite_escape_string($account['role']) . "', '" . sqlite_escape_string($account['lastactive']) . "')");
	}

	function migrateBan($ban) {
		sqlite_query($GLOBALS["db"], "INSERT INTO " . TINYIB_DBBANS . " (id, ip, timestamp, expire, reason) VALUES (" . sqlite_escape_string($ban['id']) . "', '" . sqlite_escape_string($ban['ip']) . "', '" . sqlite_escape_string($ban['timestamp']) . "', '" . sqlite_escape_string($ban['expire']) . "', '" . sqlite_escape_string($ban['reason']) . "')");
	}

	function migrateKeyword($keyword) {
		sqlite_query($GLOBALS["db"], "INSERT INTO " . TINYIB_DBKEYWORDS . " (id, text, action) VALUES ('" . sqlite_escape_string($keyword['id']) . "', '" . sqlite_escape_string($keyword['text']) . "', '" . sqlite_escape_string($keyword['action']) . "')");
	}

	function migrateLog($log) {
		sqlite_query($GLOBALS["db"], "INSERT INTO " . TINYIB_DBLOGS . " (id, timestamp, account, message) VALUES ('" . sqlite_escape_string($log['id']) . "', '" . sqlite_escape_string($log['timestamp']) . "', '" . sqlite_escape_string($log['account']) . "', '" . sqlite_escape_string($log['message']) . "')");
	}

	function migratePost($post) {
		sqlite_query($GLOBALS["db"], "INSERT INTO " . TINYIB_DBPOSTS . " (id, parent, timestamp, bumped, ip, name, tripcode, email, nameblock, subject, message, password, file, file_hex, file_original, file_size, file_size_formatted, image_width, image_height, thumb, thumb_width, thumb_height, moderated, stickied, locked) VALUES (" . $post['id'] . ", " . $post['parent'] . ", " . $post['timestamp'] . ", " . $post['bumped'] . ", '" . sqlite_escape_string($post['ip']) . "', '" . sqlite_escape_string($post['name']) . "', '" . sqlite_escape_string($post['tripcode']) . "',	'" . sqlite_escape_string($post['email']) . "',	'" . sqlite_escape_string($post['nameblock']) . "', '" . sqlite_escape_string($post['subject']) . "', '" . sqlite_escape_string($post['message']) . "', '" . sqlite_escape_string($post['password']) . "', '" . $post['file'] . "', '" . $post['file_hex'] . "', '" . sqlite_escape_string($post['file_original']) . "', " . $post['file_size'] . ", '" . $post['file_size_formatted'] . "', " . $post['image_width'] . ", " . $post['image_height'] . ", '" . $post['thumb'] . "', " . $post['thumb_width'] . ", " . $post['thumb_height'] . ", " . $post['moderated'] . ", " . $post['stickied'] . ", " . $post['locked'] . ")");
	}

	function migrateReport($report) {
		sqlite_query($GLOBALS["db"], "INSERT INTO " . TINYIB_DBREPORTS . " (id, ip, post) VALUES ('" . sqlite_escape_string($report['id']) . "', '" . sqlite_escape_string($report['ip']) . "', '" . sqlite_escape_string($report['post']) . "')");
	}
}
