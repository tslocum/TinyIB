<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

$posts_sql = "CREATE TABLE `" . TINYIB_DBPOSTS . "` (
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
) ENGINE=MyISAM";

$bans_sql = "CREATE TABLE `" . TINYIB_DBBANS . "` (
	`id` mediumint(7) unsigned NOT NULL auto_increment,
	`ip` varchar(15) NOT NULL,
	`timestamp` int(20) NOT NULL,
	`expire` int(20) NOT NULL,
	`reason` text NOT NULL,
	PRIMARY KEY	(`id`),
	KEY `ip` (`ip`)
) ENGINE=MyISAM";

function cleanString($string) {
	$search = array("<", ">");
	$replace = array("&lt;", "&gt;");

	return str_replace($search, $replace, $string);
}

function plural($singular, $count, $plural = 's') {
	if ($plural == 's') {
		$plural = $singular . $plural;
	}
	return ($count == 1 ? $singular : $plural);
}

function threadUpdated($id) {
	rebuildThread($id);
	rebuildIndexes();
}

function newPost($parent = TINYIB_NEWTHREAD) {
	return array('parent' => $parent,
		'timestamp' => '0',
		'bumped' => '0',
		'ip' => '',
		'name' => '',
		'tripcode' => '',
		'email' => '',
		'nameblock' => '',
		'subject' => '',
		'message' => '',
		'password' => '',
		'file' => '',
		'file_hex' => '',
		'file_original' => '',
		'file_size' => '0',
		'file_size_formatted' => '',
		'image_width' => '0',
		'image_height' => '0',
		'thumb' => '',
		'thumb_width' => '0',
		'thumb_height' => '0');
}

function convertBytes($number) {
	$len = strlen($number);
	if ($len < 4) {
		return sprintf("%dB", $number);
	} elseif ($len <= 6) {
		return sprintf("%0.2fKB", $number / 1024);
	} elseif ($len <= 9) {
		return sprintf("%0.2fMB", $number / 1024 / 1024);
	}

	return sprintf("%0.2fGB", $number / 1024 / 1024 / 1024);
}

function nameAndTripcode($name) {
	if (preg_match("/(#|!)(.*)/", $name, $regs)) {
		$cap = $regs[2];
		$cap_full = '#' . $regs[2];

		if (function_exists('mb_convert_encoding')) {
			$recoded_cap = mb_convert_encoding($cap, 'SJIS', 'UTF-8');
			if ($recoded_cap != '') {
				$cap = $recoded_cap;
			}
		}

		if (strpos($name, '#') === false) {
			$cap_delimiter = '!';
		} elseif (strpos($name, '!') === false) {
			$cap_delimiter = '#';
		} else {
			$cap_delimiter = (strpos($name, '#') < strpos($name, '!')) ? '#' : '!';
		}

		if (preg_match("/(.*)(" . $cap_delimiter . ")(.*)/", $cap, $regs_secure)) {
			$cap = $regs_secure[1];
			$cap_secure = $regs_secure[3];
			$is_secure_trip = true;
		} else {
			$is_secure_trip = false;
		}

		$tripcode = "";
		if ($cap != "") { // Copied from Futabally
			$cap = strtr($cap, "&amp;", "&");
			$cap = strtr($cap, "&#44;", ", ");
			$salt = substr($cap . "H.", 1, 2);
			$salt = preg_replace("/[^\.-z]/", ".", $salt);
			$salt = strtr($salt, ":;<=>?@[\\]^_`", "ABCDEFGabcdef");
			$tripcode = substr(crypt($cap, $salt), -10);
		}

		if ($is_secure_trip) {
			if ($cap != "") {
				$tripcode .= "!";
			}

			$tripcode .= "!" . substr(md5($cap_secure . TINYIB_TRIPSEED), 2, 10);
		}

		return array(preg_replace("/(" . $cap_delimiter . ")(.*)/", "", $name), $tripcode);
	}

	return array($name, "");
}

