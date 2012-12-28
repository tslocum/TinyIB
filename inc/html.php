<?php
if (!defined('TINYIB_BOARD')) { die(''); }

function pageHeader() {
	$return = <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>
EOF;
	$return .= TINYIB_BOARDDESC . <<<EOF
		</title>
		<link rel="shortcut icon" href="favicon.ico">
		<link rel="stylesheet" type="text/css" href="css/global.css">
		<link rel="stylesheet" type="text/css" href="css/futaba.css" title="Futaba">
		<link rel="alternate stylesheet" type="text/css" href="css/burichan.css" title="Burichan">
		<meta http-equiv="content-type" content="text/html;charset=UTF-8">
		<meta http-equiv="pragma" content="no-cache">
		<meta http-equiv="expires" content="-1">
	</head>
EOF;
	return $return;
}

function pageFooter() {
	/* If the footer is removed from the page, please link to TinyIB somewhere on the site. */
	return <<<EOF
		<div class="footer">
			- <a href="http://www.2chan.net" target="_top">futaba</a> + <a href="http://www.1chan.net" target="_top">futallaby</a> + <a href="https://github.com/tslocum/TinyIB" target="_top">tinyib</a> -
		</div>
	</body>
</html>
EOF;
}

function buildPost($post, $res) {
	$return = "";
	$threadid = ($post['parent'] == TINYIB_NEWTHREAD) ? $post['id'] : $post['parent'];
	$postlink = ($res == TINYIB_RESPAGE) ? ($threadid . '.html#' . $post['id']) : ('res/' . $threadid . '.html#' . $post['id']);
	if (!isset($post["omitted"])) { $post["omitted"] = 0; }
	
	if ($post["parent"] != TINYIB_NEWTHREAD) {
		$return .= <<<EOF
<table>
<tbody>
<tr>
<td class="doubledash">
	&#0168;
</td>
<td class="reply" id="reply${post["id"]}">
EOF;
	} elseif ($post["file"] != "") {
		$return .= <<<EOF
<span class="filesize">File: <a href="src/${post["file"]}">${post["file"]}</a>&ndash;(${post["file_size_formatted"]}, ${post["image_width"]}x${post["image_height"]}, ${post["file_original"]})</span>
<br>
<a target="_blank" href="src/${post["file"]}">
<span id="thumb${post['id']}"><img src="thumb/${post["thumb"]}" alt="${post["id"]}" class="thumb" width="${post["thumb_width"]}" height="${post["thumb_height"]}"></span>
</a>
EOF;
	}
	
	$return .= <<<EOF
<a name="${post['id']}"></a>
<label>
	<input type="checkbox" name="delete" value="${post['id']}"> 
EOF;

	if ($post['subject'] != '') {
		$return .= '	<span class="filetitle">' . $post['subject'] . '</span> ';
	}
	
	$return .= <<<EOF
${post["nameblock"]}
</label>
<span class="reflink">
	<a href="$postlink">No.${post["id"]}</a>
</span>
EOF;
	
	if ($post['parent'] != TINYIB_NEWTHREAD && $post["file"] != "") {
		$return .= <<<EOF
<br>
<span class="filesize"><a href="src/${post["file"]}">${post["file"]}</a>&ndash;(${post["file_size_formatted"]}, ${post["image_width"]}x${post["image_height"]}, ${post["file_original"]})</span>
<br>
<a target="_blank" href="src/${post["file"]}">
	<span id="thumb${post["id"]}"><img src="thumb/${post["thumb"]}" alt="${post["id"]}" class="thumb" width="${post["thumb_width"]}" height="${post["thumb_height"]}"></span>
</a>
EOF;
	}
	
	if ($post['parent'] == TINYIB_NEWTHREAD && $res == TINYIB_INDEXPAGE) {
		$return .= "&nbsp;[<a href=\"res/${post["id"]}.html\">Reply</a>]";
	}
	
	if (TINYIB_TRUNCATE > 0 && !$res && substr_count($post['message'], '<br>') > TINYIB_TRUNCATE) { // Truncate messages on board index pages for readability
		$br_offsets = strallpos($post['message'], '<br>');
		$post['message'] = substr($post['message'], 0, $br_offsets[TINYIB_TRUNCATE - 1]);
		$post['message'] .= '<br><span class="omittedposts">Post truncated.  Click Reply to view.</span><br>';
	}
	$return .= <<<EOF
<blockquote>
${post["message"]}
</blockquote>
EOF;

	if ($post['parent'] == TINYIB_NEWTHREAD) {
		if ($res == TINYIB_INDEXPAGE && $post['omitted'] > 0) {
			$return .= '<span class="omittedposts">' . $post['omitted'] . ' ' . plural('post', $post['omitted']) . ' omitted. Click Reply to view.</span>';
		}
	} else {
		$return .= <<<EOF
</td>
</tr>
</tbody>
</table>
EOF;
	}
	
	return $return;
}

