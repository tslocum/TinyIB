<?php
/*
TinyIB
https://gitlab.com/tslocum/tinyib

MIT License

Copyright (c) 2020 Trevor Slocum <trevor@rocketnine.space>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

use Gettext\Translator;
use Gettext\Translations;

error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
setcookie(session_name(), session_id(), time() + 2592000);
ob_implicit_flush();
if (function_exists('ob_get_level')) {
	while (ob_get_level() > 0) {
		ob_end_flush();
	}
}

function fancyDie($message) {
	$back = 'Click here to go back';
	if (function_exists('__')) {
		$back = __('Click here to go back');
	}
	die('<body text="#800000" bgcolor="#FFFFEE" align="center"><br><div style="display: inline-block; background-color: #F0E0D6;font-size: 1.25em;font-family: Tahoma, Geneva, sans-serif;padding: 7px;border: 1px solid #D9BFB7;border-left: none;border-top: none;">' . $message . '</div><br><br>- <a href="javascript:history.go(-1)">' . $back . '</a> -</body>');
}

if (!file_exists('settings.php')) {
	fancyDie('Please copy the file settings.default.php to settings.php');
}
require 'settings.php';
require 'inc/defines.php';

if (!defined('TINYIB_LOCALE') || TINYIB_LOCALE == '') {
	function __($string) {
		return $string;
	}
} else {
	setlocale(LC_ALL, TINYIB_LOCALE);
	require 'inc/gettext/src/autoloader.php';
	$translations = Translations::fromPoFile('locale/' . TINYIB_LOCALE . '/tinyib.po');
	$translator = new Translator();
	$translator->loadTranslations($translations);
	$translator->register();
}

$database_modes = array('flatfile', 'mysql', 'mysqli', 'sqlite', 'sqlite3', 'pdo');
if (!in_array(TINYIB_DBMODE, $database_modes)) {
	fancyDie(__('Unknown database mode specified.'));
}

if (TINYIB_DBMODE == 'pdo' && TINYIB_DBDRIVER == 'pgsql') {
	$posts_sql = 'CREATE TABLE "' . TINYIB_DBPOSTS . '" (
		"id" bigserial NOT NULL,
		"parent" integer NOT NULL,
		"timestamp" integer NOT NULL,
		"bumped" integer NOT NULL,
		"ip" varchar(255) NOT NULL,
		"name" varchar(75) NOT NULL,
		"tripcode" varchar(24) NOT NULL,
		"email" varchar(75) NOT NULL,
		"nameblock" varchar(255) NOT NULL,
		"subject" varchar(75) NOT NULL,
		"message" text NOT NULL,
		"password" varchar(255) NOT NULL,
		"file" text NOT NULL,
		"file_hex" varchar(75) NOT NULL,
		"file_original" varchar(255) NOT NULL,
		"file_size" integer NOT NULL default \'0\',
		"file_size_formatted" varchar(75) NOT NULL,
		"image_width" smallint NOT NULL default \'0\',
		"image_height" smallint NOT NULL default \'0\',
		"thumb" varchar(255) NOT NULL,
		"thumb_width" smallint NOT NULL default \'0\',
		"thumb_height" smallint NOT NULL default \'0\',
		"moderated" smallint NOT NULL default \'1\',
		"stickied" smallint NOT NULL default \'0\',
		"locked" smallint NOT NULL default \'0\',
		PRIMARY KEY	("id")
	);
	CREATE INDEX ON "' . TINYIB_DBPOSTS . '"("parent");
	CREATE INDEX ON "' . TINYIB_DBPOSTS . '"("bumped");
	CREATE INDEX ON "' . TINYIB_DBPOSTS . '"("stickied");
	CREATE INDEX ON "' . TINYIB_DBPOSTS . '"("moderated");';

	$bans_sql = 'CREATE TABLE "' . TINYIB_DBBANS . '" (
		"id" bigserial NOT NULL,
		"ip" varchar(255) NOT NULL,
		"timestamp" integer NOT NULL,
		"expire" integer NOT NULL,
		"reason" text NOT NULL,
		PRIMARY KEY	("id")
	);
	CREATE INDEX ON "' . TINYIB_DBBANS . '"("ip");';

	$reports_sql = 'CREATE TABLE "' . TINYIB_DBREPORTS . '" (
		"id" bigserial NOT NULL,
		"ip" varchar(255) NOT NULL,
		"post" integer NOT NULL,
		PRIMARY KEY	("id")
	);';

	$keywords_sql = 'CREATE TABLE "' . TINYIB_DBKEYWORDS . '" (
		"id" bigserial NOT NULL,
		"text" varchar(255) NOT NULL,
		"action" varchar(255) NOT NULL,
		PRIMARY KEY	("id")
	);';
} else {
	$posts_sql = "CREATE TABLE `" . TINYIB_DBPOSTS . "` (
		`id` mediumint(7) unsigned NOT NULL auto_increment,
		`parent` mediumint(7) unsigned NOT NULL,
		`timestamp` int(20) NOT NULL,
		`bumped` int(20) NOT NULL,
		`ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`name` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`tripcode` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`email` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`nameblock` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`subject` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`file` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`file_hex` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`file_original` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`file_size` int(20) unsigned NOT NULL default '0',
		`file_size_formatted` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`image_width` smallint(5) unsigned NOT NULL default '0',
		`image_height` smallint(5) unsigned NOT NULL default '0',
		`thumb` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`thumb_width` smallint(5) unsigned NOT NULL default '0',
		`thumb_height` smallint(5) unsigned NOT NULL default '0',
		`stickied` tinyint(1) NOT NULL default '0',
		`moderated` tinyint(1) NOT NULL default '1',
		PRIMARY KEY	(`id`),
		KEY `parent` (`parent`),
		KEY `bumped` (`bumped`),
		KEY `stickied` (`stickied`),
		KEY `moderated` (`moderated`)
	)";

	$bans_sql = "CREATE TABLE `" . TINYIB_DBBANS . "` (
		`id` mediumint(7) unsigned NOT NULL auto_increment,
		`ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`timestamp` int(20) NOT NULL,
		`expire` int(20) NOT NULL,
		`reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		PRIMARY KEY	(`id`),
		KEY `ip` (`ip`)
	)";

	$reports_sql = "CREATE TABLE `" . TINYIB_DBREPORTS . "` (
		`id` mediumint(7) unsigned NOT NULL auto_increment,
		`ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`post` int(20) NOT NULL,
		PRIMARY KEY	(`id`)
	)";

	$keywords_sql = "CREATE TABLE `" . TINYIB_DBKEYWORDS . "` (
		`id` mediumint(7) unsigned NOT NULL auto_increment,
		`text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		PRIMARY KEY	(`id`)
	)";
}

// Check directories are writable by the script
$writedirs = array('res', 'src', 'thumb');
if (TINYIB_DBMODE == 'flatfile') {
	$writedirs[] = 'inc/database/flatfile';
}
foreach ($writedirs as $dir) {
	if (!is_writable($dir)) {
		fancyDie(sprintf(__("Directory '%s' can not be written to.  Please modify its permissions."), $dir));
	}
}

$includes = array('inc/functions.php', 'inc/html.php', 'inc/database/' . TINYIB_DBMODE . '_link.php', 'inc/database/' . TINYIB_DBMODE . '.php');
foreach ($includes as $include) {
	require $include;
}

if (TINYIB_TRIPSEED == '' || TINYIB_ADMINPASS == '') {
	fancyDie(__('TINYIB_TRIPSEED and TINYIB_ADMINPASS must be configured.'));
}

if ((TINYIB_CAPTCHA === 'hcaptcha' || TINYIB_MANAGECAPTCHA === 'hcaptcha') && (TINYIB_HCAPTCHA_SITE == '' || TINYIB_HCAPTCHA_SECRET == '')) {
	fancyDie(__('TINYIB_HCAPTCHA_SITE and TINYIB_HCAPTCHA_SECRET  must be configured.'));
}

if ((TINYIB_CAPTCHA === 'recaptcha' || TINYIB_MANAGECAPTCHA === 'recaptcha') && (TINYIB_RECAPTCHA_SITE == '' || TINYIB_RECAPTCHA_SECRET == '')) {
	fancyDie(__('TINYIB_RECAPTCHA_SITE and TINYIB_RECAPTCHA_SECRET  must be configured.'));
}

if (TINYIB_TIMEZONE != '') {
	date_default_timezone_set(TINYIB_TIMEZONE);
}

$bcrypt_salt = '$2y$12$' . str_pad(str_replace('=', '/', str_replace('+', '.', substr(base64_encode(TINYIB_TRIPSEED), 0, 22))), 22, '/');

$redirect = true;
// Check if the request is to make a post
if (!isset($_GET['delete']) && !isset($_GET['manage']) && (isset($_POST['name']) || isset($_POST['email']) || isset($_POST['subject']) || isset($_POST['message']) || isset($_POST['file']) || isset($_POST['embed']) || isset($_POST['password']))) {
	if (TINYIB_DBMIGRATE) {
		fancyDie(__('Posting is currently disabled.<br>Please try again in a few moments.'));
	}

	list($loggedin, $isadmin) = manageCheckLogIn();
	$rawpost = isRawPost();
	$rawposttext = '';
	if (!$loggedin) {
		checkCAPTCHA(TINYIB_CAPTCHA);
		checkBanned();
		checkFlood();
	}
	if (!$rawpost) {
		checkMessageSize();
	}

	$post = newPost(setParent());

	if (!$loggedin) {
		if ($post['parent'] == TINYIB_NEWTHREAD && TINYIB_DISALLOWTHREADS != '') {
			fancyDie(TINYIB_DISALLOWTHREADS);
		} else if ($post['parent'] != TINYIB_NEWTHREAD && TINYIB_DISALLOWREPLIES != '') {
			fancyDie(TINYIB_DISALLOWREPLIES);
		}
	}

	$hide_fields = $post['parent'] == TINYIB_NEWTHREAD ? $tinyib_hidefieldsop : $tinyib_hidefields;

	if ($post['parent'] != TINYIB_NEWTHREAD && !$loggedin) {
		$parent = postByID($post['parent']);
		if (!isset($parent['locked'])) {
			fancyDie(__('Invalid parent thread ID supplied, unable to create post.'));
		} else if ($parent['locked'] == 1) {
			fancyDie(__('Replies are not allowed to locked threads.'));
		}
	}

	if ($post['name'] == '' && $post['tripcode'] == '') {
		global $tinyib_anonymous;
		$post['name'] = $tinyib_anonymous[array_rand($tinyib_anonymous)];
	}

	$post['ip'] = $_SERVER['REMOTE_ADDR'];

	if ($rawpost || !in_array('name', $hide_fields)) {
		list($post['name'], $post['tripcode']) = nameAndTripcode($_POST['name']);
		$post['name'] = cleanString(substr($post['name'], 0, 75));
		if (!$rawpost && TINYIB_MAXNAME > 0) {
			$post['name'] = substr($post['name'], 0, TINYIB_MAXNAME);
		}
	}
	if ($rawpost || !in_array('email', $hide_fields)) {
		$post['email'] = cleanString(str_replace('"', '&quot;', substr($_POST['email'], 0, 75)));
		if (!$rawpost && TINYIB_MAXEMAIL > 0) {
			$post['email'] = substr($post['email'], 0, TINYIB_MAXEMAIL);
		}
	}
	if ($rawpost || !in_array('subject', $hide_fields)) {
		$post['subject'] = cleanString(substr($_POST['subject'], 0, 75));
		if (!$rawpost && TINYIB_MAXSUBJECT > 0) {
			$post['subject'] = substr($post['subject'], 0, TINYIB_MAXSUBJECT);
		}
	}
	if ($rawpost || !in_array('message', $hide_fields)) {
		$post['message'] = $_POST['message'];
		if ($rawpost) {
			// Treat message as raw HTML
			$rawposttext = ($isadmin) ? ' <span style="color: ' . $tinyib_capcodes[0][1] . ' ;">## ' . $tinyib_capcodes[0][0] . '</span>' : ' <span style="color: ' . $tinyib_capcodes[1][1] . ';">## ' . $tinyib_capcodes[1][0] . '</span>';
		} else {
			if (TINYIB_WORDBREAK > 0) {
				$post['message'] = preg_replace('/([^\s]{' . TINYIB_WORDBREAK . '})(?=[^\s])/', '$1' . TINYIB_WORDBREAK_IDENTIFIER, $post['message']);
			}
			$post['message'] = str_replace("\n", '<br>', makeLinksClickable(colorQuote(postLink(cleanString(rtrim($post['message']))))));
			if (TINYIB_WORDBREAK > 0) {
				$post['message'] = finishWordBreak($post['message']);
			}
		}
	}
	if ($rawpost || !in_array('password', $hide_fields)) {
		$post['password'] = ($_POST['password'] != '') ? hashData($_POST['password']) : '';
	}

	$report_post = false;
	foreach (array($post['name'], $post['email'], $post['subject'], $post['message']) as $field) {
		$keyword = checkKeywords($field);
		if (empty($keyword)) {
			continue;
		}

		$expire = -1;
		switch ($keyword['action']) {
			case 'report':
				$report_post = true;
				break;
			case 'delete':
				fancyDie(__('Your post contains a blocked keyword.'));
			case 'ban0':
				$expire = 0;
				break;
			case 'ban1h':
				$expire = 3600;
				break;
			case 'ban1d':
				$expire = 86400;
				break;
			case 'ban2d':
				$expire = 172800;
				break;
			case 'ban1w':
				$expire = 604800;
				break;
			case 'ban2w':
				$expire = 1209600;
				break;
			case 'ban1m':
				$expire = 2592000;
				break;
		}
		if ($expire >= 0) {
			$ban = array();
			$ban['ip'] = $post['ip'];
			$ban['expire'] = $expire > 0 ? (time() + $expire) : 0;
			$ban['reason'] = 'Keyword: ' . $keyword['text'];
			insertBan($ban);

			$expire_txt = ($ban['expire'] > 0) ? ('<br>This ban will expire ' . strftime(TINYIB_DATEFMT, $ban['expire'])) : '<br>This ban is permanent and will not expire.';
			$reason_txt = ($ban['reason'] == '') ? '' : ('<br>Reason: ' . $ban['reason']);
			fancyDie('Your IP address ' . $_SERVER['REMOTE_ADDR'] . ' has been banned from posting on this image board.  ' . $expire_txt . $reason_txt);
		}
		break;
	}

	$post['nameblock'] = nameBlock($post['name'], $post['tripcode'], $post['email'], time(), $rawposttext);

	if (isset($_POST['embed']) && trim($_POST['embed']) != '' && ($rawpost || !in_array('embed', $hide_fields))) {
		if (isset($_FILES['file']) && $_FILES['file']['name'] != "") {
			fancyDie(__('Embedding a URL and uploading a file at the same time is not supported.'));
		}

		list($service, $embed) = getEmbed(trim($_POST['embed']));
		if (empty($embed) || !isset($embed['html']) || !isset($embed['title']) || !isset($embed['thumbnail_url'])) {
			if (!TINYIB_UPLOADVIAURL) {
				fancyDie(sprintf(__('Invalid embed URL. Only %s URLs are supported.'), implode('/', array_keys($tinyib_embeds))));
			}

			$headers = get_headers(trim($_POST['embed']), true);
			if (TINYIB_MAXKB > 0 && isset($headers['Content-Length']) && intval($headers['Content-Length']) > (TINYIB_MAXKB * 1024)) {
				fancyDie(sprintf(__('That file is larger than %s.'), TINYIB_MAXKBDESC));
			}

			$data = url_get_contents(trim($_POST['embed']));
			if (strlen($data) == 0) {
				fancyDie(__('Failed to download file at specified URL.'));
			}

			if (TINYIB_MAXKB > 0 && strlen($data) > (TINYIB_MAXKB * 1024)) {
				fancyDie(sprintf(__('That file is larger than %s.'), TINYIB_MAXKBDESC));
			}

			$filepath = 'src/' . time() . substr(microtime(), 2, 3) . rand(1000, 9999) . '.txt';
			if (!file_put_contents($filepath, $data)) {
				@unlink($filepath);
				fancyDie(__('Failed to download file at specified URL.'));
			}

			$post = attachFile($post, $filepath, basename(parse_url(trim($_POST['embed']), PHP_URL_PATH)), false);
		} else {
			$post['file_hex'] = $service;
			$temp_file = time() . substr(microtime(), 2, 3);
			$file_location = "thumb/" . $temp_file;
			file_put_contents($file_location, url_get_contents($embed['thumbnail_url']));

			$file_info = getimagesize($file_location);
			$file_mime = mime_content_type($file_location);
			$post['image_width'] = $file_info[0];
			$post['image_height'] = $file_info[1];

			if ($file_mime == "image/jpeg") {
				$post['thumb'] = $temp_file . '.jpg';
			} else if ($file_mime == "image/gif") {
				$post['thumb'] = $temp_file . '.gif';
			} else if ($file_mime == "image/png") {
				$post['thumb'] = $temp_file . '.png';
			} else {
				fancyDie(__('Error while processing audio/video.'));
			}
			$thumb_location = "thumb/" . $post['thumb'];

			list($thumb_maxwidth, $thumb_maxheight) = thumbnailDimensions($post);

			if (!createThumbnail($file_location, $thumb_location, $thumb_maxwidth, $thumb_maxheight)) {
				fancyDie(__('Could not create thumbnail.'));
			}

			addVideoOverlay($thumb_location);

			$thumb_info = getimagesize($thumb_location);
			$post['thumb_width'] = $thumb_info[0];
			$post['thumb_height'] = $thumb_info[1];

			$post['file_original'] = cleanString($embed['title']);
			$post['file'] = str_ireplace(array('src="https://', 'src="http://'), 'src="//', $embed['html']);
		}
	} else if (isset($_FILES['file']) && $_FILES['file']['name'] != "" && ($rawpost || !in_array('file', $hide_fields))) {
		validateFileUpload();

		$post = attachFile($post, $_FILES['file']['tmp_name'], $_FILES['file']['name'], true);
	}

	if ($post['file'] == '') { // No file uploaded
		$file_ok = !empty($tinyib_uploads) && ($rawpost || !in_array('file', $hide_fields));
		$embed_ok = (!empty($tinyib_embeds) || TINYIB_UPLOADVIAURL) && ($rawpost || !in_array('embed', $hide_fields));
		$allowed = '';
		if ($file_ok && $embed_ok) {
			$allowed = __('upload a file or embed a URL');
		} else if ($file_ok) {
			$allowed = __('upload a file');
		} else if ($embed_ok) {
			$allowed = __('embed a URL');
		}
		if ($post['parent'] == TINYIB_NEWTHREAD && $allowed != "" && !TINYIB_NOFILEOK) {
			fancyDie(sprintf(__('Please %s to start a new thread.'), $allowed));
		}
		if (!$rawpost && str_replace('<br>', '', $post['message']) == "") {
			$message_ok = !in_array('message', $hide_fields);
			if ($message_ok) {
				if ($allowed != '') {
					fancyDie(sprintf(__('Please enter a message and/or %s.'), $allowed));
				}
				fancyDie(__('Please enter a message.'));
			}
			fancyDie(sprintf(__('Please %s.'), $allowed));
		}
	} else {
		echo sprintf(__('%s uploaded.'), $post['file_original']) . '<br>';
	}

	if (!$loggedin && (($post['file'] != '' && TINYIB_REQMOD == 'files') || TINYIB_REQMOD == 'all')) {
		$post['moderated'] = '0';
		echo sprintf(__('Your %s will be shown <b>once it has been approved</b>.'), $post['parent'] == TINYIB_NEWTHREAD ? 'thread' : 'post') . '<br>';
		$slow_redirect = true;
	}

	$post['id'] = insertPost($post);

	if ($report_post) {
		$report = array('ip' => $post['ip'], 'post' => $post['id']);
		insertReport($report);
	}

	if ($post['moderated'] == '1') {
		if (TINYIB_ALWAYSNOKO || strtolower($post['email']) == 'noko') {
			$redirect = 'res/' . ($post['parent'] == TINYIB_NEWTHREAD ? $post['id'] : $post['parent']) . '.html#' . $post['id'];
		}

		trimThreads();

		echo __('Updating thread...') . '<br>';
		if ($post['parent'] != TINYIB_NEWTHREAD) {
			rebuildThread($post['parent']);

			if (strtolower($post['email']) != 'sage') {
				if (TINYIB_MAXREPLIES == 0 || numRepliesToThreadByID($post['parent']) <= TINYIB_MAXREPLIES) {
					bumpThreadByID($post['parent']);
				}
			}
		} else {
			rebuildThread($post['id']);
		}

		echo __('Updating index...') . '<br>';
		rebuildIndexes();
	}
// Check if the request is to auto-refresh a thread
} elseif (isset($_GET['posts']) && !isset($_GET['manage'])) {
	if (TINYIB_AUTOREFRESH <= 0) {
		fancyDie(__('Automatic refreshing is disabled.'));
	}

	$thread_id = intval($_GET['posts']);
	$new_since = intval($_GET['since']);
	if ($thread_id <= 0 || $new_since < 0) {
		fancyDie('');
	}

	$json_posts = array();
	$posts = postsInThreadByID($thread_id);
	if ($new_since > 0) {
		foreach ($posts as $i =>  $post) {
			if ($post['id'] <= $new_since) {
				continue;
			}
			$json_posts[$post['id']] = fixLinksInRes(buildPost($post, true));
		}
	}

	echo json_encode($json_posts);
	die();
// Check if the request is to report a post
} elseif (isset($_GET['report']) && !isset($_GET['manage'])) {
	if (!TINYIB_REPORT) {
		fancyDie(__('Reporting is disabled.'));
	}

	$post = postByID($_GET['report']);
	if (!$post) {
		fancyDie(__('Sorry, an invalid post identifier was sent. Please go back, refresh the page, and try again.'));
	}

	$report = reportByIP($post['id'], $_SERVER['REMOTE_ADDR']);
	if (!empty($report)) {
		fancyDie(__('You have already submitted a report for that post.'));
	}

	$report = array('ip' => $_SERVER['REMOTE_ADDR'], 'post' => $post['id']);
	insertReport($report);

	fancyDie(__('Post reported.'));
// Check if the request is to delete a post and/or its associated image
} elseif (isset($_GET['delete']) && !isset($_GET['manage'])) {
	if (!isset($_POST['delete'])) {
		fancyDie(__('Tick the box next to a post and click "Delete" to delete it.'));
	}

	if (TINYIB_DBMIGRATE) {
		fancyDie(__('Post deletion is currently disabled.<br>Please try again in a few moments.'));
	}

	$post = postByID($_POST['delete']);
	if ($post) {
		list($loggedin, $isadmin) = manageCheckLogIn();

		if ($loggedin && $_POST['password'] == '') {
			// Redirect to post moderation page
			echo '--&gt; --&gt; --&gt;<meta http-equiv="refresh" content="0;url=' . basename($_SERVER['PHP_SELF']) . '?manage&moderate=' . $_POST['delete'] . '">';
		} elseif ($post['password'] != '' && (hashData($_POST['password']) == $post['password'] || md5(md5($_POST['password'])) == $post['password'])) {
			deletePost($post['id']);
			if ($post['parent'] == TINYIB_NEWTHREAD) {
				threadUpdated($post['id']);
			} else {
				threadUpdated($post['parent']);
			}
			fancyDie(__('Post deleted.'));
		} else {
			fancyDie(__('Invalid password.'));
		}
	} else {
		fancyDie(__('Sorry, an invalid post identifier was sent. Please go back, refresh the page, and try again.'));
	}

	$redirect = false;
// Check if the request is to access the management area
} elseif (isset($_GET['manage'])) {
	$text = '';
	$onload = '';
	$navbar = '&nbsp;';
	$redirect = false;
	$loggedin = false;
	$isadmin = false;
	$returnlink = basename($_SERVER['PHP_SELF']);

	if (isset($_GET["logout"])) {
		$_SESSION['tinyib'] = '';
		$_SESSION['tinyib_key'] = '';
		session_destroy();
		die('--&gt; --&gt; --&gt;<meta http-equiv="refresh" content="0;url=imgboard.php">');
	}

	list($loggedin, $isadmin) = manageCheckLogIn();

	if ($loggedin) {
		if ($isadmin) {
			if (isset($_GET['rebuildall'])) {
				$allthreads = allThreads();
				foreach ($allthreads as $thread) {
					rebuildThread($thread['id']);
				}
				rebuildIndexes();
				$text .= manageInfo(__('Rebuilt board.'));
			} else if (isset($_GET['reports'])) {
				if (!TINYIB_REPORT) {
					fancyDie(__('Reporting is disabled.'));
				}
				$text .= manageReportsPage($_GET['reports']);
			} elseif (isset($_GET['bans'])) {
				clearExpiredBans();

				if (isset($_POST['ip'])) {
					if ($_POST['ip'] != '') {
						$banexists = banByIP($_POST['ip']);
						if ($banexists) {
							fancyDie(__('Sorry, there is already a ban on record for that IP address.'));
						}

						if (TINYIB_REPORT) {
							deleteReportsByIP($_POST['ip']);
						}

						$ban = array();
						$ban['ip'] = $_POST['ip'];
						$ban['expire'] = ($_POST['expire'] > 0) ? (time() + $_POST['expire']) : 0;
						$ban['reason'] = $_POST['reason'];

						insertBan($ban);
						$text .= manageInfo(sprintf(__('Ban record added for %s'), $ban['ip']));
					}
				} elseif (isset($_GET['lift'])) {
					$ban = banByID($_GET['lift']);
					if ($ban) {
						deleteBanByID($_GET['lift']);
						$text .= manageInfo(sprintf(__('Ban record lifted for %s'), $ban['ip']));
					}
				}

				$onload = manageOnLoad('bans');
				$text .= manageBanForm();
				$text .= manageBansTable();
			} elseif (isset($_GET['keywords'])) {
				if (isset($_POST['text']) && $_POST['text'] != '') {
					if ($_GET['keywords'] > 0) {
						deleteKeyword($_GET['keywords']);
					}

					$keyword_exists = keywordByText($_POST['text']);
					if ($keyword_exists) {
						fancyDie(__('Sorry, that keyword has already been added.'));
					}

					$keyword = array();
					$keyword['text'] = $_POST['text'];
					$keyword['action'] = $_POST['action'];

					insertKeyword($keyword);
					if ($_GET['keywords'] > 0) {
						$text .= manageInfo(__('Keyword updated.'));
						$_GET['keywords'] = 0;
					} else {
						$text .= manageInfo(__('Keyword added.'));
					}
				} elseif (isset($_GET['deletekeyword'])) {
					deleteKeyword($_GET['deletekeyword']);
					$text .= manageInfo(__('Keyword deleted.'));
				}

				$onload = manageOnLoad('keywords');
				if ($_GET['keywords'] > 0) {
					$text .= manageEditKeyword($_GET['keywords']);
				} else {
					$text .= manageEditKeyword(0);
					$text .= manageKeywordsTable();
				}
			} else if (isset($_GET['update'])) {
				if (is_dir('.git')) {
					$git_output = shell_exec('git pull 2>&1');
					$text .= '<blockquote class="reply" style="padding: 7px;font-size: 1.25em;">
					<pre style="margin: 0;padding: 0;">Attempting update...' . "\n\n" . $git_output . '</pre>
					</blockquote>
					<p><b>Note:</b> If TinyIB updates and you have made custom modifications, <a href="https://gitlab.com/tslocum/tinyib/commits/master" target="_blank">review the changes</a> which have been merged into your installation.
					Ensure that your modifications do not interfere with any new/modified files.
					See the <a href="https://gitlab.com/tslocum/tinyib#readme">README</a> for more information.</p>';
				} else {
					$text .= '<p><b>TinyIB was not installed via Git.</b></p>
					<p>If you installed TinyIB without Git, you must <a href="https://gitlab.com/tslocum/tinyib">update manually</a>.  If you did install with Git, ensure the script has read and write access to the <b>.git</b> folder.</p>';
				}
			} elseif (isset($_GET['dbmigrate'])) {
				if (TINYIB_DBMIGRATE !== '' && TINYIB_DBMIGRATE !== false) {
					if (isset($_GET['go'])) {
						if (TINYIB_DBMODE == TINYIB_DBMIGRATE) {
							fancyDie('Set TINYIB_DBMIGRATE to the desired TINYIB_DBMODE and enter in any database related settings in settings.php before migrating.');
						}

						$mysql_modes = array('mysql', 'mysqli');
						if (in_array(TINYIB_DBMODE, $mysql_modes) && in_array(TINYIB_DBMIGRATE, $mysql_modes)) {
							fancyDie('TINYIB_DBMODE and TINYIB_DBMIGRATE are both set to MySQL database modes. No migration is necessary.');
						}

						if (!in_array(TINYIB_DBMIGRATE, $database_modes)) {
							fancyDie(__('Unknown database mode specified.'));
						}
						require 'inc/database/' . TINYIB_DBMIGRATE . '_link.php';

						$threads = allThreads();
						foreach ($threads as $thread) {
							$posts = postsInThreadByID($thread['id']);
							foreach ($posts as $post) {
								migratePost($post);
							}
						}

						$bans = allBans();
						foreach ($bans as $ban) {
							migrateBan($ban);
						}

						echo '<p><b>Database migration complete</b>.  Set TINYIB_DBMODE to mysqli and TINYIB_DBMIGRATE to false, then click <b>Rebuild All</b> above and ensure everything looks the way it should.</p>';
					} else {
						$text .= '<p>Your original database will not be deleted.  If the migration fails, disable the tool and your board will be unaffected.  See the <a href="https://gitlab.com/tslocum/tinyib#migrating" target="_blank">README</a> <small>(<a href="README.md" target="_blank">alternate link</a>)</small> for instructions.</a><br><br><a href="?manage&dbmigrate&go"><b>Start the migration</b></a></p>';
					}
				} else {
					fancyDie('Set TINYIB_DBMIGRATE to true in settings.php to use this feature.');
				}
			}
		}

		if (isset($_GET['delete'])) {
			$post = postByID($_GET['delete']);
			if ($post) {
				deletePost($post['id']);
				if ($post['parent'] == TINYIB_NEWTHREAD) {
					threadUpdated($post['id']);
				} else {
					threadUpdated($post['parent']);
				}
				$text .= manageInfo(sprintf(__('Post No.%d deleted.'), $post['id']));
			} else {
				fancyDie(__("Sorry, there doesn't appear to be a post with that ID."));
			}
		} elseif (isset($_GET['approve'])) {
			if ($_GET['approve'] > 0) {
				$post = postByID($_GET['approve']);
				if ($post) {
					approvePostByID($post['id']);
					$thread_id = $post['parent'] == TINYIB_NEWTHREAD ? $post['id'] : $post['parent'];

					if (strtolower($post['email']) != 'sage' && (TINYIB_MAXREPLIES == 0 || numRepliesToThreadByID($thread_id) <= TINYIB_MAXREPLIES)) {
						bumpThreadByID($thread_id);
					}
					threadUpdated($thread_id);

					$text .= manageInfo(sprintf(__('Post No.%d approved.'), $post['id']));
				} else {
					fancyDie(__("Sorry, there doesn't appear to be a post with that ID."));
				}
			}
		} elseif (isset($_GET['moderate'])) {
			if ($_GET['moderate'] > 0) {
				$post = postByID($_GET['moderate']);
				if ($post) {
					$text .= manageModeratePost($post);
				} else {
					fancyDie(__("Sorry, there doesn't appear to be a post with that ID."));
				}
			} else {
				$onload = manageOnLoad('moderate');
				$text .= manageModeratePostForm();
			}
		} elseif (isset($_GET['sticky']) && isset($_GET['setsticky'])) {
			if ($_GET['sticky'] > 0) {
				$post = postByID($_GET['sticky']);
				if ($post && $post['parent'] == TINYIB_NEWTHREAD) {
					stickyThreadByID($post['id'], intval($_GET['setsticky']));
					threadUpdated($post['id']);

					$text .= manageInfo('Thread No.' . $post['id'] . ' ' . (intval($_GET['setsticky']) == 1 ? 'stickied' : 'un-stickied') . '.');
				} else {
					fancyDie(__("Sorry, there doesn't appear to be a post with that ID."));
				}
			} else {
				fancyDie(__('Form data was lost. Please go back and try again.'));
			}
		} elseif (isset($_GET['lock']) && isset($_GET['setlock'])) {
			if ($_GET['lock'] > 0) {
				$post = postByID($_GET['lock']);
				if ($post && $post['parent'] == TINYIB_NEWTHREAD) {
					lockThreadByID($post['id'], intval($_GET['setlock']));
					threadUpdated($post['id']);

					$text .= manageInfo('Thread No.' . $post['id'] . ' ' . (intval($_GET['setlock']) == 1 ? 'locked' : 'unlocked') . '.');
				} else {
					fancyDie(__("Sorry, there doesn't appear to be a post with that ID."));
				}
			} else {
				fancyDie(__('Form data was lost. Please go back and try again.'));
			}
		} elseif (isset($_GET['clearreports'])) {
			if ($_GET['clearreports'] > 0) {
				$post = postByID($_GET['clearreports']);
				if ($post) {
					deleteReportsByPost($post['id']);

					$text .= manageInfo(__('Reports cleared.'));
				} else {
					fancyDie(__("Sorry, there doesn't appear to be a post with that ID."));
				}
			}
		} elseif (isset($_GET["rawpost"])) {
			$onload = manageOnLoad("rawpost");
			$text .= buildPostForm(0, true);
		}

		if ($text == '') {
			$text = manageStatus();
		}
	} else {
		$onload = manageOnLoad('login');
		$text .= manageLogInForm();
	}

	echo managePage($text, $onload);
} elseif (!file_exists(TINYIB_INDEX) || countThreads() == 0) {
	rebuildIndexes();
}

if ($redirect) {
	echo '--&gt; --&gt; --&gt;<meta http-equiv="refresh" content="' . (isset($slow_redirect) ? '3' : '0') . ';url=' . (is_string($redirect) ? $redirect : TINYIB_INDEX) . '">';
}