function nameBlock($name, $tripcode, $email, $timestamp, $rawposttext) {
	$output = '<span class="postername">';
	$output .= ($name == '' && $tripcode == '') ? 'Anonymous' : $name;

	if ($tripcode != '') {
		$output .= '</span><span class="postertrip">!' . $tripcode;
	}

	$output .= '</span>';

	if ($email != '' && strtolower($email) != 'noko') {
		$output = '<a href="mailto:' . $email . '">' . $output . '</a>';
	}

	return $output . $rawposttext . ' ' . date('y/m/d(D)H:i:s', $timestamp);
}

function writePage($filename, $contents) {
	$tempfile = tempnam('res/', TINYIB_BOARD . 'tmp'); /* Create the temporary file */
	$fp = fopen($tempfile, 'w');
	fwrite($fp, $contents);
	fclose($fp);
	/* If we aren't able to use the rename function, try the alternate method */
	if (!@rename($tempfile, $filename)) {
		copy($tempfile, $filename);
		unlink($tempfile);
	}

	chmod($filename, 0664); /* it was created 0600 */
}

function fixLinksInRes($html) {
	$search = array(' href="css/', ' href="src/', ' href="thumb/', ' href="res/', ' href="imgboard.php', ' href="favicon.ico', 'src="thumb/', ' action="imgboard.php');
	$replace = array(' href="../css/', ' href="../src/', ' href="../thumb/', ' href="../res/', ' href="../imgboard.php', ' href="../favicon.ico', 'src="../thumb/', ' action="../imgboard.php');

	return str_replace($search, $replace, $html);
}

function _postLink($matches) {
	$post = postByID($matches[1]);
	if ($post) {
		return '<a href="res/' . ($post['parent'] == TINYIB_NEWTHREAD ? $post['id'] : $post['parent']) . '.html#' . $matches[1] . '">' . $matches[0] . '</a>';
	}
	return $matches[0];
}

function postLink($message) {
	return preg_replace_callback('/&gt;&gt;([0-9]+)/', '_postLink', $message);
}

function colorQuote($message) {
	if (substr($message, -1, 1) != "\n") {
		$message .= "\n";
	}
	return preg_replace('/^(&gt;[^\>](.*))\n/m', '<span class="unkfunc">\\1</span>' . "\n", $message);
}

function deletePostImages($post) {
	if ($post['file'] != '') {
		@unlink('src/' . $post['file']);
	}
	if ($post['thumb'] != '') {
		@unlink('thumb/' . $post['thumb']);
	}
}

function checkBanned() {
	$ban = banByIP($_SERVER['REMOTE_ADDR']);
	if ($ban) {
		if ($ban['expire'] == 0 || $ban['expire'] > time()) {
			$expire = ($ban['expire'] > 0) ? ('<br>This ban will expire ' . date('y/m/d(D)H:i:s', $ban['expire'])) : '<br>This ban is permanent and will not expire.';
			$reason = ($ban['reason'] == '') ? '' : ('<br>Reason: ' . $ban['reason']);
			fancyDie('Your IP address ' . $ban['ip'] . ' has been banned from posting on this image board.  ' . $expire . $reason);
		} else {
			clearExpiredBans();
		}
	}
}

function checkFlood() {
	if (TINYIB_DELAY > 0) {
		$lastpost = lastPostByIP();
		if ($lastpost) {
			if ((time() - $lastpost['timestamp']) < TINYIB_DELAY) {
				fancyDie("Please wait a moment before posting again.  You will be able to make another post in " . (TINYIB_DELAY - (time() - $lastpost['timestamp'])) . " " . plural("second", (TINYIB_DELAY - (time() - $lastpost['timestamp']))) . ".");
			}
		}
	}
}

function checkMessageSize() {
	if (strlen($_POST["message"]) > 8000) {
		fancyDie("Please shorten your message, or post it in multiple parts. Your message is " . strlen($_POST["message"]) . " characters long, and the maximum allowed is 8000.");
	}
}

