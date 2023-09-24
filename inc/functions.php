<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

$multibyte_enabled = function_exists('mb_strlen');

if (!function_exists('array_column')) {
	function array_column($array, $column_name) {
		return array_map(function ($element) use ($column_name) {
			return $element[$column_name];
		}, $array);
	}
}

// lockDatabase obtains an exclusive lock to prevent race conditions when
// accessing the database.
function lockDatabase() {
	if (TINYIB_LOCKFILE == '') {
		return true;
	}
	$fp = fopen(TINYIB_LOCKFILE, 'c+');
	if (!flock($fp, LOCK_EX)) {
		fancyDie('Failed to lock control file.');
	}
	return $fp;
}

function _strlen($string) {
	global $multibyte_enabled;
	if ($multibyte_enabled) {
		return mb_strlen($string);
	}
	return strlen($string);
}

function _strpos($haystack, $needle, $offset=0) {
	global $multibyte_enabled;
	if ($multibyte_enabled) {
		return mb_strpos($haystack, $needle, $offset);
	}
	return strpos($haystack, $needle, $offset);
}

function _substr($string, $start, $length=null) {
	global $multibyte_enabled;
	if ($multibyte_enabled) {
		return mb_substr($string, $start, $length);
	}
	return substr($string, $start, $length);
}

function _substr_count($haystack, $needle) {
	global $multibyte_enabled;
	if ($multibyte_enabled) {
		return mb_substr_count($haystack, $needle);
	}
	return substr_count($haystack, $needle);
}

function hashData($data, $force = false) {
	global $bcrypt_salt;
	if (substr($data, 0, 4) == '$2y$' && !$force) {
		return $data;
	}
	return crypt($data, $bcrypt_salt);
}

function cleanString($string) {
	$search = array("&", "<", ">");
	$replace = array("&amp;", "&lt;", "&gt;");

	return str_replace($search, $replace, $string);
}

function cleanQuotes($string) {
	$search = array("'", "\"");
	$replace = array("&apos;", "&quot;");

	return str_replace($search, $replace, $string);
}

