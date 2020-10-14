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

// Create the posts table if it does not exist
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

// Create the bans table if it does not exist
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

// Add moderated column if it isn't present
@$db->exec("ALTER TABLE " . TINYIB_DBPOSTS . " ADD COLUMN moderated INTEGER NOT NULL DEFAULT '0'");

// Add stickied column if it isn't present
@$db->exec("ALTER TABLE " . TINYIB_DBPOSTS . " ADD COLUMN stickied INTEGER NOT NULL DEFAULT '0'");

// Add locked column if it isn't present
@$db->exec("ALTER TABLE " . TINYIB_DBPOSTS . " ADD COLUMN locked INTEGER NOT NULL DEFAULT '0'");

if (function_exists('insertPost')) {
	function migratePost($post) {
		sqlite_query($GLOBALS["db"], "INSERT INTO " . TINYIB_DBPOSTS . " (id, parent, timestamp, bumped, ip, name, tripcode, email, nameblock, subject, message, password, file, file_hex, file_original, file_size, file_size_formatted, image_width, image_height, thumb, thumb_width, thumb_height, moderated, stickied, locked) VALUES (" . $post['id'] . ", " . $post['parent'] . ", " . $post['timestamp'] . ", " . $post['bumped'] . ", '" . sqlite_escape_string($post['ip']) . "', '" . sqlite_escape_string($post['name']) . "', '" . sqlite_escape_string($post['tripcode']) . "',	'" . sqlite_escape_string($post['email']) . "',	'" . sqlite_escape_string($post['nameblock']) . "', '" . sqlite_escape_string($post['subject']) . "', '" . sqlite_escape_string($post['message']) . "', '" . sqlite_escape_string($post['password']) . "', '" . $post['file'] . "', '" . $post['file_hex'] . "', '" . sqlite_escape_string($post['file_original']) . "', " . $post['file_size'] . ", '" . $post['file_size_formatted'] . "', " . $post['image_width'] . ", " . $post['image_height'] . ", '" . $post['thumb'] . "', " . $post['thumb_width'] . ", " . $post['thumb_height'] . ", " . $post['moderated'] . ", " . $post['stickied'] . ", " . $post['locked'] . ")");
	}

	function migrateBan($ban) {
		sqlite_query($GLOBALS["db"], "INSERT INTO " . TINYIB_DBBANS . " (id, ip, timestamp, expire, reason) VALUES (" . $ban['id'] . "', '" . sqlite_escape_string($ban['ip']) . "', '" . $ban['timestamp'] . "', '" . $ban['expire'] . "', '" . sqlite_escape_string($ban['reason']) . "')");
	}
}
