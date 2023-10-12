<?php
/*
TinyIB
https://code.rocket9labs.com/tslocum/tinyib

MIT License

Copyright (c) 2020 Trevor Slocum <trevor@rocket9labs.com>

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

error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
setcookie(session_name(), session_id(), time() + 2592000);
ob_implicit_flush();
while (ob_get_level() > 0) {
	ob_end_flush();
}

function fancyDie($message, $go_back = 1) {
	$go_back_text = 'Click here to go back';
	if (function_exists('__')) {
		$go_back_text = __('Click here to go back');
	}
	die('<body text="#800000" bgcolor="#FFFFEE" align="center"><br><div style="display: inline-block; background-color: #F0E0D6;font-size: 1.25em;font-family: Tahoma, Geneva, sans-serif;padding: 7px;border: 1px solid #D9BFB7;border-left: none;border-top: none;">' . $message . '</div><br><br>- <a href="javascript:history.go(-' . $go_back . ')">' . $go_back_text . '</a> -</body>');
}

if (!file_exists('settings.php')) {
	fancyDie('Please copy the file settings.default.php to settings.php');
}
require 'settings.php';
require 'inc/defines.php';
global $tinyib_capcodes, $tinyib_embeds, $tinyib_hidefields, $tinyib_hidefieldsop;

if (!defined('TINYIB_LOCALE') || TINYIB_LOCALE == '') {
	function __($string) {
		return $string;
	}
} else {
	require 'inc/gettext.php';
}

if ((TINYIB_CAPTCHA === 'hcaptcha' || TINYIB_REPLYCAPTCHA === 'hcaptcha' || TINYIB_MANAGECAPTCHA === 'hcaptcha') && (TINYIB_HCAPTCHA_SITE == '' || TINYIB_HCAPTCHA_SECRET == '')) {
	fancyDie(__('TINYIB_HCAPTCHA_SITE and TINYIB_HCAPTCHA_SECRET  must be configured.'));
}

if ((TINYIB_CAPTCHA === 'recaptcha' || TINYIB_REPLYCAPTCHA === 'recaptcha' || TINYIB_MANAGECAPTCHA === 'recaptcha') && (TINYIB_RECAPTCHA_SITE == '' || TINYIB_RECAPTCHA_SECRET == '')) {
	fancyDie(__('TINYIB_RECAPTCHA_SITE and TINYIB_RECAPTCHA_SECRET  must be configured.'));
}

if (TINYIB_TIMEZONE != '') {
	date_default_timezone_set(TINYIB_TIMEZONE);
}

if (TINYIB_TRIPSEED == '') {
	fancyDie(__('TINYIB_TRIPSEED must be configured.'));
}

$bcrypt_salt = '$2y$12$' . str_pad(str_replace('=', '/', str_replace('+', '.', substr(base64_encode(TINYIB_TRIPSEED), 0, 22))), 22, '/');

$database_modes = array('flatfile', 'mysql', 'mysqli', 'sqlite', 'sqlite3', 'pdo');
if (!in_array(TINYIB_DBMODE, $database_modes)) {
	fancyDie(__('Unknown database mode specified.'));
}

if (TINYIB_DBMODE == 'pdo' && TINYIB_DBDRIVER == 'pgsql') {
	$accounts_sql = 'CREATE TABLE "' . TINYIB_DBACCOUNTS . '" (
		"id" bigserial NOT NULL,
		"username" varchar(255) NOT NULL,
		"password" text NOT NULL,
		"role" integer NOT NULL,
		"lastactive" integer NOT NULL,
		PRIMARY KEY	("id")
	);';

	$bans_sql = 'CREATE TABLE "' . TINYIB_DBBANS . '" (
		"id" bigserial NOT NULL,
		"ip" varchar(255) NOT NULL,
		"timestamp" integer NOT NULL,
		"expire" integer NOT NULL,
		"reason" text NOT NULL,
		PRIMARY KEY	("id")
	);
	CREATE INDEX ON "' . TINYIB_DBBANS . '"("ip");';

	$keywords_sql = 'CREATE TABLE "' . TINYIB_DBKEYWORDS . '" (
		"id" bigserial NOT NULL,
		"text" varchar(255) NOT NULL,
		"action" varchar(255) NOT NULL,
		PRIMARY KEY	("id")
	);';

	$logs_sql = 'CREATE TABLE "' . TINYIB_DBLOGS . '" (
		"id" bigserial NOT NULL,
		"timestamp" integer NOT NULL,
		"account" integer NOT NULL,
		"message" text NOT NULL,
		PRIMARY KEY	("id")
	);
	CREATE INDEX ON "' . TINYIB_DBLOGS . '"("account");';

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

	$reports_sql = 'CREATE TABLE "' . TINYIB_DBREPORTS . '" (
		"id" bigserial NOT NULL,
		"ip" varchar(255) NOT NULL,
		"post" integer NOT NULL,
		PRIMARY KEY	("id")
	);';
} else {
	$accounts_sql = "CREATE TABLE `" . TINYIB_DBACCOUNTS . "` (
		`id` mediumint(7) unsigned NOT NULL auto_increment,
		`username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`password` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`role` mediumint(7) unsigned NOT NULL,
		`lastactive` int(20) unsigned NOT NULL,
		PRIMARY KEY	(`id`)
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

	$keywords_sql = "CREATE TABLE `" . TINYIB_DBKEYWORDS . "` (
		`id` mediumint(7) unsigned NOT NULL auto_increment,
		`text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		PRIMARY KEY	(`id`)
	)";

	$logs_sql = "CREATE TABLE `" . TINYIB_DBLOGS . "` (
		`id` mediumint(7) unsigned NOT NULL auto_increment,
		`timestamp` int(20),
		`account` mediumint(7) unsigned NOT NULL,
		`message` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		PRIMARY KEY	(`id`),
		KEY `account` (`account`)
	)";

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

	$reports_sql = "CREATE TABLE `" . TINYIB_DBREPORTS . "` (
		`id` mediumint(7) unsigned NOT NULL auto_increment,
		`ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`post` int(20) NOT NULL,
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

$includes = array('inc/functions.php', 'inc/html.php', 'inc/database/' . TINYIB_DBMODE . '_link.php', 'inc/database/' . TINYIB_DBMODE . '.php', 'inc/database/database.php');
foreach ($includes as $include) {
	require $include;
}

list($account, $loggedin, $isadmin) = manageCheckLogIn(false);

if (!$loggedin) {
	checkBanned();
}

$redirect = true;
// Check if the request is to make a post
if (!isset($_GET['delete']) && !isset($_GET['manage']) && (isset($_POST['name']) || isset($_POST['email']) || isset($_POST['subject']) || isset($_POST['message']) || isset($_POST['file']) || isset($_POST['embed']) || isset($_POST['password']))) {
	$lock = lockDatabase();

	if (TINYIB_DBMIGRATE) {
		fancyDie(__('Posting is currently disabled.<br>Please try again in a few moments.'));
	}

	$staffpost = isStaffPost();
	$capcode = '';
	if (!$staffpost) {
		checkMessageSize();
	}

	$post = newPost(setParent());

	if (!$loggedin) {
		checkCAPTCHA($post['parent'] == TINYIB_NEWTHREAD ? TINYIB_CAPTCHA : TINYIB_REPLYCAPTCHA);
		checkFlood();
	}

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

	$post['ip'] = remoteAddress();

	$spoiler = TINYIB_SPOILERIMAGE && isset($_POST['spoiler']);

	if ($staffpost || !in_array('name', $hide_fields)) {
		list($post['name'], $post['tripcode']) = nameAndTripcode($_POST['name']);
		if (TINYIB_MAXNAME > 0) {
			$post['name'] = _substr($post['name'], 0, TINYIB_MAXNAME);
		}
		$post['name'] = cleanString($post['name']);
	}
	if ($staffpost || !in_array('email', $hide_fields)) {
		$post['email'] = $_POST['email'];
		if (TINYIB_MAXEMAIL > 0) {
			$post['email'] = _substr($post['email'], 0, TINYIB_MAXEMAIL);
		}
		$post['email'] = cleanString(str_replace('"', '&quot;', $post['email']));
	}
	if ($staffpost) {
		$capcode = ($isadmin) ? ' <span style="color: ' . $tinyib_capcodes[0][1] . ' ;">## ' . $tinyib_capcodes[0][0] . '</span>' : ' <span style="color: ' . $tinyib_capcodes[1][1] . ';">## ' . $tinyib_capcodes[1][0] . '</span>';
	}
	if ($staffpost || !in_array('subject', $hide_fields)) {
		$post['subject'] = $_POST['subject'];
		if (TINYIB_MAXSUBJECT > 0) {
			$post['subject'] = _substr($post['subject'], 0, TINYIB_MAXSUBJECT);
		}
		$post['subject'] = cleanString($post['subject']);
	}
	if ($staffpost || !in_array('message', $hide_fields)) {
		$post['message'] = $_POST['message'];
		if ($staffpost && isset($_POST['raw'])) {
			// Treat message as raw HTML
		} else {
			if (TINYIB_WORDBREAK > 0) {
				$post['message'] = preg_replace('/([^\s]{' . TINYIB_WORDBREAK . '})(?=[^\s])/u', '$1' . TINYIB_WORDBREAK_IDENTIFIER, $post['message']);
			}
			$post['message'] = str_replace("\n", '<br>', makeLinksClickable(colorQuote(postLink(cleanString(rtrim($post['message']))))));

			if (TINYIB_SPOILERTEXT) {
				$post['message'] = preg_replace('/&lt;s&gt;(.*?)&lt;\/s&gt;/i', '<span class="spoiler">$1</span>', $post['message']);
				$post['message'] = preg_replace('/&lt;spoiler&gt;(.*?)&lt;\/spoiler&gt;/i', '<span class="spoiler">$1</span>', $post['message']);
				$post['message'] = preg_replace('/&lt;spoilers&gt;(.*?)&lt;\/spoilers&gt;/i', '<span class="spoiler">$1</span>', $post['message']);
			}

			if (TINYIB_WORDBREAK > 0) {
				$post['message'] = finishWordBreak($post['message']);
			}
		}
	}
	if ($staffpost || !in_array('password', $hide_fields)) {
		$post['password'] = ($_POST['password'] != '') ? hashData($_POST['password']) : '';
	}

	$hide_post = false;
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
			case 'hide':
				$hide_post = true;
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
			$ban['reason'] = __('Keyword') . ': ' . $keyword['text'];
			insertBan($ban);

			if ($ban['expire'] > 0) {
				$bannedText = sprintf(__('Your IP address (%1$s) is banned until %2$s.'), remoteAddress(), formatDate($ban['expire']));
			} else {
				$bannedText = sprintf(__('Your IP address (%s) is permanently banned.'), remoteAddress());
			}
			if ($ban['reason'] != '') {
				$bannedText .= '<br>' . __('Reason') . ': ' . $ban['reason'];
			}
			fancyDie($bannedText);
		}
		break;
	}

	$post['nameblock'] = nameBlock($post['name'], $post['tripcode'], $post['email'], time(), $capcode);

	if (isset($_POST['embed']) && trim($_POST['embed']) != '' && ($staffpost || !in_array('embed', $hide_fields))) {
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

			$post = attachFile($post, $filepath, basename(parse_url(trim($_POST['embed']), PHP_URL_PATH)), false, $spoiler);
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

			if (!createThumbnail($file_location, $thumb_location, $thumb_maxwidth, $thumb_maxheight, $spoiler)) {
				fancyDie(__('Could not create thumbnail.'));
			}

			addVideoOverlay($thumb_location);

			$thumb_info = getimagesize($thumb_location);
			$post['thumb_width'] = $thumb_info[0];
			$post['thumb_height'] = $thumb_info[1];

			$post['file_original'] = cleanString($embed['title']);
			$post['file'] = str_ireplace(array('src="https://', 'src="http://'), 'src="//', $embed['html']);
		}
	} else if (isset($_FILES['file']) && $_FILES['file']['name'] != "" && ($staffpost || !in_array('file', $hide_fields))) {
		validateFileUpload();

		$post = attachFile($post, $_FILES['file']['tmp_name'], $_FILES['file']['name'], true, $spoiler);
	}

	if ($post['file'] == '') { // No file uploaded
		$file_ok = !empty($tinyib_uploads) && ($staffpost || !in_array('file', $hide_fields));
		$embed_ok = (!empty($tinyib_embeds) || TINYIB_UPLOADVIAURL) && ($staffpost || !in_array('embed', $hide_fields));
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
		if (!$staffpost && str_replace('<br>', '', $post['message']) == "") {
			$message_ok = !in_array('message', $hide_fields);
			if ($message_ok) {
				if ($allowed != '') {
					fancyDie(sprintf(__('Please enter a message and/or %s.'), $allowed));
				}
				fancyDie(__('Please enter a message.'));
			}
			fancyDie(sprintf(__('Please %s.'), $allowed));
		}
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
		checkAutoHide($post);
	}

	if ($hide_post) {
		approvePostByID($post['id'], 0);
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

	if ($staffpost) {
		manageLogAction(__('Created staff post') . ' ' . postLink('&gt;&gt;' . $post['id']));
	}
// Check if the request is to preview a post
} elseif (isset($_GET['preview']) && !isset($_GET['manage'])) {
	$post = postByID(intval($_GET['preview']));
	if (empty($post)) {
		die(__('This post has been deleted'));
	} else if ($post['moderated'] == 0 && !$isadmin) {
		die(__('This post requires moderation before it can be displayed'));
	}

	$html = buildPost($post, isset($_GET['res']), true);
	if (isset($_GET['res'])) {
		$html = fixLinksInRes($html);
	}

	echo $html;
	die();
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
		foreach ($posts as $i => $post) {
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
	$lock = lockDatabase();

	if (!TINYIB_REPORT) {
		fancyDie(__('Reporting is disabled.'));
	}

	$post = postByID($_GET['report']);
	if (!$post) {
		fancyDie(__('Sorry, an invalid post identifier was sent. Please go back, refresh the page, and try again.'));
	}

	if ($post['moderated'] == 2) {
		fancyDie(__('Moderators have determined that post does not break any rules.'));
	}

	$report = reportByIP($post['id'], remoteAddress());
	if (!empty($report)) {
		fancyDie(__('You have already submitted a report for that post.'));
	}

	$go_back = 1;
	if (TINYIB_REPORTCAPTCHA != '') {
		if (isset($_GET['verify'])) {
			checkCAPTCHA(TINYIB_REPORTCAPTCHA);
			$go_back = 2;
		} else {
			if (TINYIB_REPORTCAPTCHA === 'hcaptcha') {
				$captcha = '
<br>
<div style="min-height: 82px;">
	<div class="h-captcha" data-sitekey="' . TINYIB_HCAPTCHA_SITE . '"></div>
</div><br><br>';
			} else if (TINYIB_REPORTCAPTCHA === 'recaptcha') {
				$captcha = '
<br>
<div style="min-height: 80px;">
	<div class="g-recaptcha" data-sitekey="' . TINYIB_RECAPTCHA_SITE . '"></div>
	<noscript>
		<div>
			<div style="width: 302px; height: 422px; position: relative;">
				<div style="width: 302px; height: 422px; position: absolute;">
					<iframe src="https://www.google.com/recaptcha/api/fallback?k=' . TINYIB_RECAPTCHA_SITE . '" frameborder="0" scrolling="no" style="width: 302px; height:422px; border-style: none;"></iframe>
				</div>
			</div>
			<div style="width: 300px; height: 60px; border-style: none;bottom: 12px; left: 25px; margin: 0px; padding: 0px; right: 25px;background: #f9f9f9; border: 1px solid #c1c1c1; border-radius: 3px;">
				<textarea id="g-recaptcha-response" name="g-recaptcha-response" class="g-recaptcha-response" style="width: 250px; height: 40px; border: 1px solid #c1c1c1; margin: 10px 25px; padding: 0px; resize: none;"></textarea>
			</div>
		</div>
	</noscript>
</div><br><br>';
			} else { // Simple CAPTCHA
				$captcha = '
<br>
<input type="text" name="captcha" id="captcha" size="6" accesskey="c" autocomplete="off">&nbsp;&nbsp;' . __('(enter the text below)') . '<br>
<img id="captchaimage" src="inc/captcha.php" width="175" height="55" alt="CAPTCHA" onclick="javascript:reloadCAPTCHA()" style="margin-top: 5px;cursor: pointer;"><br><br>';
			}

			$txt_report = __('Please complete a CAPTCHA to submit your report');
			$txt_submit = __('Submit');
			$body = <<<EOF
<form id="tinyib" name="tinyib" method="post" action="?report={$post['id']}&verify">
<fieldset>
<legend align="center">$txt_report</legend>
<div class="login">
$captcha
<input type="submit" value="$txt_submit" class="managebutton">
</div>
</fieldset>
</form>
EOF;

			echo pageHeader() . $body . pageFooter();
			die();
		}
	}

	$report = array('ip' => remoteAddress(), 'post' => $post['id']);
	insertReport($report);
	checkAutoHide($post);

	fancyDie(__('Post reported.'), $go_back);
// Check if the request is to delete a post and/or its associated image
} elseif (isset($_GET['delete']) && !isset($_GET['manage'])) {
	$lock = lockDatabase();

	if (!isset($_POST['delete'])) {
		fancyDie(__('Tick the box next to a post and click "Delete" to delete it.'));
	}

	if (TINYIB_DBMIGRATE) {
		fancyDie(__('Post deletion is currently disabled.<br>Please try again in a few moments.'));
	}

	$post_ids = array();
	if (is_array($_POST['delete'])) {
		$post_ids = $_POST['delete'];
	} else {
		$post_ids = array($_POST['delete']);
	}

	list($account, $loggedin, $isadmin) = manageCheckLogIn(false);
	if (!empty($account)) {
		// Redirect to post moderation page
		echo '--&gt; --&gt; --&gt;<meta http-equiv="refresh" content="0;url=' . basename($_SERVER['PHP_SELF']) . '?manage&moderate=' . implode(',', $post_ids) . '">';
		die();
	}

	$post = postByID($post_ids[0]);
	if (!$post) {
		fancyDie(__('Sorry, an invalid post identifier was sent. Please go back, refresh the page, and try again.'));
	} else if ($post['password'] != '' && (hashData($_POST['password']) == $post['password'] || md5(md5($_POST['password'])) == $post['password'])) {
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

	$redirect = false;
// Check if the request is to access the management area
} elseif (isset($_GET['manage'])) {
	$lock = lockDatabase();

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

	list($account, $loggedin, $isadmin) = manageCheckLogIn(true);

	if ($loggedin) {
		if ($isadmin) {
			if (isset($_GET['rebuildall'])) {
				$allthreads = allThreads();
				foreach ($allthreads as $thread) {
					rebuildThread($thread['id']);
				}
				rebuildIndexes();
				$text .= manageInfo(__('Rebuilt board.'));
			} else if (isset($_GET['modlog'])) {
				$text .= manageModerationLog($_GET['modlog']);
			} else if (isset($_GET['reports'])) {
				if (!TINYIB_REPORT) {
					fancyDie(__('Reporting is disabled.'));
				}
				$text .= manageReportsPage($_GET['reports']);
			} elseif (isset($_GET['accounts'])) {
				if ($account['role'] != TINYIB_SUPER_ADMINISTRATOR) {
					fancyDie(__('Access denied'));
				}

				$id = intval($_GET['accounts']);
				if (isset($_POST['id'])) {
					$id = intval($_POST['id']);
				}
				$a = array('id' => 0);
				if ($id > 0) {
					$a = accountByID($id);
					if (empty($a)) {
						fancyDie(__('Account not found.'));
					}

					if ($a['username'] == 'admin' && TINYIB_ADMINPASS != '') {
						fancyDie(__('This account may not be updated while TINYIB_ADMINPASS is set.'));
					} else if ($a['username'] == 'mod' && TINYIB_MODPASS != '') {
						fancyDie(__('This account may not be updated while TINYIB_MODPASS is set.'));
					}
				}

				if (isset($_POST['id'])) {
					if ($id == 0 && $_POST['password'] == '') {
						fancyDie(__('A password is required.'));
					}

					$prev = $a;

					$a['username'] = $_POST['username'];
					if ($_POST['password'] != '') {
						$a['password'] = $_POST['password'];
					}
					$a['role'] = intval($_POST['role']);
					if ($a['role'] !== TINYIB_SUPER_ADMINISTRATOR && $a['role'] != TINYIB_ADMINISTRATOR && $a['role'] != TINYIB_MODERATOR && $a['role'] != TINYIB_DISABLED) {
						fancyDie(__('Invalid role.'));
					}

					if ($id == 0) {
						insertAccount($a);
						manageLogAction(sprintf(__('Added account %s'), $a['username']));
						$text .= manageInfo(__('Added account'));
					} else {
						updateAccount($a);
						if ($a['username'] != $prev['username']) {
							manageLogAction(sprintf(__('Renamed account %1$s as %2$s'), $prev['username'], $a['username']));
						}
						if ($a['password'] != $prev['password']) {
							manageLogAction(sprintf(__('Changed password of account %s'), $a['username']));
						}
						if ($a['role'] != $prev['role']) {
							$r = '';
							switch ($a['role']) {
								case  TINYIB_SUPER_ADMINISTRATOR:
									$r = __('Super-administrator');
									break;
								case  TINYIB_ADMINISTRATOR:
									$r = __('Administrator');
									break;
								case TINYIB_MODERATOR:
									$r = __('Moderator');
									break;
								case  TINYIB_DISABLED:
									$r = __('Disabled');
									break;
							}
							manageLogAction(sprintf(__('Changed role of account %s to %s'), $a['username'], $r));
						}
						$text .= manageInfo(__('Updated account'));
					}
				}

				$onload = manageOnLoad('accounts');
				$text .= manageAccountForm($_GET['accounts']);
				if (intval($_GET['accounts']) == 0) {
					$text .= manageAccountsTable();
				}
			} elseif (isset($_GET['bans'])) {
				clearExpiredBans();

				if (isset($_POST['ip']) && $_POST['ip'] != '') {
					$ips = explode(',', $_POST['ip']);
					foreach ($ips as $ip) {
						$banexists = banByIP($ip);
						if ($banexists) {
							continue;
						}

						if (TINYIB_REPORT) {
							deleteReportsByIP($ip);
						}

						$ban = array();
						$ban['ip'] = $ip;
						$ban['expire'] = ($_POST['expire'] > 0) ? (time() + $_POST['expire']) : 0;
						$ban['reason'] = $_POST['reason'];

						$until = __('permanently');
						if ($ban['expire'] > 0) {
							$until = sprintf(__('until %s'), formatDate($ban['expire']));
						}
						$action = sprintf(__('Banned %s %s'), htmlentities($ban['ip']), $until);
						if ($ban['reason'] != '') {
							$action = sprintf(__('Banned %s %s: %s'), htmlentities($ban['ip']), $until, htmlentities($ban['reason']));
						}

						insertBan($ban);
						manageLogAction($action);
					}
					if (TINYIB_BANMESSAGE && isset($_POST['message']) && $_POST['message'] != '' && isset($_GET['posts']) && $_GET['posts'] != '') {
						$post_ids = explode(',', $_GET['posts']);
						foreach ($post_ids as $post_id) {
							$post = postByID($post_id);
							if (!$post) {
								continue; // The post has been deleted
							}
							updatePostMessage($post['id'], $post['message'] . '<br>' . "\n" . '<span class="banmessage">(' . htmlentities($_POST['message']) . ')</span><br>');
							manageLogAction(sprintf(__('Added ban message to %s'), postLink('&gt;&gt;' . $post['id'])));
						}
						clearPostCache();
						foreach ($post_ids as $post_id) {
							$post = postByID($post_id);
							if (!$post) {
								continue; // The post has been deleted
							}
							threadUpdated(getParent($post));
						}
					}
					if (count($ips) == 1) {
						$text .= manageInfo(__('Banned 1 IP address'));
					} else {
						$text .= manageInfo(sprintf(__('Banned %d IP addresses'), count($ips)));
					}
				} elseif (isset($_GET['lift'])) {
					$ban = banByID($_GET['lift']);
					if ($ban) {
						deleteBanByID($_GET['lift']);
						$info = sprintf(__('Lifted ban on %s'), htmlentities($ban['ip']));
						manageLogAction($info);
						$text .= manageInfo($info);
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

					$kw = $keyword['text'];

					if (isset($_POST['regexp']) && $_POST['regexp'] == '1') {
						$keyword['text'] = 'regexp:' . $keyword['text'];
					}

					insertKeyword($keyword);
					if ($_GET['keywords'] > 0) {
						manageLogAction(sprintf(__('Updated keyword %s'), htmlentities($kw)));
						$text .= manageInfo(__('Keyword updated.'));
						$_GET['keywords'] = 0;
					} else {
						manageLogAction(sprintf(__('Updated keyword %s'), htmlentities($kw)));
						$text .= manageInfo(__('Keyword added.'));
					}
				} elseif (isset($_GET['deletekeyword'])) {
					$keyword = keywordByID($_GET['deletekeyword']);
					if (empty($keyword)) {
						fancyDie(__('That keyword does not exist.'));
					}

					$kw = $keyword['text'];
					if (substr($keyword['text'], 0, 7) == 'regexp:') {
						$kw = substr($keyword['text'], 7);
					}

					deleteKeyword($_GET['deletekeyword']);
					manageLogAction(sprintf(__('Deleted keyword %s'), htmlentities($kw)));
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
					<p><b>Note:</b> If TinyIB updates and you have made custom modifications, <a href="https://code.rocket9labs.com/tslocum/tinyib/commits/master" target="_blank">review the changes</a> which have been merged into your installation.
					Ensure that your modifications do not interfere with any new/modified files.
					See the <a href="https://code.rocket9labs.com/tslocum/tinyib/src/branch/master/README.md">README</a> <small>(<a href="README.md" target="_blank">alternate link</a>)</small> for instructions.</p>';
				} else {
					$text .= '<p><b>TinyIB was not installed via Git.</b></p>
					<p>If you installed TinyIB without Git, you must <a href="https://code.rocket9labs.com/tslocum/tinyib">update manually</a>.  If you did install with Git, ensure the script has read and write access to the <b>.git</b> folder.</p>';
				}
			} elseif (isset($_GET['dbmigrate'])) {
				if (TINYIB_DBMIGRATE !== '' && TINYIB_DBMIGRATE !== false && TINYIB_DBMODE != TINYIB_DBMIGRATE) {
					$mysql_modes = array('mysql', 'mysqli');
					if (in_array(TINYIB_DBMODE, $mysql_modes) && in_array(TINYIB_DBMIGRATE, $mysql_modes)) {
						fancyDie('TINYIB_DBMODE and TINYIB_DBMIGRATE are both set to MySQL database modes. No migration is necessary.');
					}

					$sqlite_modes = array('sqlite', 'sqlite3');
					if (in_array(TINYIB_DBMODE, $sqlite_modes) && in_array(TINYIB_DBMIGRATE, $sqlite_modes)) {
						fancyDie('TINYIB_DBMODE and TINYIB_DBMIGRATE are both set to SQLite database modes. No migration is necessary.');
					}

					if (!in_array(TINYIB_DBMIGRATE, $database_modes)) {
						fancyDie(__('Unknown database mode specified.'));
					}

					if (isset($_GET['go'])) {
						require 'inc/database/' . TINYIB_DBMIGRATE . '_link.php';

						echo '<p>Migrating accounts...</p>';
						$accounts = allAccounts();
						foreach ($accounts as $account) {
							migrateAccount($account);
						}

						echo '<p>Migrating bans...</p>';
						$bans = allBans();
						foreach ($bans as $ban) {
							migrateBan($ban);
						}

						echo '<p>Migrating keywords...</p>';
						$keywords = allKeywords();
						foreach ($keywords as $keyword) {
							migrateKeyword($keyword);
						}

						echo '<p>Migrating logs...</p>';
						$logs = allLogs();
						foreach ($logs as $log) {
							migrateLog($log);
						}

						echo '<p>Migrating posts...</p>';
						$threads = allThreads();
						foreach ($threads as $thread) {
							$posts = postsInThreadByID($thread['id']);
							foreach ($posts as $post) {
								migratePost($post);
							}
						}

						echo '<p>Migrating reports...</p>';
						$reports = allReports();
						foreach ($reports as $report) {
							migrateReport($report);
						}

						echo '<p><b>Database migration complete</b>.  Set TINYIB_DBMODE to the new database mode and TINYIB_DBMIGRATE to false, then click <b>Rebuild All</b> above and ensure everything looks and works as it should.</p>';
					} else {
						$text .= '<p>Your original database will not be deleted.  If the migration fails, disable the tool and your board will be unaffected.  See the <a href="https://code.rocket9labs.com/tslocum/tinyib/src/branch/master/README.md" target="_blank">README</a> <small>(<a href="README.md" target="_blank">alternate link</a>)</small> for instructions.</a><br><br><a href="?manage&dbmigrate&go"><b>Start the migration</b></a></p>';
					}
				} else {
					fancyDie('Set TINYIB_DBMIGRATE to the desired TINYIB_DBMODE and enter in any database related settings in settings.php before migrating.');
				}
			}
		}

		if (isset($_GET['delete'])) {
			$post_ids = explode(',', $_GET['delete']);
			$posts = array();
			foreach ($post_ids as $post_id) {
				$post = postByID($post_id);
				if (!$post) {
					continue; // The post has already been deleted
				}
				$posts[$post_id] = $post;
			}
			foreach ($post_ids as $post_id) {
				$post = $posts[$post_id];

				deletePost($post['id']);
				if ($post['parent'] == TINYIB_NEWTHREAD) {
					rebuildThread($post['id']);
				} else {
					rebuildThread($post['parent']);
				}

				$action = sprintf(__('Deleted %s'),'&gt;&gt;' . $post['id']) . ' - ' . hashData($post['ip']);
				$stripped = strip_tags($post['message']);
				if ($stripped != '') {
					$action .= ' - ' . htmlentities(_substr($stripped, 0, 32));
					if (_strlen($stripped) > 32) {
						$action .= '...';
					}
				}
				manageLogAction($action);
			}
			rebuildIndexes();
			if (count($post_ids) == 1) {
				$text .= manageInfo(__('Deleted 1 post'));
			} else {
				$text .= manageInfo(sprintf(__('Deleted %d posts'), count($post_ids)));
			}
		} elseif (isset($_GET['approve'])) {
			if ($_GET['approve'] > 0) {
				$post = postByID($_GET['approve']);
				if ($post) {
					approvePostByID($post['id'], 2);
					$thread_id = $post['parent'] == TINYIB_NEWTHREAD ? $post['id'] : $post['parent'];

					if (strtolower($post['email']) != 'sage' && (TINYIB_MAXREPLIES == 0 || numRepliesToThreadByID($thread_id) <= TINYIB_MAXREPLIES)) {
						bumpThreadByID($thread_id);
					}
					threadUpdated($thread_id);

					manageLogAction(__('Approved') . ' ' . postLink('&gt;&gt;' . $post['id']));
					$text .= manageInfo(sprintf(__('Post No.%d approved.'), $post['id']));
				} else {
					fancyDie(__("Sorry, there doesn't appear to be a post with that ID."));
				}
			}
		} elseif (isset($_GET['moderate'])) {
			if ($_GET['moderate'] != '' && $_GET['moderate'] != '0') {
				$post_ids = explode(',', $_GET['moderate']);
				$compact = count($post_ids) > 1;
				$posts = array();
				$threads = 0;
				$replies = 0;
				$ips = array();

				foreach ($post_ids as $post_id) {
					$post = postByID($post_id);
					if (!$post) {
						fancyDie(__("Sorry, there doesn't appear to be a post with that ID."));
					}
					if ($post['parent'] == TINYIB_NEWTHREAD) {
						$threads++;
					} else {
						$replies++;
					}
					$ips[] = $post['ip'];

					$posts[$post_id] = $post;
				}

				$ips = array_unique($ips);

				if (count($post_ids) > 1) {
					$text .= manageModerateAll($post_ids, $threads, $replies, $ips);
				}
				foreach ($post_ids as $post_id) {
					$text .= manageModeratePost($posts[$post_id], $compact);
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

					$actionMessage = intval($_GET['setsticky']) == 1 ? __('Stickied') : __('Unstickied') . ' ' . postLink('&gt;&gt;' . $post['id']);
					manageLogAction($actionMessage);
					$text .= manageInfo($actionMessage);
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

					$actionMessage = intval($_GET['setlock']) == 1 ? __('Locked') : __('Unlocked') . ' ' . postLink('&gt;&gt;' . $post['id']);
					manageLogAction($actionMessage);
					$text .= manageInfo($actionMessage);
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
					approvePostByID($post['id'], 2);
					deleteReportsByPost($post['id']);

					manageLogAction(__('Approved') . ' ' . postLink('&gt;&gt;' . $post['id']));
					$text .= manageInfo(sprintf(__('Post No.%d approved.'), $post['id']));
				} else {
					fancyDie(__("Sorry, there doesn't appear to be a post with that ID."));
				}
			}
		} elseif (isset($_GET["staffpost"])) {
			$onload = manageOnLoad("staffpost");
			$text .= buildPostForm(0, true);
		} elseif (isset($_GET['changepassword'])) {
			if ($account['username'] == 'admin' && TINYIB_ADMINPASS != '') {
				fancyDie(__('This account may not be updated while TINYIB_ADMINPASS is set.'));
			} else if ($account['username'] == 'mod' && TINYIB_MODPASS != '') {
				fancyDie(__('This account may not be updated while TINYIB_MODPASS is set.'));
			}

			if (isset($_POST['password']) && isset($_POST['confirm'])) {
				if ($_POST['password'] == '') {
					fancyDie(__('A password is required.'));
				} else if ($_POST['password'] != $_POST['confirm']) {
					fancyDie(__('Passwords do not match.'));
				}

				$account['password'] = $_POST['password'];
				updateAccount($account);

				$text .= manageInfo(__('Password updated'));
			}

			$text .= manageChangePasswordForm();
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
