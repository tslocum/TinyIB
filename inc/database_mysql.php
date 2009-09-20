<?php
if (!isset($tinyib)) { die(''); }

$link = mysql_connect($mysql_host, $mysql_username, $mysql_password);
if (!$link) {
	fancyDie("Could not connect to database: " . mysql_error());
}
$db_selected = mysql_select_db($mysql_database, $link);
if (!$db_selected) {
	fancyDie("Could not select database: " . mysql_error());
}

// Create the posts table if it does not exist
if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . $mysql_posts_table . "'")) == 0) {
	mysql_query("CREATE TABLE `" . $mysql_posts_table . "` (
		`id` mediumint(7) unsigned NOT NULL auto_increment,
		`parent` mediumint(7) unsigned NOT NULL,
		`timestamp` int(20) NOT NULL,
		`bumped` int(20) NOT NULL,
		`ip` varchar(15) NOT NULL,
		`name` varchar(75) NOT NULL,
		`tripcode` varchar(10) NOT NULL,
		`email` varchar(75) NOT NULL,
		`nameblock` varchar(255) NOT NULL,
		`subject` varchar(75) NOT NULL,
		`message` text NOT NULL,
		`password` varchar(255) NOT NULL,
		`file` varchar(75) NOT NULL,
		`file_hex` varchar(75) NOT NULL,
		`file_original` varchar(255) NOT NULL,
		`file_size` int(20) unsigned NOT NULL default '0',
		`file_size_formatted` varchar(75) NOT NULL,
		`image_width` smallint(5) unsigned NOT NULL default '0',
		`image_height` smallint(5) unsigned NOT NULL default '0',
		`thumb` varchar(255) NOT NULL,
		`thumb_width` smallint(5) unsigned NOT NULL default '0',
		`thumb_height` smallint(5) unsigned NOT NULL default '0',
		PRIMARY KEY	(`id`),
		KEY `parent` (`parent`),
		KEY `bumped` (`bumped`)
	) ENGINE=MyISAM");
}

// Create the bans table if it does not exist
if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . $mysql_bans_table . "'")) == 0) {
	mysql_query("CREATE TABLE `" . $mysql_bans_table . "` (
		`id` mediumint(7) unsigned NOT NULL auto_increment,
		`ip` varchar(15) NOT NULL,
		`timestamp` int(20) NOT NULL,
		`expire` int(20) NOT NULL,
		`reason` text NOT NULL,
		PRIMARY KEY	(`id`),
		KEY `ip` (`ip`)
	) ENGINE=MyISAM");
}

# Post Functions
function uniquePosts() {
	$row = mysql_fetch_row(mysql_query("SELECT COUNT(DISTINCT(`ip`)) FROM " . $GLOBALS['mysql_posts_table']));
	return $row[0];
}

function postByID($id) {
	$result = mysql_query("SELECT * FROM `" . $GLOBALS['mysql_posts_table'] . "` WHERE `id` = '" . mysql_real_escape_string($id) . "' LIMIT 1");
	while ($post = mysql_fetch_assoc($result)) {
		return $post;
	}
}

function threadExistsByID($id) {
	return mysql_result(mysql_query("SELECT COUNT(*) FROM `" . $GLOBALS['mysql_posts_table'] . "` WHERE `id` = '" . mysql_real_escape_string($id) . "' AND `parent` = 0 LIMIT 1"), 0, 0) > 0;
}

function insertPost($post) {
	mysql_query("INSERT INTO `" . $GLOBALS['mysql_posts_table'] . "` (`parent`, `timestamp`, `bumped`, `ip`, `name`, `tripcode`, `email`, `nameblock`, `subject`, `message`, `password`, `file`, `file_hex`, `file_original`, `file_size`, `file_size_formatted`, `image_width`, `image_height`, `thumb`, `thumb_width`, `thumb_height`) VALUES (" . $post['parent'] . ", " . time() . ", " . time() . ", '" . $_SERVER['REMOTE_ADDR'] . "', '" . mysql_real_escape_string($post['name']) . "', '" . mysql_real_escape_string($post['tripcode']) . "',	'" . mysql_real_escape_string($post['email']) . "',	'" . mysql_real_escape_string($post['nameblock']) . "', '" . mysql_real_escape_string($post['subject']) . "', '" . mysql_real_escape_string($post['message']) . "', '" . mysql_real_escape_string($post['password']) . "', '" . $post['file'] . "', '" . $post['file_hex'] . "', '" . mysql_real_escape_string($post['file_original']) . "', " . $post['file_size'] . ", '" . $post['file_size_formatted'] . "', " . $post['image_width'] . ", " . $post['image_height'] . ", '" . $post['thumb'] . "', " . $post['thumb_width'] . ", " . $post['thumb_height'] . ")");
	return mysql_insert_id();
}

function bumpThreadByID($id) {
	mysql_query("UPDATE `" . $GLOBALS['mysql_posts_table'] . "` SET `bumped` = " . time() . " WHERE `id` = " . $id . " LIMIT 1");
}

