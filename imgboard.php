<?php
# TinyIB
#
# https://github.com/tslocum/TinyIB

error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();

if (get_magic_quotes_gpc()) {
	foreach ($_GET as $key => $val) { $_GET[$key] = stripslashes($val); }
	foreach ($_POST as $key => $val) { $_POST[$key] = stripslashes($val); }
}
if (get_magic_quotes_runtime()) { set_magic_quotes_runtime(0); }

function fancyDie($message) {
	die('<span style="color: red;font-size: 1.5em;font-family: Helvetica;">' . $message . '</span>');
}

if (!file_exists('settings.php')) {
	fancyDie('Please rename the file settings.default.php to settings.php');
}
require 'settings.php';

// Check directories are writable by the script
$writedirs = array("res", "src", "thumb");
if (TINYIB_DBMODE == 'flatfile') { $writedirs[] = "inc/flatfile"; }
foreach ($writedirs as $dir) {
	if (!is_writable($dir)) {
		fancyDie("Directory '" . $dir . "' can not be written to! Please modify its permissions.");
	}
}

$includes = array("inc/functions.php", "inc/html.php");
if (TINYIB_DBMODE == 'flatfile') {
	$includes[] = 'inc/database_flatfile.php';
} elseif (TINYIB_DBMODE == 'mysql') {
	$includes[] = 'inc/database_mysql.php';
} elseif (TINYIB_DBMODE == 'sqlite') {
	$includes[] = 'inc/database_sqlite.php';
} else {
	fancyDie("Unknown database mode specificed");
}

foreach ($includes as $include) {
	include $include;
}

if (TINYIB_TRIPSEED == '' || TINYIB_ADMINPASS == '') {
	fancyDie('TINYIB_TRIPSEED and TINYIB_ADMINPASS must be configured!');
}

