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

// Create the posts table if it does not exist
if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . TINYIB_DBPOSTS . "'")) == 0) {
	mysql_query("CREATE TABLE `" . TINYIB_DBPOSTS . "` (
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
if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . TINYIB_DBBANS . "'")) == 0) {
	mysql_query("CREATE TABLE `" . TINYIB_DBBANS . "` (
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
	$row = mysql_fetch_row(mysql_query("SELECT COUNT(DISTINCT(`ip`)) FROM " . TINYIB_DBPOSTS));
	return $row[0];
}

function postByID($id) {
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBPOSTS . "` WHERE `id` = '" . mysql_real_escape_string($id) . "' LIMIT 1");
	if ($result) {
		while ($post = mysql_fetch_assoc($result)) {
			return $post;
		}
	}
}

function threadExistsByID($id) {
	return mysql_result(mysql_query("SELECT COUNT(*) FROM `" . TINYIB_DBPOSTS . "` WHERE `id` = '" . mysql_real_escape_string($id) . "' AND `parent` = 0 LIMIT 1"), 0, 0) > 0;
}

function insertPost($post) {
	mysql_query("INSERT INTO `" . TINYIB_DBPOSTS . "` (`parent`, `timestamp`, `bumped`, `ip`, `name`, `tripcode`, `email`, `nameblock`, `subject`, `message`, `password`, `file`, `file_hex`, `file_original`, `file_size`, `file_size_formatted`, `image_width`, `image_height`, `thumb`, `thumb_width`, `thumb_height`) VALUES (" . $post['parent'] . ", " . time() . ", " . time() . ", '" . $_SERVER['REMOTE_ADDR'] . "', '" . mysql_real_escape_string($post['name']) . "', '" . mysql_real_escape_string($post['tripcode']) . "',	'" . mysql_real_escape_string($post['email']) . "',	'" . mysql_real_escape_string($post['nameblock']) . "', '" . mysql_real_escape_string($post['subject']) . "', '" . mysql_real_escape_string($post['message']) . "', '" . mysql_real_escape_string($post['password']) . "', '" . $post['file'] . "', '" . $post['file_hex'] . "', '" . mysql_real_escape_string($post['file_original']) . "', " . $post['file_size'] . ", '" . $post['file_size_formatted'] . "', " . $post['image_width'] . ", " . $post['image_height'] . ", '" . $post['thumb'] . "', " . $post['thumb_width'] . ", " . $post['thumb_height'] . ")");
	return mysql_insert_id();
}

function bumpThreadByID($id) {
	mysql_query("UPDATE `" . TINYIB_DBPOSTS . "` SET `bumped` = " . time() . " WHERE `id` = " . $id . " LIMIT 1");
}

function countThreads() {
	return mysql_result(mysql_query("SELECT COUNT(*) FROM `" . TINYIB_DBPOSTS . "` WHERE `parent` = 0"), 0, 0);
}

function allThreads() {
	$threads = array();
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBPOSTS . "` WHERE `parent` = 0 ORDER BY `bumped` DESC");
	if ($result) {
		while ($thread = mysql_fetch_assoc($result)) {
			$threads[] = $thread;
		}
	}
	return $threads;
}

function numRepliesToThreadByID($id) {
	return mysql_result(mysql_query("SELECT COUNT(*) FROM `" . TINYIB_DBPOSTS . "` WHERE `parent` = " . $id), 0, 0);
}

function postsInThreadByID($id) {
	$posts = array();
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBPOSTS . "` WHERE `id` = " . $id . " OR `parent` = " . $id . " ORDER BY `id` ASC");
	if ($result) {
		while ($post = mysql_fetch_assoc($result)) {
			$posts[] = $post;
		}
	}
	return $posts;
}