function countThreads() {	
	return mysql_result(mysql_query("SELECT COUNT(*) FROM `" . $GLOBALS['mysql_posts_table'] . "` WHERE `parent` = 0"), 0, 0);
}

function allThreads() {	
	$threads = array();
	$result = mysql_query("SELECT * FROM `" . $GLOBALS['mysql_posts_table'] . "` WHERE `parent` = 0 ORDER BY `bumped` DESC");
	while ($thread = mysql_fetch_assoc($result)) {
		$threads[] = $thread;
	}
	return $threads;
}

function postsInThreadByID($id) {	
	$posts = array();
	$result = mysql_query("SELECT * FROM `" . $GLOBALS['mysql_posts_table'] . "` WHERE `id` = " . $id . " OR `parent` = " . $id . " ORDER BY `id` ASC");
	while ($post = mysql_fetch_assoc($result)) {
		$posts[] = $post;
	}
	return $posts;
}

function latestRepliesInThreadByID($id) {	
	$posts = array();
	$replies = mysql_query("SELECT * FROM `" . $GLOBALS['mysql_posts_table'] . "` WHERE `parent` = " . $id . " ORDER BY `id` DESC LIMIT 3");
		while ($post = mysql_fetch_assoc($replies)) {
		$posts[] = $post;
	}
	return $posts;
}

function postsByHex($hex) {	
	$posts = array();
	$result = mysql_query("SELECT `id`, `parent` FROM `" . $GLOBALS['mysql_posts_table'] . "` WHERE `file_hex` = '" . mysql_real_escape_string($hex) . "' LIMIT 1");
	while ($post = mysql_fetch_assoc($result)) {
		$posts[] = $post;
	}
	return $posts;
}

function deletePostByID($id) {	
	$posts = postsInThreadByID($id);
	foreach ($posts as $post) {
		if ($post['id'] != $id) {
			deletePostImages($post);
			mysql_query("DELETE FROM `" . $GLOBALS['mysql_posts_table'] . "` WHERE `id` = " . $post['id'] . " LIMIT 1");
		} else {
			$thispost = $post;
		}
	}	if (isset($thispost)) {
		deletePostImages($thispost);
		mysql_query("DELETE FROM `" . $GLOBALS['mysql_posts_table'] . "` WHERE `id` = " . $thispost['id'] . " LIMIT 1");
	}
}

function trimThreads() {
	global $tinyib;
	if ($tinyib['maxthreads'] > 0) {
		$result = mysql_query("SELECT `id` FROM `b_posts` WHERE `parent` = 0 ORDER BY `bumped` DESC LIMIT " . $tinyib['maxthreads']. ", 10");
		while ($post = mysql_fetch_assoc($result)) {
			deletePostByID($post['id']);
		}
	}
}

function lastPostByIP() {	
	$replies = mysql_query("SELECT * FROM `" . $GLOBALS['mysql_posts_table'] . "` WHERE `ip` = '" . $_SERVER['REMOTE_ADDR'] . "' ORDER BY `id` DESC LIMIT 1");
	while ($post = mysql_fetch_assoc($replies)) {
		return $post;
	}
}

# Ban Functions
function banByID($id) {
	$result = mysql_query("SELECT * FROM `" . $GLOBALS['mysql_bans_table'] . "` WHERE `id` = '" . mysql_real_escape_string($id) . "' LIMIT 1");
	while ($ban = mysql_fetch_assoc($result)) {
		return $ban;
	}
}

function banByIP($ip) {
	$result = mysql_query("SELECT * FROM `" . $GLOBALS['mysql_bans_table'] . "` WHERE `ip` = '" . mysql_real_escape_string($ip) . "' LIMIT 1");
	while ($ban = mysql_fetch_assoc($result)) {
		return $ban;
	}
}

function allBans() {
	$bans = array();
	$result = mysql_query("SELECT * FROM `" . $GLOBALS['mysql_bans_table'] . "` ORDER BY `timestamp` DESC");
	while ($ban = mysql_fetch_assoc($result)) {
		$bans[] = $ban;
	}
	return $bans;
}

function insertBan($ban) {
	mysql_query("INSERT INTO `" . $GLOBALS['mysql_bans_table'] . "` (`ip`, `timestamp`, `expire`, `reason`) VALUES ('" . mysql_real_escape_string($ban['ip']) . "', " . time() . ", '" . mysql_real_escape_string($ban['expire']) . "', '" . mysql_real_escape_string($ban['reason']) . "')");
	return mysql_insert_id();
}

function clearExpiredBans() {
	$result = mysql_query("SELECT * FROM `" . $GLOBALS['mysql_bans_table'] . "` WHERE `expire` > 0 AND `expire` <= " . time());
	while ($ban = mysql_fetch_assoc($result)) {
		mysql_query("DELETE FROM `" . $GLOBALS['mysql_bans_table'] . "` WHERE `id` = " . $ban['id'] . " LIMIT 1");
	}
}

function deleteBanByID($id) {
	mysql_query("DELETE FROM `" . $GLOBALS['mysql_bans_table'] . "` WHERE `id` = " . mysql_real_escape_string($id) . " LIMIT 1");
}

?>