function buildPage($htmlposts, $parent, $pages=0, $thispage=0) {
	$managelink = basename($_SERVER['PHP_SELF']) . "?manage";
	$maxdimensions = TINYIB_MAXW . 'x' . TINYIB_MAXH;
	$maxfilesize = TINYIB_MAXKB * 1024;
	
	$postingmode = "";
	$pagenavigator = "";
	if ($parent == TINYIB_NEWTHREAD) {
		$pages = max($pages, 0);
		$previous = ($thispage == 1) ? "index" : $thispage - 1;
		$next = $thispage + 1;
		
		$pagelinks = ($thispage == 0) ? "<td>Previous</td>" : '<td><form method="get" action="' . $previous . '.html"><input value="Previous" type="submit"></form></td>';
		
		$pagelinks .= "<td>";
		for ($i = 0;$i <= $pages;$i++) {
			if ($thispage == $i) {
				$pagelinks .= '&#91;' . $i . '&#93; ';
			} else {
				$href = ($i == 0) ? "index" : $i;
				$pagelinks .= '&#91;<a href="' . $href . '.html">' . $i . '</a>&#93; ';
			}
		}
		$pagelinks .= "</td>";
		
		$pagelinks .= ($pages <= $thispage) ? "<td>Next</td>" : '<td><form method="get" action="' . $next . '.html"><input value="Next" type="submit"></form></td>';
		
		$pagenavigator = <<<EOF
<table border="1">
	<tbody>
		<tr>
			$pagelinks
		</tr>
	</tbody>
</table>
EOF;
	} else {
		$postingmode = '&#91;<a href="../">Return</a>&#93;<div class="replymode">Posting mode: Reply</div> ';
	}
	
	$unique_posts_html = '';
	$unique_posts = uniquePosts();
	if ($unique_posts > 0) {
		$unique_posts_html = "<li>Currently $unique_posts unique user posts.</li>\n";
	}
	
	$max_file_size_html = '';
	if (TINYIB_MAXKB > 0) {
		$max_file_size_html = "<li>Maximum file size allowed is " . TINYIB_MAXKBDESC . ".</li>\n";
	}
	
	$body = <<<EOF
	<body>
		<div class="adminbar">
			[<a href="$managelink" style="text-decoration: underline;">Manage</a>]
		</div>
		<div class="logo">
EOF;
	$body .= TINYIB_LOGO .  TINYIB_BOARDDESC . <<<EOF
		</div>
		<hr width="90%" size="1">
		$postingmode
		<div class="postarea">
			<form name="postform" id="postform" action="imgboard.php" method="post" enctype="multipart/form-data">
			<input type="hidden" name="MAX_FILE_SIZE" value="$maxfilesize">
			<input type="hidden" name="parent" value="$parent">
			<table class="postform">
				<tbody>
					<tr>
						<td class="postblock">
							Name
						</td>
						<td>
							<input type="text" name="name" size="28" maxlength="75" accesskey="n">
						</td>
					</tr>
					<tr>
						<td class="postblock">
							E-mail
						</td>
						<td>
							<input type="text" name="email" size="28" maxlength="75" accesskey="e">
						</td>
					</tr>
					<tr>
						<td class="postblock">
							Subject
						</td>
						<td>
							<input type="text" name="subject" size="40" maxlength="75" accesskey="s">
							<input type="submit" value="Submit" accesskey="z">
						</td>
					</tr>
					<tr>
						<td class="postblock">
							Message
						</td>
						<td>
							<textarea name="message" cols="48" rows="4" accesskey="m"></textarea>
						</td>
					</tr>
					<tr>
						<td class="postblock">
							File
						</td>
						<td>
							<input type="file" name="file" size="35" accesskey="f">
						</td>
					</tr>
					<tr>
						<td class="postblock">
							Password
						</td>
						<td>
							<input type="password" name="password" size="8" accesskey="p">&nbsp;(for post and file deletion)
						</td>
					</tr>
					<tr>
						<td colspan="2" class="rules">
							<ul>
								<li>Supported file types are: GIF, JPG, PNG</li>
								$max_file_size_html
								<li>Images greater than $maxdimensions pixels will be thumbnailed.</li>
								$unique_posts_html
							</ul>
						</td>
					</tr>
				</tbody>
			</table>
			</form>
		</div>
		<hr>
		<form id="delform" action="imgboard.php?delete" method="post">
		<input type="hidden" name="board" 
EOF;
		$body .= 'value="' . TINYIB_BOARD . '">' . <<<EOF
		$htmlposts
		<table class="userdelete">
			<tbody>
				<tr>
					<td>
						Delete Post <input type="password" name="password" size="8" placeholder="Password">&nbsp;<input name="deletepost" value="Delete" type="submit"> 
					</td>
				</tr>
			</tbody>
		</table>
		</form>
		$pagenavigator
		<br>
EOF;
	return pageHeader() . $body . pageFooter();
}