function plural($count, $singular, $plural) {
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
	return array(
		'parent' => $parent,
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
		'thumb_height' => '0',
		'stickied' => '0',
		'locked' => '0',
		'moderated' => '1'
	);
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

function nameBlock($name, $tripcode, $email, $timestamp, $capcode) {
	global $tinyib_anonymous;
	$anonymous = $tinyib_anonymous[array_rand($tinyib_anonymous)];

	$output = '<span class="postername">';
	$output .= ($name == '' && $tripcode == '') ? $anonymous : $name;

	if ($tripcode != '') {
		$output .= '</span><span class="postertrip">!' . $tripcode;
	}

	$output .= '</span>';

	if ($email != '' && strtolower($email) != 'noko') {
		$output = '<a href="mailto:' . $email . '">' . $output . '</a>';
	}

	return $output . $capcode . ' ' . formatDate($timestamp);
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
	$search = array(' href="css/', ' src="js/', ' href="src/', ' href="thumb/', ' href="res/', ' href="imgboard.php', ' href="catalog.html', ' href="favicon.ico', 'src="thumb/', 'src="inc/', 'src="sticky.png', 'src="lock.png', ' action="imgboard.php', ' action="catalog.html');
	$replace = array(' href="../css/', ' src="../js/', ' href="../src/', ' href="../thumb/', ' href="../res/', ' href="../imgboard.php', ' href="../catalog.html', ' href="../favicon.ico', 'src="../thumb/', 'src="../inc/', 'src="../sticky.png', 'src="../lock.png', ' action="../imgboard.php', ' action="../catalog.html');

	return str_replace($search, $replace, $html);
}

function _postLink($matches) {
	$post = postByID($matches[1]);
	if ($post) {
		$is_op = $post['parent'] == TINYIB_NEWTHREAD;
		return '<a href="res/' . ($is_op ? $post['id'] : $post['parent']) . '.html#' . $matches[1] . '" class="' . ($is_op ? 'refop' : 'refreply') . '">' . $matches[0] . '</a>';
	}
	return $matches[0];
}

function postLink($message) {
	return preg_replace_callback('/&gt;&gt;([0-9]+)/', '_postLink', $message);
}

function _finishWordBreak($matches) {
	return '<a' . $matches[1] . 'href="' . str_replace(TINYIB_WORDBREAK_IDENTIFIER, '', $matches[2]) . '"' . $matches[3] . '>' . str_replace(TINYIB_WORDBREAK_IDENTIFIER, '<br>', $matches[4]) . '</a>';
}

function finishWordBreak($message) {
	return str_replace(TINYIB_WORDBREAK_IDENTIFIER, '<br>', preg_replace_callback('/<a(.*?)href="([^"]*?)"(.*?)>(.*?)<\/a>/', '_finishWordBreak', $message));
}

function colorQuote($message) {
	if (substr($message, -1, 1) != "\n") {
		$message .= "\n";
	}
	return preg_replace('/^(&gt;[^\>](.*))\n/m', '<span class="unkfunc">\\1</span>' . "\n", $message);
}

function deletePostImages($post) {
	if (!isEmbed($post['file_hex']) && $post['file'] != '') {
		@unlink('src/' . $post['file']);
	}
	if ($post['thumb'] != '') {
		@unlink('thumb/' . $post['thumb']);
	}
}

function deletePost($id) {
	$id = intval($id);

	$is_op = false;
	$parent = 0;
	$op = array();
	$posts = postsInThreadByID($id, false);
	foreach ($posts as $post) {
		if ($post['parent'] == TINYIB_NEWTHREAD) {
			if ($post['id'] == $id) {
				$is_op = true;
			}
			$op = $post;
			continue;
		} else if ($post['id'] == $id) {
			$parent = $post['parent'];
		}

		deletePostImages($post);
		deleteReportsByPost($post['id']);
		deletePostByID($post['id']);
	}
	if (!empty($op)) {
		deletePostImages($op);
		deleteReportsByPost($op['id']);
		deletePostByID($op['id']);
	}

	if ($is_op) {
		@unlink('res/' . $id . '.html');
		return;
	}

	$current_bumped = 0;
	$new_bumped = 0;
	$posts = postsInThreadByID($parent, false);
	foreach ($posts as $post) {
		if ($post['parent'] == TINYIB_NEWTHREAD) {
			$current_bumped = $post['bumped'];
		} else if ($post['id'] == $id || strtolower($post['email']) == 'sage') {
			continue;
		}
		$new_bumped = $post['timestamp'];
	}
	if ($new_bumped >= $current_bumped) {
		return;
	}
	updatePostBumped($parent, $new_bumped);
	rebuildIndexes();
}

function checkCAPTCHA($mode) {
	if ($mode === 'hcaptcha') {
		$captcha = isset($_POST['h-captcha-response']) ? $_POST['h-captcha-response'] : '';
		if ($captcha == '') {
			fancyDie('Failed CAPTCHA. Reason:<br>Please click the checkbox labeled "I am human".');
		}

		$data = array(
			'secret' => TINYIB_HCAPTCHA_SECRET,
			'response' => $captcha
		);
		$verify = curl_init();
		curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
		curl_setopt($verify, CURLOPT_POST, true);
		curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
		$verifyResponse = curl_exec($verify);
		$responseData = json_decode($verifyResponse);
		if (!isset($responseData->success) || !$responseData->success) {
			fancyDie('Failed CAPTCHA.');
		}
	} else if ($mode === 'recaptcha') {
		require_once 'inc/recaptcha/autoload.php';

		$captcha = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
		$failed_captcha = true;

		$recaptcha = new \ReCaptcha\ReCaptcha(TINYIB_RECAPTCHA_SECRET);
		$resp = $recaptcha->verify($captcha, remoteAddress());
		if ($resp->isSuccess()) {
			$failed_captcha = false;
		}

		if ($failed_captcha) {
			$captcha_error = 'Failed CAPTCHA.';
			$error_reason = '';

			if (count($resp->getErrorCodes()) == 1) {
				$error_codes = $resp->getErrorCodes();
				$error_reason = $error_codes[0];
			}

			if ($error_reason == 'missing-input-response') {
				$captcha_error .= ' Please click the checkbox labeled "I\'m not a robot".';
			} else {
				$captcha_error .= ' Reason:';
				foreach ($resp->getErrorCodes() as $error) {
					$captcha_error .= '<br>' . $error;
				}
			}
			fancyDie($captcha_error);
		}
	} else if ($mode) { // Simple CAPTCHA
		$captcha = isset($_POST['captcha']) ? strtolower(trim($_POST['captcha'])) : '';
		$captcha_solution = isset($_SESSION['tinyibcaptcha']) ? strtolower(trim($_SESSION['tinyibcaptcha'])) : '';

		if ($captcha == '') {
			fancyDie(__('Please enter the CAPTCHA text.'));
		} else if ($captcha != $captcha_solution) {
			fancyDie(__('Incorrect CAPTCHA text entered.  Please try again.<br>Click the image to retrieve a new CAPTCHA.'));
		}
	}
}

function checkBanned() {
	$ban = banByIP(remoteAddress());
	if ($ban) {
		if ($ban['expire'] == 0 || $ban['expire'] > time()) {
			$expire = ($ban['expire'] > 0) ? ('<br>This ban will expire ' . formatDate($ban['expire'])) : '<br>This ban is permanent and will not expire.';
			$reason = ($ban['reason'] == '') ? '' : ('<br>Reason: ' . $ban['reason']);
			fancyDie('Your IP address ' . remoteAddress() . ' has been banned from posting on this image board.  ' . $expire . $reason);
		} else {
			clearExpiredBans();
		}
	}
}

function checkKeywords($text) {
	$keywords = allKeywords();
	foreach ($keywords as $keyword) {
		if (substr($keyword['text'], 0, 7) == 'regexp:') {
			if (preg_match(substr($keyword['text'], 7), $text)) {
				$keyword['text'] = substr($keyword['text'], 7);
				return $keyword;
			}
			continue;
		}

		if (stripos($text, $keyword['text']) !== false) {
			return $keyword;
		}
	}
	return array();
}

function checkFlood() {
	if (TINYIB_DELAY > 0) {
		$lastpost = lastPostByIP();
		if ($lastpost) {
			if ((time() - $lastpost['timestamp']) < TINYIB_DELAY) {
				fancyDie("Please wait a moment before posting again.  You will be able to make another post in " . (TINYIB_DELAY - (time() - $lastpost['timestamp'])) . " " . plural(TINYIB_DELAY - (time() - $lastpost['timestamp']), "second", "seconds") . ".");
			}
		}
	}
}

function checkMessageSize() {
	if (TINYIB_MAXMESSAGE > 0 && _strlen($_POST['message']) > TINYIB_MAXMESSAGE) {
		fancyDie(sprintf(__('Please shorten your message, or post it in multiple parts. Your message is %1$d characters long, and the maximum allowed is %2$d.'), _strlen($_POST['message']), TINYIB_MAXMESSAGE));
	}
}

function checkAutoHide($post) {
	if (TINYIB_AUTOHIDE <= 0) {
		return;
	}

	$reports = reportsByPost($post['id']);
	if (count($reports) >= TINYIB_AUTOHIDE) {
		approvePostByID($post['id'], 0);

		$parent_id = $post['parent'] == TINYIB_NEWTHREAD ? $post['id'] : $post['parent'];
		threadUpdated($parent_id);
	}
}

function manageCheckLogIn($requireKey) {
	$account = array();
	$loggedin = false;
	$isadmin = false;

	$key = (isset($_GET['manage']) && $_GET['manage'] != '') ? hashData($_GET['manage']) : '';
	if ($key == '' && isset($_SESSION['tinyib_key'])) {
		$key = $_SESSION['tinyib_key'];
	}
	if (TINYIB_MANAGEKEY != '' && $key !== hashData(TINYIB_MANAGEKEY)) {
		$_SESSION['tinyib_key'] = '';
		$_SESSION['tinyib_account'] = '';
		session_destroy();

		if ($requireKey) {
			fancyDie(__('Invalid key.'));
		}

		return array($account, $loggedin, $isadmin);
	}

	if (isset($_POST['username']) && isset($_POST['managepassword']) && $_POST['username'] != '' && $_POST['managepassword'] != '') {
		checkCAPTCHA(TINYIB_MANAGECAPTCHA);

		$a = accountByUsername($_POST['username']);
		if (empty($a) || hashData($_POST['managepassword'], true) !== $a['password']) {
			fancyDie(__('Invalid username or password.'));
		}
		$_SESSION['tinyib_key'] = hashData(TINYIB_MANAGEKEY);
		$_SESSION['tinyib_username'] = $a['username'];
		$_SESSION['tinyib_password'] = $a['password'];

		// Prevent reauthentication
		$_POST['username'] = '';
		$_POST['managepassword'] = '';
	}

	if (isset($_SESSION['tinyib_username']) && isset($_SESSION['tinyib_password'])) {
		$a = accountByUsername($_SESSION['tinyib_username']);
		if (!empty($a) && $a['password'] == $_SESSION['tinyib_password'] && $a['role'] != TINYIB_DISABLED) {
			$account = $a;
			$loggedin = true;
			if ($account['role'] == TINYIB_SUPER_ADMINISTRATOR || $account['role'] == TINYIB_ADMINISTRATOR) {
				$isadmin = true;
			}

			$account['lastactive'] = time();
			updateAccount($account);
		}
	}

	return array($account, $loggedin, $isadmin);
}

function manageLogAction($action) {
	global $account;
	$account_id = 0;
	if (isset($account['id'])) {
		$account_id = $account['id'];
	}
	$log = array(
		'timestamp' => time(),
		'account' => $account_id,
		'message' => $action,
	);
	insertLog($log);
}

function setParent() {
	if (isset($_POST["parent"])) {
		if ($_POST["parent"] != TINYIB_NEWTHREAD) {
			if (!threadExistsByID($_POST['parent'])) {
				fancyDie(__('Invalid parent thread ID supplied, unable to create post.'));
			}

			return $_POST["parent"];
		}
	}

	return TINYIB_NEWTHREAD;
}

function getParent($post) {
	if ($post['parent'] == TINYIB_NEWTHREAD) {
		return $post['id'];
	}
	return $post['parent'];
}

function isStaffPost() {
	if (isset($_POST['staffpost'])) {
		list($loggedin, $isadmin) = manageCheckLogIn(false);
		return $loggedin;
	}

	return false;
}

function validateFileUpload() {
	switch ($_FILES['file']['error']) {
		case UPLOAD_ERR_OK:
			break;
		case UPLOAD_ERR_FORM_SIZE:
			fancyDie(sprintf(__('That file is larger than %s.'), TINYIB_MAXKBDESC));
			break;
		case UPLOAD_ERR_INI_SIZE:
			fancyDie(sprintf(__('The uploaded file exceeds the upload_max_filesize directive (%s) in php.ini.'), ini_get('upload_max_filesize')));
			break;
		case UPLOAD_ERR_PARTIAL:
			fancyDie(__('The uploaded file was only partially uploaded.'));
			break;
		case UPLOAD_ERR_NO_FILE:
			fancyDie(__('No file was uploaded.'));
			break;
		case UPLOAD_ERR_NO_TMP_DIR:
			fancyDie(__('Missing a temporary folder.'));
			break;
		case UPLOAD_ERR_CANT_WRITE:
			fancyDie(__('Failed to write file to disk'));
			break;
		default:
			fancyDie(__('Unable to save the uploaded file.'));
	}
}

function checkDuplicateFile($hex) {
	$hexmatches = postsByHex($hex);
	if (count($hexmatches) > 0) {
		foreach ($hexmatches as $hexmatch) {
			fancyDie(sprintf(__('Duplicate file uploaded. That file has already been posted <a href="%s">here</a>.'), 'res/' . (($hexmatch['parent'] == TINYIB_NEWTHREAD) ? $hexmatch['id'] : $hexmatch['parent']) . '.html#' . $hexmatch['id']));
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

function videoDimensions($file_location) {
	$discard = '';
	$exit_status = 1;
	exec("ffprobe -version", $discard, $exit_status);
	if ($exit_status != 0) {
		fancyDie('FFMPEG is not installed, or the commands ffmpeg and ffprobe are not in the server\'s $PATH.<br>Install FFMPEG, or set TINYIB_THUMBNAIL to \'gd\' or \'imagemagick\'.');
	}

	$dimensions = '';
	$exit_status = 1;
	exec("ffprobe -hide_banner -loglevel error -of csv=p=0 -select_streams v -show_entries stream=width,height $file_location", $dimensions, $exit_status);
	if ($exit_status != 0) {
		return array(0, 0);
	}
	if (is_array($dimensions)) {
		$dimensions = $dimensions[0];
	}
	$split = explode(',', $dimensions);
	if (count($split) != 2) {
		return array(0, 0);
	}
	return array(intval($split[0]), intval($split[1]));
}

function videoDuration($file_location) {
	$discard = '';
	$exit_status = 1;
	exec("ffprobe -version", $discard, $exit_status);
	if ($exit_status != 0) {
		fancyDie('FFMPEG is not installed, or the commands ffmpeg and ffprobe are not in the server\'s $PATH.<br>Install FFMPEG, or set TINYIB_THUMBNAIL to \'gd\' or \'imagemagick\'.');
	}

	$duration = '';
	$exit_status = 1;
	exec("ffprobe -hide_banner -loglevel error -of csv=p=0 -show_entries format=duration $file_location", $duration, $exit_status);
	if ($exit_status != 0) {
		return 0;
	}
	if (is_array($duration)) {
		$duration = $duration[0];
	}
	return floatval($duration);
}

function ffmpegThumbnail($file_location, $thumb_location, $new_w, $new_h) {
	$discard = '';
	$exit_status = 1;
	exec("ffmpeg -version", $discard, $exit_status);
	if ($exit_status != 0) {
		fancyDie('FFMPEG is not installed, or the commands ffmpeg and ffprobe are not in the server\'s $PATH.<br>Install FFMPEG, or set TINYIB_THUMBNAIL to \'gd\' or \'imagemagick\'.');
	}

	$quarter = videoDuration($file_location) / 4;

	$exit_status = 1;
	exec("ffmpeg -hide_banner -loglevel error -ss $quarter -i $file_location -frames:v 1 -vf scale=w=$new_w:h=$new_h:force_original_aspect_ratio=decrease $thumb_location", $discard, $exit_status);
	if ($exit_status != 0) {
		return false;
	}
}

function createThumbnail($file_location, $thumb_location, $new_w, $new_h, $spoiler) {
	$system = explode(".", $thumb_location);
	$system = array_reverse($system);
	if (TINYIB_THUMBNAIL == 'gd' || (TINYIB_THUMBNAIL == 'ffmpeg' && preg_match("/jpg|jpeg/", $system[0]))) {
		if (preg_match("/jpg|jpeg/", $system[0])) {
			$src_img = imagecreatefromjpeg($file_location);
		} else if (preg_match("/png/", $system[0])) {
			$src_img = imagecreatefrompng($file_location);
		} else if (preg_match("/gif/", $system[0])) {
			$src_img = imagecreatefromgif($file_location);
		} else {
			return false;
		}

		if (!$src_img) {
			fancyDie(__('Unable to read the uploaded file while creating its thumbnail. A common cause for this is an incorrect extension when the file is actually of a different type.'));
		}

		$old_x = imageSX($src_img);
		$old_y = imageSY($src_img);
		$percent = ($old_x > $old_y) ? ($new_w / $old_x) : ($new_h / $old_y);
		$thumb_w = round($old_x * $percent);
		$thumb_h = round($old_y * $percent);

		$dst_img = imagecreatetruecolor($thumb_w, $thumb_h);
		if (preg_match("/png/", $system[0]) && imagepng($src_img, $thumb_location)) {
			imagealphablending($dst_img, false);
			imagesavealpha($dst_img, true);

			$color = imagecolorallocatealpha($dst_img, 0, 0, 0, 0);
			imagefilledrectangle($dst_img, 0, 0, $thumb_w, $thumb_h, $color);
			imagecolortransparent($dst_img, $color);

			imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);
		} else {
			fastimagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);
		}

		if (preg_match("/png/", $system[0])) {
			if (!imagepng($dst_img, $thumb_location)) {
				return false;
			}
		} else if (preg_match("/jpg|jpeg/", $system[0])) {
			if (!imagejpeg($dst_img, $thumb_location, 70)) {
				return false;
			}
		} else if (preg_match("/gif/", $system[0])) {
			if (!imagegif($dst_img, $thumb_location)) {
				return false;
			}
		}

		imagedestroy($dst_img);
		imagedestroy($src_img);
	} else if (TINYIB_THUMBNAIL == 'ffmpeg') {
		ffmpegThumbnail($file_location, $thumb_location, $new_w, $new_h);
	} else { // ImageMagick
		$discard = '';

		$exit_status = 1;
		exec("convert -version", $discard, $exit_status);
		if ($exit_status != 0) {
			fancyDie('ImageMagick is not installed, or the convert command is not in the server\'s $PATH.<br>Install ImageMagick, or set TINYIB_THUMBNAIL to \'gd\' or \'ffmpeg\'.');
		}

		$exit_status = 1;
		exec("convert $file_location -auto-orient -thumbnail '" . $new_w . "x" . $new_h . "' -coalesce -layers OptimizeFrame -depth 4 -type palettealpha $thumb_location", $discard, $exit_status);

		if ($exit_status != 0) {
			return false;
		}
	}

	if (!$spoiler) {
		return true;
	}

	if (preg_match("/jpg|jpeg/", $system[0])) {
		$src_img = imagecreatefromjpeg($thumb_location);
	} else if (preg_match("/png/", $system[0])) {
		$src_img = imagecreatefrompng($thumb_location);
	} else if (preg_match("/gif/", $system[0])) {
		$src_img = imagecreatefromgif($thumb_location);
	} else {
		return true;
	}

	if (!$src_img) {
		fancyDie(__('Unable to read the uploaded file while creating its thumbnail. A common cause for this is an incorrect extension when the file is actually of a different type.'));
	}

	$gaussian = array(array(1.0, 2.0, 1.0), array(2.0, 4.0, 2.0), array(1.0, 2.0, 1.0));
	for ($x = 1; $x <= 149; $x++) {
		imageconvolution($src_img, $gaussian, 16, 0);
	}

	if (preg_match("/png/", $system[0])) {
		if (!imagepng($src_img, $thumb_location)) {
			return false;
		}
	} else if (preg_match("/jpg|jpeg/", $system[0])) {
		if (!imagejpeg($src_img, $thumb_location, 70)) {
			return false;
		}
	} else if (preg_match("/gif/", $system[0])) {
		if (!imagegif($src_img, $thumb_location)) {
			return false;
		}
	}
	imagedestroy($src_img);
	return true;
}

function fastimagecopyresampled(&$dst_image, &$src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $quality = 3) {
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
	if (!file_exists('video_overlay.png')) {
		return;
	}

	if (TINYIB_THUMBNAIL == 'gd' || TINYIB_THUMBNAIL == 'ffmpeg') {
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
	} else { // imagemagick
		$discard = '';
		$exit_status = 1;
		exec("convert $thumb_location video_overlay.png -gravity center -composite -quality 75 $thumb_location", $discard, $exit_status);
	}
}

function strallpos($haystack, $needle, $offset = 0) {
	$result = array();
	for ($i = $offset; $i < _strlen($haystack); $i++) {
		$pos = _strpos($haystack, $needle, $i);
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

function url_get_contents($url) {
	if (!function_exists('curl_init')) {
		return file_get_contents($url);
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$output = curl_exec($ch);
	$responsecode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	curl_close($ch);

	if (intval($responsecode) != 200) {
		return '';
	}
	return $output;
}

function isEmbed($file_hex) {
	global $tinyib_embeds;
	return in_array($file_hex, array_keys($tinyib_embeds));
}

function getEmbed($url) {
	global $tinyib_embeds;
	foreach ($tinyib_embeds as $service => $service_url) {
		$service_url = str_ireplace("TINYIBEMBED", urlencode($url), $service_url);
		$data = url_get_contents($service_url);
		if ($data != '') {
			$result = json_decode($data, true);
			if (!empty($result)) {
				return array($service, $result);
			}
		}
	}

	return array('', array());
}

function attachFile($post, $filepath, $filename, $uploaded, $spoiler) {
	global $tinyib_uploads;

	if (!is_file($filepath) || !is_readable($filepath)) {
		@unlink($filepath);
		fancyDie(__('File transfer failure. Please retry the submission.'));
	}

	$file_mime_split = explode(' ', trim(mime_content_type($filepath)));
	if (count($file_mime_split) > 0) {
		$file_mime = strtolower(array_pop($file_mime_split));
	} else {
		if (!@getimagesize($filepath)) {
			@unlink($filepath);
			fancyDie(__('Failed to read the MIME type and size of the uploaded file. Please retry the submission.'));
		}
		$file_mime = mime_content_type($filepath);
	}
	if (empty($file_mime) || !isset($tinyib_uploads[$file_mime])) {
		fancyDie(supportedFileTypes());
	}

	$file_name_pre = time() . substr(microtime(), 2, 3);
	$file_name = $file_name_pre . '.' . $tinyib_uploads[$file_mime][0];
	$file_src = 'src/' . $file_name;

	if ($uploaded) {
		if (!move_uploaded_file($filepath, $file_src)) {
			fancyDie(__('Could not copy uploaded file.'));
		}
	} else {
		if (!rename($filepath, $file_src)) {
			@unlink($filepath);
			fancyDie(__('Could not copy uploaded file.'));
		}
	}
	$filepath = $file_src;

	$filesize = filesize($filepath);
	if (filesize($filepath) != $filesize) {
		@unlink($filepath);
		fancyDie(__('File transfer failure. Please go back and try again.'));
	} else if (TINYIB_MAXKB > 0 && $filesize > (TINYIB_MAXKB * 1024)) {
		@unlink($filepath);
		fancyDie(sprintf(__('That file is larger than %s.'), TINYIB_MAXKBDESC));
	}

	if (TINYIB_STRIPMETADATA) {
		stripMetadata($filepath);
	}

	$post['file'] = $file_name;
	$post['file_original'] = trim(htmlentities(_substr($filename, 0, 50), ENT_QUOTES));
	$post['file_hex'] = md5_file($filepath);
	$post['file_size'] = $filesize;
	$post['file_size_formatted'] = convertBytes($post['file_size']);
	checkDuplicateFile($post['file_hex']);

	if (in_array($file_mime, array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif', 'application/x-shockwave-flash'))) {
		$file_info = getimagesize($file_src);
		$post['image_width'] = $file_info[0] != '' ? $file_info[0] : 0;
		$post['image_height'] = $file_info[1] != '' ? $file_info[1] : 0;
	}

	if (isset($tinyib_uploads[$file_mime][1])) {
		$thumbfile_split = explode('.', $tinyib_uploads[$file_mime][1]);
		$post['thumb'] = $file_name_pre . 's.' . array_pop($thumbfile_split);
		if (!copy($tinyib_uploads[$file_mime][1], 'thumb/' . $post['thumb'])) {
			@unlink($file_src);
			fancyDie(__('Could not create thumbnail.'));
		}
		if ($file_mime == 'application/x-shockwave-flash') {
			addVideoOverlay('thumb/' . $post['thumb']);
		}
	} else if (in_array($file_mime, array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'))) {
		$post['thumb'] = $file_name_pre . 's.' . $tinyib_uploads[$file_mime][0];
		list($thumb_maxwidth, $thumb_maxheight) = thumbnailDimensions($post);

		if (!createThumbnail($file_src, 'thumb/' . $post['thumb'], $thumb_maxwidth, $thumb_maxheight, $spoiler)) {
			@unlink($file_src);
			fancyDie(__('Could not create thumbnail.'));
		}
	} else if ($file_mime == 'audio/webm' || $file_mime == 'video/webm' || $file_mime == 'audio/mp4' || $file_mime == 'video/mp4') {
		list($post['image_width'], $post['image_height']) = videoDimensions($file_src);

		if ($post['image_width'] > 0 && $post['image_height'] > 0) {
			list($thumb_maxwidth, $thumb_maxheight) = thumbnailDimensions($post);
			$post['thumb'] = $file_name_pre . 's.jpg';
			ffmpegThumbnail($file_src, 'thumb/' . $post['thumb'], $thumb_maxwidth, $thumb_maxheight);

			$thumb_info = getimagesize('thumb/' . $post['thumb']);
			$post['thumb_width'] = $thumb_info[0];
			$post['thumb_height'] = $thumb_info[1];

			if ($post['thumb_width'] <= 0 || $post['thumb_height'] <= 0) {
				@unlink($file_src);
				@unlink('thumb/' . $post['thumb']);
				fancyDie(__('Sorry, your video appears to be corrupt.'));
			}

			addVideoOverlay('thumb/' . $post['thumb']);
		}

		$duration = videoDuration($file_src);
		if ($duration > 0) {
			$mins = floor(round($duration / 1000) / 60);
			$secs = str_pad(floor(round($duration / 1000) % 60), 2, '0', STR_PAD_LEFT);

			$post['file_original'] = "$mins:$secs" . ($post['file_original'] != '' ? (', ' . $post['file_original']) : '');
		}
	}

	if ($post['thumb'] != '') {
		$thumb_info = getimagesize('thumb/' . $post['thumb']);
		$post['thumb_width'] = $thumb_info[0];
		$post['thumb_height'] = $thumb_info[1];

		if ($post['thumb_width'] <= 0 || $post['thumb_height'] <= 0) {
			@unlink($file_src);
			@unlink('thumb/' . $post['thumb']);
			fancyDie(__('Sorry, your video appears to be corrupt.'));
		}
	}

	return $post;
}

function stripMetadata($filename) {
	$discard = '';
	$exit_status = 1;
	exec("exiftool -ver", $discard, $exit_status);
	if ($exit_status != 0) {
		fancyDie('ExifTool is not installed, or the <i>exiftool</i> executable is not in the server\'s $PATH.<br>Install ExifTool, or set TINYIB_STRIPMETADATA to false.');
	}

	$discard = '';
	$exit_status = 1;
	exec("exiftool -All= -overwrite_original_in_place " . escapeshellarg($filename), $discard, $exit_status);
}

function formatDate($timestamp) {
	return @strftime(TINYIB_DATEFMT, $timestamp);
}

function remoteAddress() {
	if (TINYIB_CLOUDFLARE && isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
		return $_SERVER['HTTP_CF_CONNECTING_IP'];
	}
	return $_SERVER['REMOTE_ADDR'];
}

function installedViaGit() {
	return is_dir('.git');
}