function manageCheckLogIn() {
	$loggedin = false;
	$isadmin = false;
	if (isset($_POST['password'])) {
		if ($_POST['password'] == TINYIB_ADMINPASS) {
			$_SESSION['tinyib'] = TINYIB_ADMINPASS;
		} elseif (TINYIB_MODPASS != '' && $_POST['password'] == TINYIB_MODPASS) {
			$_SESSION['tinyib'] = TINYIB_MODPASS;
		}
	}

	if (isset($_SESSION['tinyib'])) {
		if ($_SESSION['tinyib'] == TINYIB_ADMINPASS) {
			$loggedin = true;
			$isadmin = true;
		} elseif (TINYIB_MODPASS != '' && $_SESSION['tinyib'] == TINYIB_MODPASS) {
			$loggedin = true;
		}
	}

	return array($loggedin, $isadmin);
}

function setParent() {
	if (isset($_POST["parent"])) {
		if ($_POST["parent"] != TINYIB_NEWTHREAD) {
			if (!threadExistsByID($_POST['parent'])) {
				fancyDie("Invalid parent thread ID supplied, unable to create post.");
			}

			return $_POST["parent"];
		}
	}

	return TINYIB_NEWTHREAD;
}

function isRawPost() {
	if (isset($_POST['rawpost'])) {
		list($loggedin, $isadmin) = manageCheckLogIn();
		if ($loggedin) {
			return true;
		}
	}

	return false;
}

function validateFileUpload() {
	switch ($_FILES['file']['error']) {
		case UPLOAD_ERR_OK:
			break;
		case UPLOAD_ERR_FORM_SIZE:
			fancyDie("That file is larger than " . TINYIB_MAXKBDESC . ".");
			break;
		case UPLOAD_ERR_INI_SIZE:
			fancyDie("The uploaded file exceeds the upload_max_filesize directive (" . ini_get('upload_max_filesize') . ") in php.ini.");
			break;
		case UPLOAD_ERR_PARTIAL:
			fancyDie("The uploaded file was only partially uploaded.");
			break;
		case UPLOAD_ERR_NO_FILE:
			fancyDie("No file was uploaded.");
			break;
		case UPLOAD_ERR_NO_TMP_DIR:
			fancyDie("Missing a temporary folder.");
			break;
		case UPLOAD_ERR_CANT_WRITE:
			fancyDie("Failed to write file to disk");
			break;
		default:
			fancyDie("Unable to save the uploaded file.");
	}
}

function checkDuplicateFile($hex) {
	$hexmatches = postsByHex($hex);
	if (count($hexmatches) > 0) {
		foreach ($hexmatches as $hexmatch) {
			fancyDie("Duplicate file uploaded. That file has already been posted <a href=\"res/" . (($hexmatch["parent"] == TINYIB_NEWTHREAD) ? $hexmatch["id"] : $hexmatch["parent"]) . ".html#" . $hexmatch["id"] . "\">here</a>.");
		}
	}
}

function thumbnailDimensions($post) {
	if ($post['parent'] == TINYIB_NEWTHREAD) {
		$max_width = TINYIB_MAXWOP;
		$max_height = TINYIB_MAXHOP;
	} else {
		$max_width = TINYIB_MAXW;
		$max_height = TINYIB_MAXH;
	}
	return ($post['image_width'] > $max_width || $post['image_height'] > $max_height) ? array($max_width, $max_height) : array($post['image_width'], $post['image_height']);
}