function rebuildIndexes() {	
	$page = 0; $i = 0; $htmlposts = '';
	$pages = ceil(countThreads() / 10) - 1;
	$threads = allThreads(); 
	
	foreach ($threads as $thread) {
		$replies = latestRepliesInThreadByID($thread['id']);
		
		$htmlreplies = array();
		foreach ($replies as $reply) {
			$htmlreplies[] = buildPost($reply, TINYIB_INDEXPAGE);
		}
		
		$thread['omitted'] = (count($htmlreplies) == 3) ? (count(postsInThreadByID($thread['id'])) - 4) : 0;
		
		$htmlposts .= buildPost($thread, TINYIB_INDEXPAGE) . implode('', array_reverse($htmlreplies)) . "<br clear=\"left\">\n<hr>";
		
		$i += 1;
		if ($i == 10) {
			$file = ($page == 0) ? 'index.html' : $page . '.html';
			writePage($file, buildPage($htmlposts, 0, $pages, $page));
			
			$page += 1; $i = 0; $htmlposts = '';
		}
	}
	
	if ($page == 0 || $htmlposts != '') {
		$file = ($page == 0) ? 'index.html' : $page . '.html';
		writePage($file, buildPage($htmlposts, 0, $pages, $page));
	}
}

function rebuildThread($id) {
	$htmlposts = "";
	$posts = postsInThreadByID($id);
	foreach ($posts as $post) {
		$htmlposts .= buildPost($post, TINYIB_RESPAGE);
	}
	
	$htmlposts .= "<br clear=\"left\">\n<hr>\n";
	
	writePage('res/' . $id . '.html', fixLinksInRes(buildPage($htmlposts, $id)));
}

function adminBar() {
	global $loggedin, $isadmin, $returnlink;
	$return = '[<a href="' . $returnlink . '" style="text-decoration: underline;">Return</a>]';
	if (!$loggedin) { return $return; }
	return '[<a href="?manage">Status</a>] [' . (($isadmin) ? '<a href="?manage&bans">Bans</a>] [' : '') . '<a href="?manage&moderate">Moderate Post</a>] [<a href="?manage&rawpost">Raw Post</a>] [' . (($isadmin) ? '<a href="?manage&rebuildall">Rebuild All</a>] [' : '') . '<a href="?manage&logout">Log Out</a>] &middot; ' . $return;
}

function managePage($text, $onload='') {
	$adminbar = adminBar();
	$body = <<<EOF
	<body$onload>
		<div class="adminbar">
			$adminbar
		</div>
		<div class="logo">
EOF;
	$body .= TINYIB_LOGO . TINYIB_BOARDDESC . <<<EOF
		</div>
		<hr width="90%" size="1">
		<div class="replymode">Manage mode</div>
		$text
		<hr>
EOF;
	return pageHeader() . $body . pageFooter();
}

function manageOnLoad($page) {
	switch ($page) {
		case 'login':
			return ' onload="document.tinyib.password.focus();"';
		case 'moderate':
			return ' onload="document.tinyib.moderate.focus();"';
		case 'rawpost':
			return ' onload="document.tinyib.message.focus();"';
		case 'bans':
			return ' onload="document.tinyib.ip.focus();"';
	}
}