$redirect = true;
// Check if the request is to make a post
if (isset($_POST["message"]) || isset($_POST["file"])) {
	list($loggedin, $isadmin) = manageCheckLogIn();
	$modpost = isModPost();
	checkBanned();
	checkFlood();
	
	if (strlen($_POST["message"]) > 8000) {
		fancyDie("Please shorten your message, or post it in multiple parts. Your message is " . strlen($_POST["message"]) . " characters long, and the maximum allowed is 8000.");
	}
	
	$post = newPost();
	$post['parent'] =setParent();
	$post['ip'] = $_SERVER['REMOTE_ADDR'];
	
	list($post['name'], $post['tripcode']) = nameAndTripcode($_POST["name"]);
	
	$post['name'] = cleanString(substr($post['name'], 0, 75));
	$post['email'] = cleanString(str_replace('"', '&quot;', substr($_POST["email"], 0, 75)));
	$post['subject'] = cleanString(substr($_POST["subject"], 0, 75));
	if ($modpost) {
		$modposttext = ($isadmin) ? ' <span style="color: red;">## Admin</span>' : ' <span style="color: purple;">## Mod</span>';
		$post['message'] = $_POST["message"]; // Treat message as raw HTML
	} else {
		$modposttext = '';
		$post['message'] = str_replace("\n", "<br>", colorQuote(cleanString(rtrim($_POST["message"]))));
	}
	$post['password'] = ($_POST['password'] != '') ? md5(md5($_POST['password'])) : '';
	if (strtolower($post['email']) == "noko") {
		$post['email'] = '';
		$noko = true;
	} else {
		$noko = false;
	}
	$post['nameblock'] = nameBlock($post['name'], $post['tripcode'], $post['email'], time(), $modposttext);
	
	if (isset($_FILES['file'])) {
		if ($_FILES['file']['name'] != "") {
			validateFileUpload();
			
			if (!is_file($_FILES['file']['tmp_name']) || !is_readable($_FILES['file']['tmp_name'])) {
				fancyDie("File transfer failure. Please retry the submission.");
			}
			
			$post['file_original'] = substr(htmlentities($_FILES['file']['name'], ENT_QUOTES), 0, 50);
			$post['file_hex'] = md5_file($_FILES['file']['tmp_name']);
			$post['file_size'] = $_FILES['file']['size'];
			$post['file_size_formatted'] = convertBytes($post['file_size']);
			$file_type = strtolower(preg_replace('/.*(\..+)/', '\1', $_FILES['file']['name'])); if ($file_type == '.jpeg') { $file_type = '.jpg'; }
			$file_name = time() . mt_rand(1, 99);
			$post['thumb'] = $file_name . "s" . $file_type;
			$post['file'] = $file_name . $file_type;
			$thumb_location = "thumb/" . $post['thumb'];
			$file_location = "src/" . $post['file'];
			
			if (!($file_type == '.jpg' || $file_type == '.gif' || $file_type == '.png')) {
				fancyDie("Only GIF, JPG, and PNG files are allowed.");
			}
			
			if (!@getimagesize($_FILES['file']['tmp_name'])) {
				fancyDie("Failed to read the size of the uploaded file. Please retry the submission.");
			}
			$file_info = getimagesize($_FILES['file']['tmp_name']);
			$file_mime = $file_info['mime'];
			
			if (!($file_mime == "image/jpeg" || $file_mime == "image/gif" || $file_mime == "image/png")) {
				fancyDie("Only GIF, JPG, and PNG files are allowed.");
			}

			checkDuplicateImage($post['file_hex']);
			
			if (!move_uploaded_file($_FILES['file']['tmp_name'], $file_location)) {
				fancyDie("Could not copy uploaded file.");
			}
			
			if ($_FILES['file']['size'] != filesize($file_location)) {
				fancyDie("File transfer failure. Please go back and try again.");
			}
			
			$post['image_width'] = $file_info[0]; $post['image_height'] = $file_info[1];
			
			list($thumb_maxwidth, $thumb_maxheight) = thumbnailDimensions($post['image_width'], $post['image_height']);
			 
			if (!createThumbnail($file_location, $thumb_location, $thumb_maxwidth, $thumb_maxheight)) {
				fancyDie("Could not create thumbnail.");
			}

			$thumb_info = getimagesize($thumb_location);
			$post['thumb_width'] = $thumb_info[0]; $post['thumb_height'] = $thumb_info[1];
		}
	}
	
	if ($post['file'] == '') { // No file uploaded
		if ($post['parent'] == '0') {
			fancyDie("An image is required to start a thread.");
		}
		if (str_replace('<br>', '', $post['message']) == "") {
			fancyDie("Please enter a message and/or upload an image to make a reply.");
		}
	}
	
	$post['id'] = insertPost($post);
	if ($noko) {
		$redirect = ($post['parent'] != '0') ? 'res/' . $post['parent'] . '.html#' . $post['id'] : 'res/' . $post['id'] . '.html#' . $post['id'];
	}
	trimThreads();
	echo 'Updating thread page...<br>';
	if ($post['parent'] != '0') {
		rebuildThread($post['parent']);
		
		if (strtolower($post['email']) != "sage") {
			bumpThreadByID($post['parent']);
		}
	} else {
		rebuildThread($post['id']);
	}
	
	echo 'Updating thread index...<br>';
	rebuildIndexes();
// Check if the request is to delete a post and/or its associated image
} elseif (isset($_GET['delete']) && !isset($_GET['manage'])) {
	if (isset($_POST['delete'])) {
		$post = postByID($_POST['delete']);
		if ($post) {
			if ($post['password'] != '' && md5(md5($_POST['password'])) == $post['password']) {
				deletePostByID($post['id']);
				if ($post['parent'] == 0) { threadUpdated($post['id']); } else { threadUpdated($post['parent']); }
				echo 'Post successfully deleted.';
			} else {
				fancyDie('Invalid password.');
			}
		} else {
			fancyDie('Sorry, an invalid post identifier was sent.  Please go back, refresh the page, and try again.');
		}
	} else {
		fancyDie('Tick the box next to a post and click "Delete" to delete it.');
	}
	$redirect = false;
// Check if the request is to access the management area
} elseif (isset($_GET["manage"])) {
	$text = ""; $onload = ""; $navbar = "&nbsp;";
	$redirect = false; $loggedin = false; $isadmin = false;
	$returnlink = basename($_SERVER['PHP_SELF']);
	
	list($loggedin, $isadmin) = manageCheckLogIn();
	
	if ($loggedin) {
		if ($isadmin) {
			if (isset($_GET["rebuildall"])) {
				$allthreads = allThreads();
				foreach ($allthreads as $thread) {
					rebuildThread($thread["id"]);
				}
				rebuildIndexes();
				$text .= "Rebuilt board.";
			} elseif (isset($_GET["bans"])) {
				clearExpiredBans();
				
				if (isset($_POST['ip'])) {
					if ($_POST['ip'] != '') {
						$banexists = banByIP($_POST['ip']);
						if ($banexists) {
							fancyDie('Sorry, there is already a ban on record for that IP address.');
						}
						
						$ban = array();
						$ban['ip'] = $_POST['ip'];
						$ban['expire'] = ($_POST['expire'] > 0) ? (time() + $_POST['expire']) : 0;
						$ban['reason'] = $_POST['reason'];
						
						insertBan($ban);
						$text .= '<b>Successfully added a ban record for ' . $ban['ip'] . '</b><br>';
					}
				} elseif (isset($_GET['lift'])) {
					$ban = banByID($_GET['lift']);
					if ($ban) {
						deleteBanByID($_GET['lift']);
						$text .= '<b>Successfully lifted ban on ' . $ban['ip'] . '</b><br>';
					}
				}
				
				$onload = manageOnLoad('bans');
				$text .= manageBanForm();
				$text .= manageBansTable();
			}
		}
		
		if (isset($_GET["delete"])) {
			$post = postByID($_GET['delete']);
			if ($post) {
				deletePostByID($post['id']);
				rebuildIndexes();
				if ($post['parent'] > 0) {
					rebuildThread($post['parent']);
				}
				$text .= '<b>Post No.' . $post['id'] . ' successfully deleted.</b>';
			} else {
				fancyDie("Sorry, there doesn't appear to be a post with that ID.");
			}
		} elseif (isset($_GET["moderate"])) {
			if ($_GET['moderate'] > 0) {
				$post = postByID($_GET['moderate']);
				if ($post) {
					$text .= manageModeratePost($post);
				} else {
					fancyDie("Sorry, there doesn't appear to be a post with that ID.");
				}
			} else {
				$onload = manageOnLoad('moderate');
				$text .= manageModeratePostForm();
			}
		} elseif (isset($_GET["modpost"])) {
			$onload = manageOnLoad('modpost');
			$text .= manageModpostForm();
		} elseif (isset($_GET["logout"])) {
			$_SESSION['tinyib'] = '';
			session_destroy();
			die('--&gt; --&gt; --&gt;<meta http-equiv="refresh" content="0;url=' . $returnlink . '?manage">');
		}
		if ($text == '') {
			$threads = countThreads();
			$bans = count(allBans());
			$text = $threads . ' ' . plural('thread', $threads) . ', ' . $bans . ' ' . plural('ban', $bans) . '.';
		}
	} else {
		$onload = manageOnLoad('login');
		$text .= manageLogInForm();
	}

	echo managePage($text, $onload);
} elseif (!file_exists('index.html') || count(allThreads()) == 0) {
	rebuildIndexes();
}

if ($redirect) {
	echo '--&gt; --&gt; --&gt;<meta http-equiv="refresh" content="0;url=' . (is_string($redirect) ? $redirect : 'index.html') . '">';
}

?>