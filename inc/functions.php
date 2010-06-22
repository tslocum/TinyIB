<?php
if (!isset($tinyib)) { die(''); }

function cleanString($string) {
	$search = array("<", ">");
	$replace = array("&lt;", "&gt;");
	
	return str_replace($search, $replace, $string);
}

function threadUpdated($id) {
	rebuildThread($id);
	rebuildIndexes();
}

function newPost() {
	return array('parent' => '0',
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
		return sprintf("%0.2fKB", $number/1024);
	} elseif ($len <= 9) {
		return sprintf("%0.2fMB", $number/1024/1024);
	}

	return sprintf("%0.2fGB", $number/1024/1024/1024);						
}

function nameAndTripcode($name) {
	global $tinyib;

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
		if ($cap != "") {
			/* From Futabally */
			$cap = strtr($cap, "&amp;", "&");
			$cap = strtr($cap, "&#44;", ", ");
			$salt = substr($cap."H.", 1, 2);
			$salt = preg_replace("/[^\.-z]/", ".", $salt);
			$salt = strtr($salt, ":;<=>?@[\\]^_`", "ABCDEFGabcdef"); 
			$tripcode = substr(crypt($cap, $salt), -10);
		}
		
		if ($is_secure_trip) {
			if ($cap != "") {
				$tripcode .= "!";
			}
			
			$tripcode .= "!" . substr(md5($cap_secure . $tinyib['tripseed']), 2, 10);
		}
		
		return array(preg_replace("/(" . $cap_delimiter . ")(.*)/", "", $name), $tripcode);
	}
	
	return array($name, "");
}

function nameBlock($name, $tripcode, $email, $timestamp) {
	$output = '<span class="postername">';
	
	if ($name == "" && $tripcode == "") {
		$output .= "Anonymous";
	} else {
		$output .= $name;
	}
	
	if ($tripcode != "") {
		$output .= '</span><span class="postertrip">!' . $tripcode;
	}
	
	$output .= '</span>';
	
	if ($email != "") {
		$output = '<a href="mailto:' . $email . '">' . $output . '</a>';
	}

	return $output . ' ' . date('y/m/d(D)H:i:s', $timestamp);
}

function writePage($filename, $contents) {
	global $tinyib;
	
	$tempfile = tempnam('res/', $tinyib['board'] . 'tmp'); /* Create the temporary file */
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

function colorQuote($message) {
	if (substr($message, -1, 1) != "\n") { $message .= "\n"; }
	return preg_replace('/^(&gt;[^\>](.*))\n/m', '<span class="unkfunc">\\1</span>' . "\n", $message);
}

function deletePostImages($post) {
	if ($post['file'] != '') { @unlink('src/' . $post['file']); }
	if ($post['thumb'] != '') { @unlink('thumb/' . $post['thumb']); }
}

function manageCheckLogIn() {
	global $tinyib;
	$loggedin = false; $isadmin = false;
	if (isset($_POST['password'])) {
		if ($_POST['password'] == $tinyib['adminpassword']) {
			$_SESSION['tinyib'] = $tinyib['adminpassword'];
		} elseif ($tinyib['modpassword'] != '' && $_POST['password'] == $tinyib['modpassword']) {
			$_SESSION['tinyib'] = $tinyib['modpassword'];
		}
	}
	
	if (isset($_SESSION['tinyib'])) {
		if ($_SESSION['tinyib'] == $tinyib['adminpassword']) {
			$loggedin = true;
			$isadmin = true;
		} elseif ($tinyib['modpassword'] != '' && $_SESSION['tinyib'] == $tinyib['modpassword']) {
			$loggedin = true;
		}
	}
	
	return array($loggedin, $isadmin);
}

function createThumbnail($name, $filename, $new_w, $new_h) {
	$system=explode(".", $filename);
	$system = array_reverse($system);
	if (preg_match("/jpg|jpeg/", $system[0])) {
		$src_img=imagecreatefromjpeg($name);
	} else if (preg_match("/png/", $system[0])) {
		$src_img=imagecreatefrompng($name);
	} else if (preg_match("/gif/", $system[0])) {
		$src_img=imagecreatefromgif($name);
	} else {
		return false;
	}
	
	if (!$src_img) {
		fancyDie("Unable to read uploaded file during thumbnailing. A common cause for this is an incorrect extension when the file is actually of a different type.");
	}
	$old_x = imageSX($src_img);
	$old_y = imageSY($src_img);
	if ($old_x > $old_y) {
		$percent = $new_w / $old_x;
	} else {
		$percent = $new_h / $old_y;
	}
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
	//Author: Tim Eckel - Date: 12/17/04 - Project: FreeRingers.net - Freely distributable. 
	if (empty($src_image) || empty($dst_image)) { return false; }

	if ($quality <= 1) {
		$temp = imagecreatetruecolor ($dst_w + 1, $dst_h + 1);
		imagecopyresized ($temp, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w + 1, $dst_h + 1, $src_w, $src_h);
		imagecopyresized ($dst_image, $temp, 0, 0, 0, 0, $dst_w, $dst_h, $dst_w, $dst_h);
		imagedestroy ($temp);
	} elseif ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
		
		$tmp_w = $dst_w * $quality;
		$tmp_h = $dst_h * $quality;
		$temp = imagecreatetruecolor ($tmp_w + 1, $tmp_h + 1);
		
		imagecopyresized ($temp, $src_image, $dst_x * $quality, $dst_y * $quality, $src_x, $src_y, $tmp_w + 1, $tmp_h + 1, $src_w, $src_h);
		
		imagecopyresampled ($dst_image, $temp, 0, 0, 0, 0, $dst_w, $dst_h, $tmp_w, $tmp_h);
		
		imagedestroy ($temp);
		
	} else {
		imagecopyresampled ($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
	}

	
	return true;
}

?>