function manageLogInForm() {
	return <<<EOF
	<form id="tinyib" name="tinyib" method="post" action="?manage">
	<fieldset>
	<legend align="center">Enter an administrator or moderator password</legend>
	<div class="login">
	<input type="password" id="password" name="password"><br>
	<input type="submit" value="Log In" class="managebutton">
	</div>
	</fieldset>
	</form>
	<br>
EOF;
}

function manageBanForm() {
	return <<<EOF
	<form id="tinyib" name="tinyib" method="post" action="?manage&bans">
	<fieldset>
	<legend>Ban an IP address</legend>
	<label for="ip">IP Address:</label> <input type="text" name="ip" id="ip" value="${_GET['bans']}"> <input type="submit" value="Submit" class="managebutton"><br>
	<label for="expire">Expire(sec):</label> <input type="text" name="expire" id="expire" value="0">&nbsp;&nbsp;<small><a href="#" onclick="document.tinyib.expire.value='3600';return false;">1hr</a>&nbsp;<a href="#" onclick="document.tinyib.expire.value='86400';return false;">1d</a>&nbsp;<a href="#" onclick="document.tinyib.expire.value='172800';return false;">2d</a>&nbsp;<a href="#" onclick="document.tinyib.expire.value='604800';return false;">1w</a>&nbsp;<a href="#" onclick="document.tinyib.expire.value='1209600';return false;">2w</a>&nbsp;<a href="#" onclick="document.tinyib.expire.value='2592000';return false;">30d</a>&nbsp;<a href="#" onclick="document.tinyib.expire.value='0';return false;">never</a></small><br>
	<label for="reason">Reason:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label> <input type="text" name="reason" id="reason">&nbsp;&nbsp;<small>optional</small>
	<legend>
	</fieldset>
	</form><br>
EOF;
}

function manageBansTable() {
	$text = '';
	$allbans = allBans();
	if (count($allbans) > 0) {
		$text .= '<table border="1"><tr><th>IP Address</th><th>Set At</th><th>Expires</th><th>Reason Provided</th><th>&nbsp;</th></tr>';
		foreach ($allbans as $ban) {
			$expire = ($ban['expire'] > 0) ? date('y/m/d(D)H:i:s', $ban['expire']) : 'Does not expire';
			$reason = ($ban['reason'] == '') ? '&nbsp;' : htmlentities($ban['reason']);
			$text .= '<tr><td>' . $ban['ip'] . '</td><td>' . date('y/m/d(D)H:i:s', $ban['timestamp']) . '</td><td>' . $expire . '</td><td>' . $reason . '</td><td><a href="?manage&bans&lift=' . $ban['id'] . '">lift</a></td></tr>';
		}
		$text .= '</table>';
	}
	return $text;
}

function manageModeratePostForm() {
	return <<<EOF
	<form id="tinyib" name="tinyib" method="get" action="?">
	<input type="hidden" name="manage" value="">
	<fieldset>
	<legend>Moderate a post</legend>
	<div valign="top"><label for="moderate">Post ID:</label> <input type="text" name="moderate" id="moderate"> <input type="submit" value="Submit" class="managebutton"></div><br>
	<small><b>Tip:</b> While browsing the image board, you can easily moderate a post if you are logged in:<br>
	Tick the box next to a post and click "Delete" at the bottom of the page with a blank password.</small><br>
	</fieldset>
	</form><br>
EOF;
}

