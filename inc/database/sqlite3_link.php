<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

if (!extension_loaded('sqlite3')) {
	fancyDie("SQLite3 extension is either not installed or loaded");
}

$db = new SQLite3(TINYIB_DBPATH);
if (!$db) {
	fancyDie("Could not connect to database: " . $db->lastErrorMsg());
}

// Create tables (when necessary)
$result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='" . TINYIB_DBACCOUNTS . "'");
if (!$result->fetchArray()) {
	$db->exec("CREATE TABLE " . TINYIB_DBACCOUNTS . " (
		id INTEGER PRIMARY KEY,
		username TEXT NOT NULL,
		password TEXT NOT NULL,
		role INTEGER NOT NULL,
		lastactive TIMESTAMP NOT NULL
	)");
}

$result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='" . TINYIB_DBBANS . "'");
if (!$result->fetchArray()) {
	$db->exec("CREATE TABLE " . TINYIB_DBBANS . " (
		id INTEGER PRIMARY KEY,
		ip TEXT NOT NULL,
		timestamp TIMESTAMP NOT NULL,
		expire TIMESTAMP NOT NULL,
		reason TEXT NOT NULL
	)");
}

$result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='" . TINYIB_DBKEYWORDS . "'");
if (!$result->fetchArray()) {
	$db->exec("CREATE TABLE " . TINYIB_DBKEYWORDS . " (
		id INTEGER PRIMARY KEY,
		text TEXT NOT NULL,
		action TEXT NOT NULL
	)");
}

$result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='" . TINYIB_DBLOGS . "'");
if (!$result->fetchArray()) {
	$db->exec("CREATE TABLE " . TINYIB_DBLOGS . " (
		id INTEGER PRIMARY KEY,
		timestamp TIMESTAMP NOT NULL,
		account INTEGER NOT NULL,
		message TEXT NOT NULL
	)");
}

$result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='" . TINYIB_DBPOSTS . "'");
if (!$result->fetchArray()) {
	$db->exec("CREATE TABLE " . TINYIB_DBPOSTS . " (
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

$result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='" . TINYIB_DBREPORTS . "'");
if (!$result->fetchArray()) {
	$db->exec("CREATE TABLE " . TINYIB_DBREPORTS . " (
		id INTEGER PRIMARY KEY,
		ip TEXT NOT NULL,
		post INTEGER NOT NULL
	)");
}

// Add moderated column if it isn't present
@$db->exec("ALTER TABLE " . TINYIB_DBPOSTS . " ADD COLUMN moderated INTEGER NOT NULL DEFAULT '0'");

// Add stickied column if it isn't present
@$db->exec("ALTER TABLE " . TINYIB_DBPOSTS . " ADD COLUMN stickied INTEGER NOT NULL DEFAULT '0'");

// Add locked column if it isn't present
@$db->exec("ALTER TABLE " . TINYIB_DBPOSTS . " ADD COLUMN locked INTEGER NOT NULL DEFAULT '0'");

if (function_exists('insertPost')) {
	function migrateAccount($account) {
		global $db;
		$db->exec("INSERT INTO " . TINYIB_DBACCOUNTS . " (id, username, password, role, lastactive) VALUES (" . $db->escapeString($account['id']) . ", '" . $db->escapeString($account['username']) . "', " . $db->escapeString($account['password']) . ", " . $db->escapeString($account['role']) . ", '" . $db->escapeString($account['lastactive']) . "')");
	}

	function migrateBan($ban) {
		global $db;
		$db->exec("INSERT INTO " . TINYIB_DBBANS . " (id, ip, timestamp, expire, reason) VALUES (" . $db->escapeString($ban['id']) . ", '" . $db->escapeString($ban['ip']) . "', " . $db->escapeString($ban['timestamp']) . ", " . $db->escapeString($ban['expire']) . ", '" . $db->escapeString($ban['reason']) . "')");
	}

	function migrateKeyword($keyword) {
		global $db;
		$db->exec("INSERT INTO " . TINYIB_DBKEYWORDS . " (id, text, action) VALUES ('" . $db->escapeString($keyword['id']) . "', '" . $db->escapeString($keyword['text']) . "', '" . $db->escapeString($keyword['action']) . "')");
	}

	function migrateLog($log) {
		global $db;
		$db->exec("INSERT INTO " . TINYIB_DBLOGS . " (id, timestamp, account, message) VALUES ('" . $db->escapeString($log['id']) . "', '" . $db->escapeString($log['timestamp']) . "', '" . $db->escapeString($log['account']) . "', '" . $db->escapeString($log['message']) . "')");
	}

	function migratePost($post) {
		global $db;
		$db->exec("INSERT INTO " . TINYIB_DBPOSTS . " (id, parent, timestamp, bumped, ip, name, tripcode, email, nameblock, subject, message, password, file, file_hex, file_original, file_size, file_size_formatted, image_width, image_height, thumb, thumb_width, thumb_height, moderated, stickied, locked) VALUES (" . $post['id'] . ", " . $post['parent'] . ", " . $post['timestamp'] . ", " . $post['bumped'] . ", '" . $db->escapeString($post['ip']) . "', '" . $db->escapeString($post['name']) . "', '" . $db->escapeString($post['tripcode']) . "',	'" . $db->escapeString($post['email']) . "',	'" . $db->escapeString($post['nameblock']) . "', '" . $db->escapeString($post['subject']) . "', '" . $db->escapeString($post['message']) . "', '" . $db->escapeString($post['password']) . "', '" . $post['file'] . "', '" . $post['file_hex'] . "', '" . $db->escapeString($post['file_original']) . "', " . $post['file_size'] . ", '" . $post['file_size_formatted'] . "', " . $post['image_width'] . ", " . $post['image_height'] . ", '" . $post['thumb'] . "', " . $post['thumb_width'] . ", " . $post['thumb_height'] . ", " . $post['moderated'] . ", " . $post['stickied'] . ", " . $post['locked'] . ")");
	}

	function migrateReport($report) {
		global $db;
		$db->exec("INSERT INTO " . TINYIB_DBREPORTS . " (id, ip, post) VALUES ('" . $db->escapeString($report['id']) . "', '" . $db->escapeString($report['ip']) . "', '" . $db->escapeString($report['post']) . "')");
	}
}
