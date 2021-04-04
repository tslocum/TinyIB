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
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4');
}

try {
	$dbh = new PDO($dsn, TINYIB_DBUSERNAME, TINYIB_DBPASSWORD, $options);
} catch (PDOException $e) {
	fancyDie("Failed to connect to the database: " . $e->getMessage());
}

// Create tables (when necessary)
if (TINYIB_DBDRIVER === 'pgsql') {
	$query = "SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " . $dbh->quote(TINYIB_DBACCOUNTS);
	$accounts_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(TINYIB_DBACCOUNTS));
	$accounts_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}
if (!$accounts_exists) {
	$dbh->exec($accounts_sql);
}

if (TINYIB_DBDRIVER === 'pgsql') {
	$query = "SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " . $dbh->quote(TINYIB_DBLOGS);
	$logs_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(TINYIB_DBLOGS));
	$logs_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}
if (!$logs_exists) {
	$dbh->exec($logs_sql);
}

if (TINYIB_DBDRIVER === 'pgsql') {
	$query = "SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " . $dbh->quote(TINYIB_DBKEYWORDS);
	$keywords_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(TINYIB_DBKEYWORDS));
	$keywords_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}
if (!$keywords_exists) {
	$dbh->exec($keywords_sql);
}

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

if (TINYIB_DBDRIVER === 'pgsql') {
	$query = "SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " . $dbh->quote(TINYIB_DBREPORTS);
	$reports_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(TINYIB_DBREPORTS));
	$reports_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}
if (!$reports_exists) {
	$dbh->exec($reports_sql);
}

if (TINYIB_DBDRIVER === 'pgsql') {
	$query = "SELECT column_name FROM information_schema.columns WHERE table_name='" . TINYIB_DBPOSTS . "' and column_name='moderated'";
	$moderated_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW COLUMNS FROM `" . TINYIB_DBPOSTS . "` LIKE 'stickied'");
	$moderated_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}

if (!$moderated_exists) {
	$dbh->exec("ALTER TABLE `" . TINYIB_DBPOSTS . "` ADD COLUMN moderated TINYINT(1) NOT NULL DEFAULT '0'");
}
if (TINYIB_DBDRIVER === 'pgsql') {
	$query = "SELECT column_name FROM information_schema.columns WHERE table_name='" . TINYIB_DBPOSTS . "' and column_name='stickied'";
	$stickied_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW COLUMNS FROM `" . TINYIB_DBPOSTS . "` LIKE 'stickied'");
	$stickied_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}

if (!$stickied_exists) {
	$dbh->exec("ALTER TABLE `" . TINYIB_DBPOSTS . "` ADD COLUMN stickied TINYINT(1) NOT NULL DEFAULT '0'");
}

if (TINYIB_DBDRIVER === 'pgsql') {
	$query = "SELECT column_name FROM information_schema.columns WHERE table_name='" . TINYIB_DBPOSTS . "' and column_name='locked'";
	$locked_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW COLUMNS FROM `" . TINYIB_DBPOSTS . "` LIKE 'locked'");
	$locked_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}

if (!$locked_exists) {
	$dbh->exec("ALTER TABLE `" . TINYIB_DBPOSTS . "` ADD COLUMN locked TINYINT(1) NOT NULL DEFAULT '0'");
}

if (TINYIB_DBDRIVER === 'pgsql') {
	$dbh->query("ALTER TABLE `" . TINYIB_DBPOSTS . "` ALTER COLUMN tripcode VARCHAR(24) NOT NULL DEFAULT ''");

	$dbh->query("ALTER TABLE `" . TINYIB_DBPOSTS . "` ALTER COLUMN ip VARCHAR(255) NOT NULL DEFAULT ''");
	$dbh->query("ALTER TABLE `" . TINYIB_DBBANS . "` ALTER COLUMN ip VARCHAR(255) NOT NULL DEFAULT ''");
} else {
	$dbh->query("ALTER TABLE `" . TINYIB_DBPOSTS . "` MODIFY tripcode VARCHAR(24) NOT NULL DEFAULT ''");

	$dbh->query("ALTER TABLE `" . TINYIB_DBPOSTS . "` MODIFY ip VARCHAR(255) NOT NULL DEFAULT ''");
	$dbh->query("ALTER TABLE `" . TINYIB_DBBANS . "` MODIFY ip VARCHAR(255) NOT NULL DEFAULT ''");
}

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

if (function_exists('insertPost')) {
	function migrateAccount($account) {
		global $dbh;
		$stm = $dbh->prepare("INSERT INTO " . TINYIB_DBACCOUNTS . " (id, username, password, role, lastactive) VALUES (?, ?, ?, ?, ?)");
		$stm->execute(array($account['id'], $account['username'], $account['password'], $account['role'], $account['lastactive']));
	}

	function migrateBan($ban) {
		global $dbh;
		$stm = $dbh->prepare("INSERT INTO " . TINYIB_DBBANS . " (id, ip, timestamp, expire, reason) VALUES (?, ?, ?, ?, ?)");
		$stm->execute(array($ban['id'], $ban['ip'], $ban['timestamp'], $ban['expire'], $ban['reason']));
	}

	function migrateKeyword($keyword) {
		global $dbh;
		$stm = $dbh->prepare("INSERT INTO " . TINYIB_DBKEYWORDS . " (id, text, action) VALUES (?, ?, ?)");
		$stm->execute(array($keyword['id'], $keyword['text'], $keyword['action']));
	}

	function migrateLog($log) {
		global $dbh;
		$stm = $dbh->prepare("INSERT INTO " . TINYIB_DBLOGS . " (id, timestamp, account, message) VALUES (?, ?, ?, ?)");
		$stm->execute(array($log['id'], $log['timestamp'], $log['account'], $log['message']));
	}

	function migratePost($post) {
		global $dbh;
		$stm = $dbh->prepare("INSERT INTO " . TINYIB_DBPOSTS . " (id, parent, timestamp, bumped, ip, name, tripcode, email, nameblock, subject, message, password, file, file_hex, file_original, file_size, file_size_formatted, image_width, image_height, thumb, thumb_width, thumb_height, moderated, stickied, locked) " .
			" VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stm->execute(array($post['id'], $post['parent'], $post['timestamp'], $post['bumped'], $post['ip'], $post['name'], $post['tripcode'], $post['email'],
			$post['nameblock'], $post['subject'], $post['message'], $post['password'],
			$post['file'], $post['file_hex'], $post['file_original'], $post['file_size'], $post['file_size_formatted'],
			$post['image_width'], $post['image_height'], $post['thumb'], $post['thumb_width'], $post['thumb_height'], $post['moderated'], $post['stickied'], $post['locked']));
	}

	function migrateReport($report) {
		global $dbh;
		$stm = $dbh->prepare("INSERT INTO " . TINYIB_DBREPORTS . " (id, ip, post) VALUES (?, ?, ?)");
		$stm->execute(array($report['id'], $report['ip'], $report['post']));
	}
}
