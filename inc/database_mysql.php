<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}
try {
  if (TINYIB_DBHOST == "localhost") {
    // if you're using UNIX should use a unix.socket
    $dsn = "mysql:host=;dbname=" . TINYIB_DBNAME;
  }
  else {
    $dsn = "mysql:host=" . TINYIB_DBHOST . ";port=" . TINYIB_DBPORT . ";dbname=" . TINYIB_DBNAME;
  }
  $options = array(PDO::ATTR_PERSISTENT => true,
                   PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                   PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                  );
  $dbh = new PDO($dsn, TINYIB_DBUSERNAME, TINYIB_DBPASSWORD, $options);
}
catch(PDOException $e) {
  fancyDie("could not connect to database: " . $e->getMessage() );
}
#define('TINYIB_DBPOSTS', $dbh->quote(TINYIB_DBPOSTS));
#define('TINYIB_DBBANS',  $dbh->quote(TINYIB_DBBANS));

// Create the posts table if it does not exist
$dbh->query("SHOW TABLES LIKE " . $dbh->quote(TINYIB_DBPOSTS));
if ($dbh->query("SELECT FOUND_ROWS()")->fetchColumn() == 0) {
	$dbh->exec("CREATE TABLE " . TINYIB_DBPOSTS . " (
		id mediumint(7) unsigned NOT NULL auto_increment,
		parent mediumint(7) unsigned NOT NULL,
		timestamp int(20) NOT NULL,
		bumped int(20) NOT NULL,
		ip varchar(15) NOT NULL,
		name varchar(75) NOT NULL,
		tripcode varchar(10) NOT NULL,
		email varchar(75) NOT NULL,
		nameblock varchar(255) NOT NULL,
		subject varchar(75) NOT NULL,
		message text NOT NULL,
		password varchar(255) NOT NULL,
		file varchar(75) NOT NULL,
		file_hex varchar(75) NOT NULL,
		file_original varchar(255) NOT NULL,
		file_size int(20) unsigned NOT NULL default '0',
		file_size_formatted varchar(75) NOT NULL,
		image_width smallint(5) unsigned NOT NULL default '0',
		image_height smallint(5) unsigned NOT NULL default '0',
		thumb varchar(255) NOT NULL,
		thumb_width smallint(5) unsigned NOT NULL default '0',
		thumb_height smallint(5) unsigned NOT NULL default '0',
		PRIMARY KEY	(id),
		KEY parent (parent),
		KEY bumped (bumped)
	) ENGINE=MyISAM");
}

// Create the bans table if it does not exist
$dbh->query("SHOW TABLES LIKE " . $dbh->quote(TINYIB_DBBANS));
if ($dbh->query("SELECT FOUND_ROWS()")->fetchColumn() == 0) {
	$dbh->exec("CREATE TABLE " . TINYIB_DBBANS . " (
		id mediumint(7) unsigned NOT NULL auto_increment,
		ip varchar(15) NOT NULL,
		timestamp int(20) NOT NULL,
		expire int(20) NOT NULL,
		reason text NOT NULL,
		PRIMARY KEY	(id),
		KEY ip (ip)
	) ENGINE=MyISAM");
}

# Utililty
function sqlquery( $sql, $params = false) {
  global $dbh;
  if ($params) {
    $statement = $dbh->prepare($sql);
    $statement->execute($params);
  }
  else {
    $statement = $dbh->query($sql);
  }
  return $statement;
}

# Post Functions
function uniquePosts() {
  $result = sqlquery("SELECT COUNT(DISTINCT(ip)) FROM " . TINYIB_DBPOSTS);
  return (int) $result->fetchColumn();
}

function postByID($id) {
  $result = sqlquery("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE id = ?", array($id,));
	if ($result) {
		return $result->fetch();
	}
}

function threadExistsByID($id) {
	$result = sqlquery("SELECT COUNT(*) FROM " . TINYIB_DBPOSTS . " WHERE id = ? AND parent = 0", array($id,));
  return $result->fetchColumn() != 0;
}

function insertPost($post) {
  global $dbh;
  $now = time();
	$stm = $dbh->prepare("INSERT INTO " . TINYIB_DBPOSTS . 
    " (parent, timestamp, bumped, ip, name, tripcode, email, " .
    "  nameblock, subject, message, password, " .
    "  file, file_hex, file_original, file_size, file_size_formatted, " .
    "  image_width, image_height, thumb, thumb_width, thumb_height) " .
    " VALUES (?, ?, ?, ?, ?, ?, ?, " .
    "         ?, ?, ?, ?, " .
    "         ?, ?, ?, ?, ?, " .
    "         ?, ?, ?, ?, ?)"
  );
  $stm->execute(
    array($post['parent'], $now, $now, $_SERVER['REMOTE_ADDR'], $post['name'], $post['tripcode'],	$post['email'],
          $post['nameblock'], $post['subject'], $post['message'], $post['password'],
          $post['file'], $post['file_hex'], $post['file_original'], $post['file_size'], $post['file_size_formatted'],
          $post['image_width'], $post['image_height'], $post['thumb'], $post['thumb_width'], $post['thumb_height'])
  
  );
  return $dbh->lastInsertId();
}

