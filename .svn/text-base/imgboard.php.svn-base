<?php
# TinyIB
#
# http://tinyib.googlecode.com/

error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();

if (get_magic_quotes_gpc()) {
	foreach ($_GET as $key => $val) { $_GET[$key] = stripslashes($val); }
	foreach ($_POST as $key => $val) { $_POST[$key] = stripslashes($val); }
}
if (get_magic_quotes_runtime()) { set_magic_quotes_runtime(0); }

$tinyib = array();
$tinyib['board'] = "b"; // Identifier for this board using only letters and numbers
$tinyib['boarddescription'] = "TinyIB"; // Displayed in the logo area
$tinyib['maxthreads'] = 100; // Set this to limit the number of threads allowed before discarding older threads.  0 to disable
$tinyib['logo'] = ""; // Logo HTML
$tinyib['tripseed'] = ""; // Text to use when generating secure tripcodes
$tinyib['adminpassword'] = ""; // Text entered at the manage prompt to gain administrator access
$tinyib['modpassword'] = ""; // Same as above, but only has access to delete posts. Blank ("") to disable
$tinyib['databasemode'] = "flatfile"; // flatfile or mysql

// mysql settings
$mysql_host = "localhost";
$mysql_username = "";
$mysql_password = "";
$mysql_database = "";
$mysql_posts_table = $tinyib['board'] . "_posts";
$mysql_bans_table = "bans";

function fancyDie($message) {
	die('<span style="color: red;font-size: 1.5em;font-family: Helvetica;">' . $message . '</span>');
}

// Check directories are writable by the script
$writedirs = array("res", "src", "thumb");
if ($tinyib['databasemode'] == 'flatfile') { $writedirs[] = "inc/flatfile"; }
foreach ($writedirs as $dir) {
	if (!is_writable($dir)) {
		fancyDie("Directory '" . $dir . "' can not be written to! Please modify its permissions.");
	}
}

$includes = array("inc/functions.php", "inc/html.php");
if ($tinyib['databasemode'] == 'flatfile') {
	$includes[] = 'inc/database_flatfile.php';
} elseif ($tinyib['databasemode'] == 'mysql') {
	$includes[] = 'inc/database_mysql.php';
} else {
	fancyDie("Unknown database mode specificed");
}

foreach ($includes as $include) {
	include $include;
}

if ($tinyib['tripseed'] == '' || $tinyib['adminpassword'] == '') {
	fancyDie('$tinyib[\'tripseed\'] and $tinyib[\'adminpassword\'] still need to be configured!');
}