function manageRawPostForm() {
	return <<<EOF
	<div class="postarea">
		<form id="tinyib" name="tinyib" method="post" action="?" enctype="multipart/form-data">
		<input type="hidden" name="rawpost" value="1">
		<input type="hidden" name="MAX_FILE_SIZE" value="2097152">
		<table class="postform">
			<tbody>
				<tr>
					<td class="postblock">
						Reply to
					</td>
					<td>
						<input type="text" name="parent" size="28" maxlength="75" value="0" accesskey="t">&nbsp;0 to start a new thread
					</td>
				</tr>
				<tr>
					<td class="postblock">
						Name
					</td>
					<td>
						<input type="text" name="name" size="28" maxlength="75" accesskey="n">
					</td>
				</tr>
				<tr>
					<td class="postblock">
						E-mail
					</td>
					<td>
						<input type="text" name="email" size="28" maxlength="75" accesskey="e">
					</td>
				</tr>
				<tr>
					<td class="postblock">
						Subject
					</td>
					<td>
						<input type="text" name="subject" size="40" maxlength="75" accesskey="s">
						<input type="submit" value="Submit" accesskey="z">
					</td>
				</tr>
				<tr>
					<td class="postblock">
						Message
					</td>
					<td>
						<textarea name="message" cols="48" rows="4" accesskey="m"></textarea>
					</td>
				</tr>
				<tr>
					<td class="postblock">
						File
					</td>
					<td>
						<input type="file" name="file" size="35" accesskey="f">
					</td>
				</tr>
				<tr>
					<td class="postblock">
						Password
					</td>
					<td>
						<input type="password" name="password" size="8" accesskey="p">&nbsp;(for post and file deletion)
					</td>
				</tr>
				<tr>
					<td colspan="2" class="rules">
						<ul>
							<li>Text entered in the Message field will be posted as is with no formatting applied.</li>
							<li>Line-breaks must be specified with "&lt;br&gt;".</li>
						</ul>
					</td>
				</tr>
			</tbody>
		</table>
		</form>
	</div>
EOF;
}

function manageModeratePost($post) {
	global $isadmin;
	$ban = banByIP($post['ip']);
	$ban_disabled = (!$ban && $isadmin) ? '' : ' disabled';
	$ban_info = (!$ban) ? ((!$isadmin) ? 'Only an administrator may ban an IP address.' : ('IP address: ' . $post["ip"])) : (' A ban record already exists for ' . $post['ip']);
	$delete_info = ($post['parent'] == TINYIB_NEWTHREAD) ? 'This will delete the entire thread below.' : 'This will delete the post below.';
	$post_or_thread = ($post['parent'] == TINYIB_NEWTHREAD) ? 'Thread' : 'Post';
	
	if ($post["parent"] == TINYIB_NEWTHREAD) {
		$post_html = "";
		$posts = postsInThreadByID($post["id"]);
		foreach ($posts as $post_temp) {
			$post_html .= buildPost($post_temp, TINYIB_INDEXPAGE);
		}
	} else {
		$post_html = buildPost($post, TINYIB_INDEXPAGE);
	}
	
	return <<<EOF
	<fieldset>
	<legend>Moderating No.${post['id']}</legend>
	
	<fieldset>
	<legend>Action</legend>
	
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	<tr><td align="right" width="50%;">
	
	<form method="get" action="?">
	<input type="hidden" name="manage" value="">
	<input type="hidden" name="delete" value="${post['id']}">
	<input type="submit" value="Delete $post_or_thread" class="managebutton" style="width: 50%;">
	</form>
	
	</td><td><small>$delete_info</small></td></tr>
	<tr><td align="right" width="50%;">
	
	<form method="get" action="?">
	<input type="hidden" name="manage" value="">
	<input type="hidden" name="bans" value="${post['ip']}">
	<input type="submit" value="Ban Poster" class="managebutton" style="width: 50%;"$ban_disabled>
	</form>
	
	</td><td><small>$ban_info</small></td></tr>
	
	</table>
	
	</fieldset>
	
	<fieldset>
	<legend>$post_or_thread</legend>	
	$post_html
	</fieldset>
	
	</fieldset>
	<br>
EOF;
}

function manageStatus() {
	$threads = countThreads();
	$bans = count(allBans());
	$info = $threads . ' ' . plural('thread', $threads) . ', ' . $bans . ' ' . plural('ban', $bans);
	
	$post_html = '';
	$posts = latestPosts();
	$i = 0;
	foreach ($posts as $post) {
		if ($post_html != '') { $post_html .= '<tr><td colspan="2"><hr></td></tr>'; }
		$post_html .= '<tr><td>' . buildPost($post, TINYIB_INDEXPAGE) . '</td><td valign="top"><form method="get" action="?"><input type="hidden" name="manage" value=""><input type="hidden" name="moderate" value="' . $post['id'] . '"><input type="submit" value="Moderate" class="managebutton"></form></td></tr>';
	}
	
	return <<<EOF
	<fieldset>
	<legend>Status</legend>
	
	<fieldset>
	<legend>Info</legend>
	$info
	</fieldset>
	
	<fieldset>
	<legend>Recent posts</legend>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	$post_html
	</table>
	</fieldset>
	
	</fieldset>
	<br>
EOF;
}

function manageInfo($text) {
	return '<div class="manageinfo">' . $text . '</div>';
}
?>