function createThumbnail($name, $filename, $new_w, $new_h) {
	$system = explode(".", $filename);
	$system = array_reverse($system);
	if (preg_match("/jpg|jpeg/", $system[0])) {
		$src_img = imagecreatefromjpeg($name);
	} else if (preg_match("/png/", $system[0])) {
		$src_img = imagecreatefrompng($name);
	} else if (preg_match("/gif/", $system[0])) {
		$src_img = imagecreatefromgif($name);
	} else {
		return false;
	}

	if (!$src_img) {
		fancyDie("Unable to read uploaded file during thumbnailing. A common cause for this is an incorrect extension when the file is actually of a different type.");
	}
	$old_x = imageSX($src_img);
	$old_y = imageSY($src_img);
	$percent = ($old_x > $old_y) ? ($new_w / $old_x) : ($new_h / $old_y);
	$thumb_w = round($old_x * $percent);
	$thumb_h = round($old_y * $percent);

	$dst_img = ImageCreateTrueColor($thumb_w, $thumb_h);
	fastImageCopyResampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);

	if (preg_match("/png/", $system[0])) {
		if (!imagepng($dst_img, $filename)) {
			return false;
		}
	} else if (preg_match("/jpg|jpeg/", $system[0])) {
		if (!imagejpeg($dst_img, $filename, 70)) {
			return false;
		}
	} else if (preg_match("/gif/", $system[0])) {
		if (!imagegif($dst_img, $filename)) {
			return false;
		}
	}

	imagedestroy($dst_img);
	imagedestroy($src_img);

	return true;
}

function fastImageCopyResampled(&$dst_image, &$src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $quality = 3) {
	// Author: Tim Eckel - Date: 12/17/04 - Project: FreeRingers.net - Freely distributable.
	if (empty($src_image) || empty($dst_image)) {
		return false;
	}

	if ($quality <= 1) {
		$temp = imagecreatetruecolor($dst_w + 1, $dst_h + 1);

		imagecopyresized($temp, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w + 1, $dst_h + 1, $src_w, $src_h);
		imagecopyresized($dst_image, $temp, 0, 0, 0, 0, $dst_w, $dst_h, $dst_w, $dst_h);
		imagedestroy($temp);
	} elseif ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
		$tmp_w = $dst_w * $quality;
		$tmp_h = $dst_h * $quality;
		$temp = imagecreatetruecolor($tmp_w + 1, $tmp_h + 1);

		imagecopyresized($temp, $src_image, $dst_x * $quality, $dst_y * $quality, $src_x, $src_y, $tmp_w + 1, $tmp_h + 1, $src_w, $src_h);
		imagecopyresampled($dst_image, $temp, 0, 0, 0, 0, $dst_w, $dst_h, $tmp_w, $tmp_h);
		imagedestroy($temp);
	} else {
		imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
	}

	return true;
}

function addVideoOverlay($thumb_location) {
	if (file_exists('video_overlay.png')) {
		if (substr($thumb_location, -4) == ".jpg") {
			$thumbnail = imagecreatefromjpeg($thumb_location);
		} else {
			$thumbnail = imagecreatefrompng($thumb_location);
		}
		list($width, $height, $type, $attr) = getimagesize($thumb_location);

		$overlay_play = imagecreatefrompng('video_overlay.png');
		imagealphablending($overlay_play, false);
		imagesavealpha($overlay_play, true);
		list($overlay_width, $overlay_height, $overlay_type, $overlay_attr) = getimagesize('video_overlay.png');

		if (substr($thumb_location, -4) == ".png") {
			imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
			imagealphablending($thumbnail, true);
			imagesavealpha($thumbnail, true);
		}

		imagecopy($thumbnail, $overlay_play, ($width / 2) - ($overlay_width / 2), ($height / 2) - ($overlay_height / 2), 0, 0, $overlay_width, $overlay_height);

		if (substr($thumb_location, -4) == ".jpg") {
			imagejpeg($thumbnail, $thumb_location);
		} else {
			imagepng($thumbnail, $thumb_location);
		}
	}
}

function strallpos($haystack, $needle, $offset = 0) {
	$result = array();
	for ($i = $offset; $i < strlen($haystack); $i++) {
		$pos = strpos($haystack, $needle, $i);
		if ($pos !== False) {
			$offset = $pos;
			if ($offset >= $i) {
				$i = $offset;
				$result[] = $offset;
			}
		}
	}
	return $result;
}
