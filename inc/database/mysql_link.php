<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

if (!function_exists('mysql_connect')) {
	fancyDie("MySQL library is not installed");
}

$link = mysql_connect(TINYIB_DBHOST, TINYIB_DBUSERNAME, TINYIB_DBPASSWORD);
if (!$link) {
	fancyDie("Could not connect to database: " . mysql_error());
}
$db_selected = mysql_select_db(TINYIB_DBNAME, $link);
if (!$db_selected) {
	fancyDie("Could not select database: " . mysql_error());
}
mysql_query("SET NAMES 'utf8mb4'");

// Create tables (when necessary)
if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . TINYIB_DBACCOUNTS . "'")) == 0) {
	mysql_query($accounts_sql);
}

if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . TINYIB_DBBANS . "'")) == 0) {
	mysql_query($bans_sql);
}

if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . TINYIB_DBKEYWORDS . "'")) == 0) {
	mysql_query($keywords_sql);
}

if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . TINYIB_DBLOGS . "'")) == 0) {
	mysql_query($logs_sql);
}

if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . TINYIB_DBPOSTS . "'")) == 0) {
	mysql_query($posts_sql);
}

if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . TINYIB_DBREPORTS . "'")) == 0) {
	mysql_query($reports_sql);
}

if (mysql_num_rows(mysql_query("SHOW COLUMNS FROM `" . TINYIB_DBPOSTS . "` LIKE 'stickied'")) == 0) {
	mysql_query("ALTER TABLE `" . TINYIB_DBPOSTS . "` ADD COLUMN stickied TINYINT(1) NOT NULL DEFAULT '0'");
}

if (mysql_num_rows(mysql_query("SHOW COLUMNS FROM `" . TINYIB_DBPOSTS . "` LIKE 'locked'")) == 0) {
	mysql_query("ALTER TABLE `" . TINYIB_DBPOSTS . "` ADD COLUMN locked TINYINT(1) NOT NULL DEFAULT '0'");
}

mysql_query("ALTER TABLE `" . TINYIB_DBPOSTS . "` MODIFY tripcode VARCHAR(24) NOT NULL DEFAULT ''");

mysql_query("ALTER TABLE `" . TINYIB_DBPOSTS . "` MODIFY ip VARCHAR(255) NOT NULL DEFAULT ''");
mysql_query("ALTER TABLE `" . TINYIB_DBBANS . "` MODIFY ip VARCHAR(255) NOT NULL DEFAULT ''");

if (function_exists('insertPost')) {
	function migrateAccount($account) {
		mysql_query("INSERT INTO " . TINYIB_DBACCOUNTS . " (id, username, password, role, lastactive) VALUES ('" . mysql_real_escape_string($account['id']) . "', '" . mysql_real_escape_string($account['username']) . "', '" . mysql_real_escape_string($account['password']) . "', '" . mysql_real_escape_string($account['role']) . "', '" . mysql_real_escape_string($account['lastactive']) . "')");
	}

	function migrateBan($ban) {
		mysql_query("INSERT INTO " . TINYIB_DBBANS . " (id, ip, timestamp, expire, reason) VALUES (" . mysql_real_escape_string($ban['id']) . "', '" . mysql_real_escape_string($ban['ip']) . "', '" . mysql_real_escape_string($ban['timestamp']) . "', '" . mysql_real_escape_string($ban['expire']) . "', '" . mysql_real_escape_string($ban['reason']) . "')");
	}

	function migrateKeyword($keyword) {
		mysql_query("INSERT INTO " . TINYIB_DBKEYWORDS . " (id, text, action) VALUES ('" . mysql_real_escape_string($keyword['id']) . "', '" . mysql_real_escape_string($keyword['text']) . "', '" . mysql_real_escape_string($keyword['action']) . "')");
	}

	function migrateLog($log) {
		mysql_query("INSERT INTO " . TINYIB_DBLOGS . " (id, timestamp, account, message) VALUES ('" . mysql_real_escape_string($log['id']) . "', '" . mysql_real_escape_string($log['timestamp']) . "', '" . mysql_real_escape_string($log['account']) . "', '" . mysql_real_escape_string($log['message']) . "')");
	}

	function migratePost($post) {
		mysql_query("INSERT INTO " . TINYIB_DBPOSTS . " (id, parent, timestamp, bumped, ip, name, tripcode, email, nameblock, subject, message, password, file, file_hex, file_original, file_size, file_size_formatted, image_width, image_height, thumb, thumb_width, thumb_height, moderated, stickied, locked) VALUES (" . $post['id'] . ", " . $post['parent'] . ", " . $post['timestamp'] . ", " . $post['bumped'] . ", '" . mysql_real_escape_string($post['ip']) . "', '" . mysql_real_escape_string($post['name']) . "', '" . mysql_real_escape_string($post['tripcode']) . "',	'" . mysql_real_escape_string($post['email']) . "',	'" . mysql_real_escape_string($post['nameblock']) . "', '" . mysql_real_escape_string($post['subject']) . "', '" . mysql_real_escape_string($post['message']) . "', '" . mysql_real_escape_string($post['password']) . "', '" . $post['file'] . "', '" . $post['file_hex'] . "', '" . mysql_real_escape_string($post['file_original']) . "', " . $post['file_size'] . ", '" . $post['file_size_formatted'] . "', " . $post['image_width'] . ", " . $post['image_height'] . ", '" . $post['thumb'] . "', " . $post['thumb_width'] . ", " . $post['thumb_height'] . ", " . $post['moderated'] . ", " . $post['stickied'] . ", " . $post['locked'] . ")");
	}

	function migrateReport($report) {
		mysql_query("INSERT INTO " . TINYIB_DBREPORTS . " (id, ip, post) VALUES ('" . mysql_real_escape_string($report['id']) . "', '" . mysql_real_escape_string($report['ip']) . "', '" . mysql_real_escape_string($report['post']) . "')");
	}
}