function postsByHex($hex) {
	$posts = array();
	$result = mysql_query("SELECT `id`, `parent` FROM `" . TINYIB_DBPOSTS . "` WHERE `file_hex` = '" . mysql_real_escape_string($hex) . "' LIMIT 1");
	if ($result) {
		while ($post = mysql_fetch_assoc($result)) {
			$posts[] = $post;
		}
	}
	return $posts;
}

function latestPosts() {
	$posts = array();
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBPOSTS . "` ORDER BY `timestamp` DESC LIMIT 10");
	if ($result) {
		while ($post = mysql_fetch_assoc($result)) {
			$posts[] = $post;
		}
	}
	return $posts;
}

function deletePostByID($id) {
	$posts = postsInThreadByID($id);
	foreach ($posts as $post) {
		if ($post['id'] != $id) {
			deletePostImages($post);
			mysql_query("DELETE FROM `" . TINYIB_DBPOSTS . "` WHERE `id` = " . $post['id'] . " LIMIT 1");
		} else {
			$thispost = $post;
		}
	}
	if (isset($thispost)) {
		if ($thispost['parent'] == TINYIB_NEWTHREAD) {
			@unlink('res/' . $thispost['id'] . '.html');
		}
		deletePostImages($thispost);
		mysql_query("DELETE FROM `" . TINYIB_DBPOSTS . "` WHERE `id` = " . $thispost['id'] . " LIMIT 1");
	}
}

function trimThreads() {
	if (TINYIB_MAXTHREADS > 0) {
		$result = mysql_query("SELECT `id` FROM `" . TINYIB_DBPOSTS . "` WHERE `parent` = 0 ORDER BY `bumped` DESC LIMIT " . TINYIB_MAXTHREADS . ", 10");
		if ($result) {
			while ($post = mysql_fetch_assoc($result)) {
				deletePostByID($post['id']);
			}
		}
	}
}

function lastPostByIP() {
	$replies = mysql_query("SELECT * FROM `" . TINYIB_DBPOSTS . "` WHERE `ip` = '" . $_SERVER['REMOTE_ADDR'] . "' ORDER BY `id` DESC LIMIT 1");
	if ($replies) {
		while ($post = mysql_fetch_assoc($replies)) {
			return $post;
		}
	}
}

# Ban Functions
function banByID($id) {
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBBANS . "` WHERE `id` = '" . mysql_real_escape_string($id) . "' LIMIT 1");
	if ($result) {
		while ($ban = mysql_fetch_assoc($result)) {
			return $ban;
		}
	}
}

function banByIP($ip) {
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBBANS . "` WHERE `ip` = '" . mysql_real_escape_string($ip) . "' LIMIT 1");
	if ($result) {
		while ($ban = mysql_fetch_assoc($result)) {
			return $ban;
		}
	}
}

function allBans() {
	$bans = array();
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBBANS . "` ORDER BY `timestamp` DESC");
	if ($result) {
		while ($ban = mysql_fetch_assoc($result)) {
			$bans[] = $ban;
		}
	}
	return $bans;
}

function insertBan($ban) {
	mysql_query("INSERT INTO `" . TINYIB_DBBANS . "` (`ip`, `timestamp`, `expire`, `reason`) VALUES ('" . mysql_real_escape_string($ban['ip']) . "', " . time() . ", '" . mysql_real_escape_string($ban['expire']) . "', '" . mysql_real_escape_string($ban['reason']) . "')");
	return mysql_insert_id();
}

function clearExpiredBans() {
	$result = mysql_query("SELECT * FROM `" . TINYIB_DBBANS . "` WHERE `expire` > 0 AND `expire` <= " . time());
	if ($result) {
		while ($ban = mysql_fetch_assoc($result)) {
			mysql_query("DELETE FROM `" . TINYIB_DBBANS . "` WHERE `id` = " . $ban['id'] . " LIMIT 1");
		}
	}
}

function deleteBanByID($id) {
	mysql_query("DELETE FROM `" . TINYIB_DBBANS . "` WHERE `id` = " . mysql_real_escape_string($id) . " LIMIT 1");
}