function bumpThreadByID($id) {
  $now = time();
	sqlquery("UPDATE " . TINYIB_DBPOSTS . " SET bumped = ? WHERE id = ?", array($now, $id));
}

function countThreads() {
  $result = sqlquery("SELECT COUNT(*) FROM " . TINYIB_DBPOSTS . " WHERE parent = 0");
  return (int) $result->fetchColumn();
}

function allThreads() {
	$threads = array();
  $results = sqlquery("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE parent = 0 ORDER BY bumped DESC");
  while($row = $results->fetch()) {
    $threads[] = $row;
  }
	return $threads;
}

function numRepliesToThreadByID($id) {
  $result = sqlquery("SELECT COUNT(*) FROM " . TINYIB_DBPOSTS . " WHERE parent = ?", array($id,));
  return (int) $result->fetchColumn();
}

function postsInThreadByID($id) {
	$posts = array();
	$results = sqlquery("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE id = ? OR parent = ? ORDER BY id ASC", array($id,$id));
  while($row = $results->fetch(PDO::FETCH_ASSOC)) {
    $posts[] = $row;
  }
	return $posts;
}

function postsByHex($hex) {
	$posts = array();
	$results = sqlquery("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE file_hex = ? LIMIT 1", array($hex,));
  while($row = $results->fetch(PDO::FETCH_ASSOC)) {
    $posts[] = $row;
  }
	return $posts;
}

function latestPosts() {
	$posts = array();
	$results = sqlquery("SELECT * FROM " . TINYIB_DBPOSTS . " ORDER BY timestamp DESC LIMIT 10");
  while($row = $results->fetch(PDO::FETCH_ASSOC)) {
    $posts[] = $row;
  }
	return $posts;
}

function deletePostByID($id) {
	$posts = postsInThreadByID($id);
	foreach ($posts as $post) {
		if ($post['id'] != $id) {
			deletePostImages($post);
      sqlquery("DELETE FROM " . TINYIB_DBPOSTS . " WHERE id = ?", array($id,));
		}
    else {
			$thispost = $post;
		}
	}
	if (isset($thispost)) {
		if ($thispost['parent'] == TINYIB_NEWTHREAD) {
			@unlink('res/' . $thispost['id'] . '.html');
		}
		deletePostImages($thispost);
	  sqlquery("DELETE FROM " . TINYIB_DBPOSTS . " WHERE id = ?", array($thispost['id'],));
	}
}

function trimThreads() {
  $limit = (int) TINYIB_MAXTHREADS;
	if ($limit > 0) {
		$results = sqlquery("SELECT id FROM " . TINYIB_DBPOSTS . " WHERE parent = 0 ORDER BY bumped LIMIT 100 OFFSET " . $limit);
		# old mysql, sqlite3: SELECT id FROM $table ORDER BY bumped LIMIT $limit,100
    # mysql, postgresql, sqlite3: SELECT id FROM $table ORDER BY bumped LIMIT 100 OFFSET $limit
    # oracle: SELECT id FROM ( SELECT id, rownum FROM $table ORDER BY bumped) WHERE rownum >= $limit
    # MSSQL: WITH ts AS (SELECT ROWNUMBER() OVER (ORDER BY bumped) AS 'rownum', * FROM $table) SELECT id FROM ts WHERE rownum >= $limit
    foreach($results as $post) {
      deletePostByID($post['id']);
    }
  }
}

function lastPostByIP() {
	$result = sqlquery("SELECT * FROM " . TINYIB_DBPOSTS . " WHERE ip = ? ORDER BY id DESC LIMIT 1", array($_SERVER['REMOTE_ADDR'],));
  return $result->fetch(PDO::FETCH_ASSOC);
}

# Ban Functions
function banByID($id) {
	$result = sqlquery("SELECT * FROM " . TINYIB_DBBANS . " WHERE id = ?", array($id,));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function banByIP($ip) {
	$result = sqlquery("SELECT * FROM " . TINYIB_DBBANS . " WHERE ip = ? LIMIT 1", array($ip,));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function allBans() {
	$bans = array();
	$results = sqlquery("SELECT * FROM " . TINYIB_DBBANS . " ORDER BY timestamp DESC");
  while($row = $results->fetch(PDO::FETCH_ASSOC)) {
    $bans[] = $row;
  }
	return $bans;
}

function insertBan($ban) {
  global $dbh;
  $now = time();
  $stm = $dbh->prepare("INSERT INTO " . TINYIB_DBBANS . 
    " (ip, timestamp, expire, reason) " .
    " VALUES (?, ?, ?, ?)"
  );
  $stm->execute(array($ban['ip'], $now, $ban['expire'], $ban['reason']));
	return $dbh->lastInsertId();
}

function clearExpiredBans() {
  $now = time();
	sqlquery("DELETE FROM " . TINYIB_DBBANS . " WHERE expire > 0 AND expire <= ?", array($now,));
  # "SELECT * FROM `" . TINYIB_DBBANS . "` WHERE `expire` > 0 AND `expire` <= " . time());
	# foreach ($results as $ban)
	#  "DELETE FROM " . TINYIB_DBBANS . " WHERE id = ? LIMIT 1", array($ban['id'],)
}

function deleteBanByID($id) {
  sqlquery("DELETE FROM " . TINYIB_DBBANS . " WHERE id = ?", array($id,));
}