$redirect = true;
// Check if the request is to make a post
if (isset($_POST["message"]) || isset($_POST["file"])) {
	$ban = banByIP($_SERVER['REMOTE_ADDR']);
	if ($ban) {
		if ($ban['expire'] == 0 || $ban['expire'] > time()) {
			$expire = ($ban['expire'] > 0) ? ('Your ban will expire ' . date('y/m/d(D)H:i:s', $ban['expire'])) : 'The ban on your IP address is permanent and will not expire.';
			$reason = ($ban['reason'] == '') ? '' : ('<br>The reason provided was: ' . $ban['reason']);
			fancyDie('Sorry, it appears that you have been banned from posting on this image board.  ' . $expire . $reason);
		} else {
			clearExpiredBans();
		}
	}

	$parent = "0";
	if (isset($_POST["parent"])) {
		if ($_POST["parent"] != "0") {
			if (!threadExistsByID($_POST['parent'])) {
				fancyDie("Invalid parent thread ID supplied, unable to create post.");
			}
			
			$parent = $_POST["parent"];
		}
	}
	
	$lastpost = lastPostByIP();
	if ($lastpost) {
		if ((time() - $lastpost['timestamp']) < 30) {
			fancyDie("Please wait a moment before posting again.  You will be able to make another post in " . (30 - (time() - $lastpost['timestamp'])) . " seconds.");
		}
	}
	
	if (strlen($_POST["message"]) > 8000) {
		fancyDie("Please shorten your message, or post it in multiple parts. Your message is " . strlen($_POST["message"]) . " characters long, and the maximum allowed is 8000.");
	}
	
	$post = newPost();
	$post['parent'] = $parent;
	$post['ip'] = $_SERVER['REMOTE_ADDR'];
	
	$nt = nameAndTripcode($_POST["name"]);
	$post['name']     = $nt[0];
	$post['tripcode'] = $nt[1];
	
	$post['name'] = cleanString(substr($post['name'], 0, 75));
	$post['email'] = cleanString(str_replace('"', '&quot;', substr($_POST["email"], 0, 75)));
	$post['subject'] = cleanString(substr($_POST["subject"], 0, 75));
	$post['message'] = str_replace("\n", "<br>", colorQuote(cleanString(rtrim($_POST["message"]))));
	if ($_POST['password'] != '') { $post['password'] = md5(md5($_POST['password'])); } else { $post['password'] = ''; }
	$post['nameblock'] = nameBlock($post['name'], $post['tripcode'], $post['email'], time());
	
	if (isset($_FILES['file'])) {
		if ($_FILES['file']['name'] != "") {
			switch ($_FILES['file']['error']) {
				case UPLOAD_ERR_OK:
					break;
				case UPLOAD_ERR_FORM_SIZE:
					fancyDie("That file is larger than 2 MB.");
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
			
			if(function_exists("mime_content_type")) {
				$file_mime = mime_content_type($_FILES['file']['tmp_name']);
			} else {
				$file_mime = "image/jpeg"; // It is highly recommended you use PHP 4.3.0 or later!
			}
			
			if (($file_type == '.jpg' || $file_type == '.gif' || $file_type == '.png') && ($file_mime == "image/jpeg" || $file_mime == "image/gif" || $file_mime == "image/png")) {
				if (!@getimagesize($_FILES['file']['tmp_name'])) {
					fancyDie("Failed to read the size of the uploaded file. Please retry the submission.");
				}
			} else {
				fancyDie("Only GIF, JPG, and PNG files are allowed.");
			}
			
			
			$hexmatches = postsByHex($post['file_hex']);
			if (count($hexmatches) > 0) {
				foreach ($hexmatches as $hexmatch) {
					if ($hexmatch["parent"] == "0") {
						$goto = $hexmatch["id"];
					} else {
						$goto = $hexmatch["parent"];
					}
					fancyDie("Duplicate file uploaded. That file has already been posted <a href=\"res/" . $goto . ".html#" . $hexmatch["id"] . "\">here</a>.");
				}
			}
			
			if (!move_uploaded_file($_FILES['file']['tmp_name'], $file_location)) {
				fancyDie("Could not copy uploaded file.");
			}
			
			if ($_FILES['file']['size'] != filesize($file_location)) {
				fancyDie("File transfer failure. Please go back and try again.");
			}
			
			$file_imagesize = getimagesize($file_location);
			$post['image_width'] = $file_imagesize[0];
			$post['image_height'] = $file_imagesize[1];
			
			if ($post['image_width'] > 250 || $post['image_height'] > 250) {
				$width = 250;
				$height = 250;
			} else {
				$width = $post['image_width'];
				$height = $post['image_height'];
			}
			if (!createThumbnail($file_location, $thumb_location, $width, $height)) {
				fancyDie("Could not create thumbnail.");
			}

			$thumbsize = getimagesize($thumb_location);
			$post['thumb_width'] = $thumbsize[0];
			$post['thumb_height'] = $thumbsize[1];
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
		} elseif (isset($_GET["logout"])) {
			$_SESSION['tinyib'] = '';
			session_destroy();
			die('--&gt; --&gt; --&gt;<meta http-equiv="refresh" content="0;url=' . $returnlink . '?manage">');
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
	echo '--&gt; --&gt; --&gt;<meta http-equiv="refresh" content="0;url=index.html">';
}

?>