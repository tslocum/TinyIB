<?php
if (!isset($tinyib)) { die(''); }

function pageHeader() {
	global $tinyib;
	return <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>
			${tinyib['boarddescription']}
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
}

function pageFooter() {
	return <<<EOF
		<div class="footer">
			- <a href="http://www.2chan.net" target="_top">futaba</a> + <a href="http://www.1chan.net" target="_top">futallaby</a> + <a href="http://tj9991.github.com/TinyIB/" target="_top">tinyib</a> -
		</div>
	</body>
</html>
EOF;
}

function buildPost($post, $isrespage) {
	$return = "";
	$threadid = ($post['parent'] == 0) ? $post['id'] : $post['parent'];
	$postlink = ($isrespage) ? ($threadid . '.html#' . $post['id']) : ('res/' . $threadid . '.html#' . $post['id']);
	
	if ($post["parent"] != 0) {
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

	if ($post["subject"] != "") {
		$return .= "	<span class=\"filetitle\">${post["subject"]}</span> ";
	}
	
	$return .= <<<EOF
${post["nameblock"]}
</label>
<span class="reflink">
	<a href="$postlink">No.${post["id"]}</a>
</span>
EOF;
	
	if ($post['parent'] != 0 && $post["file"] != "") {
		$return .= <<<EOF
<br>
<span class="filesize"><a href="src/${post["file"]}">${post["file"]}</a>&ndash;(${post["file_size_formatted"]}, ${post["image_width"]}x${post["image_height"]}, ${post["file_original"]})</span>
<br>
<a target="_blank" href="src/${post["file"]}">
	<span id="thumb${post["id"]}"><img src="thumb/${post["thumb"]}" alt="${post["id"]}" class="thumb" width="${post["thumb_width"]}" height="${post["thumb_height"]}"></span>
</a>
EOF;
	}
	
	if ($post['parent'] == 0 && !$isrespage) {
		$return .= "&nbsp;[<a href=\"res/${post["id"]}.html\">Reply</a>]";
	}
	
	$return .= <<<EOF
<blockquote>
${post["message"]}
</blockquote>
EOF;

	if ($post['parent'] == 0) {
		if (!$isrespage && $post["omitted"] > 0) {
			$return .= '<span class="omittedposts">' . $post['omitted'] . ' ' . plural("post", $post["omitted"]) . ' omitted. Click Reply to view.</span>';
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
	global $tinyib;
	$managelink = basename($_SERVER['PHP_SELF']) . "?manage";
	
	$postingmode = "";
	$pagenavigator = "";
	if ($parent == 0) {
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
	$unique_posts_html = "<li>Currently $unique_posts unique user posts.</li>";
	}
	
	$body = <<<EOF
	<body>
		<div class="adminbar">
			[<a href="$managelink">Manage</a>]
		</div>
		<div class="logo">
			${tinyib['logo']}
			${tinyib['boarddescription']}
		</div>
		<hr width="90%" size="1">
		$postingmode
		<div class="postarea">
			<form name="postform" id="postform" action="imgboard.php" method="post" enctype="multipart/form-data">
			<input type="hidden" name="MAX_FILE_SIZE" value="2097152">
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
								<li>Maximum file size allowed is 2 MB.</li>
								<li>Images greater than 250x250 pixels will be thumbnailed.</li>
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
		<input type="hidden" name="board" value="${tinyib['board']}">
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
	global $mysql_posts_table;
	
	$htmlposts = "";
	$page = 0;
	$i = 0;
	$pages = ceil(countThreads() / 10) - 1;
	$threads = allThreads(); 
	foreach ($threads as $thread) {
		$htmlreplies = array();
		$replies = latestRepliesInThreadByID($thread['id']);
	foreach ($replies as $reply) {
			$htmlreplies[] = buildPost($reply, False);
		}
		if (count($htmlreplies) == 3) {
		$thread["omitted"] = (count(postsInThreadByID($thread['id'])) - 4);
	} else {
		$thread["omitted"] = 0;
		}
		
		$htmlposts .= buildPost($thread, False);

		$htmlposts .= implode("", array_reverse($htmlreplies));
		
		$htmlposts .= "<br clear=\"left\">\n" . 
									"<hr>";
		$i += 1;
		if ($i == 10) {
			$file = ($page == 0) ? "index.html" : $page . ".html";
			writePage($file, buildPage($htmlposts, 0, $pages, $page));
			
			$page += 1;
			$i = 0;
			$htmlposts = "";
		}
	}
	
	if ($page == 0 || $htmlposts != "") {
		$file = ($page == 0) ? "index.html" : $page . ".html";
		writePage($file, buildPage($htmlposts, 0, $pages, $page));
	}
}

function rebuildThread($id) {
	global $mysql_posts_table;
	
	$htmlposts = "";
	$posts = postsInThreadByID($id);
	foreach ($posts as $post) {
		$htmlposts .= buildPost($post, True);
	}
	
	$htmlposts .= "<br clear=\"left\">\n" . 
								"<hr>";
	
	writePage("res/" . $id . ".html", fixLinksInRes(buildPage($htmlposts, $id)));
}

function adminBar() {
	global $loggedin, $isadmin, $returnlink;
	if (!$loggedin) { return '[<a href="' . $returnlink . '">Return</a>]'; }
	$text = '[';
	$text .= ($isadmin) ? '<a href="?manage&bans">Bans</a>] [' : '';
	$text .= '<a href="?manage&moderate">Moderate Post</a>] [<a href="?manage&modpost">Mod Post</a>] [';
	$text .= ($isadmin) ? '<a href="?manage&rebuildall">Rebuild All</a>] [' : '';
	$text .= '<a href="?manage&logout">Log Out</a>] [<a href="' . $returnlink . '">Return</a>]';
	return $text;
}

function managePage($text, $onload='') {
	global $tinyib;
	
	$adminbar = adminBar();
	$body = <<<EOF
	<body$onload>
		<div class="adminbar">
			$adminbar
		</div>
		<div class="logo">
			${tinyib['logo']}
			${tinyib['boarddescription']}
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
		case 'modpost':
			return ' onload="document.tinyib.message.focus();"';
		case 'bans':
			return ' onload="document.tinyib.ip.focus();"';
	}
}

function manageLogInForm() {
	return <<<EOF
	<form id="tinyib" name="tinyib" method="post" action="?manage">
	<fieldset>
	<legend align="center">Please enter an administrator or moderator password</legend>
	<div class="login">
	<input type="password" id="password" name="password"><br>
	<input type="submit" value="Submit" class="managebutton">
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
	<legend>Ban an IP address from posting</legend>
	<label for="ip">IP Address:</label> <input type="text" name="ip" id="ip" value="${_GET['bans']}"> <input type="submit" value="Submit" class="managebutton"><br>
	<label for="expire">Expire(sec):</label> <input type="text" name="expire" id="expire" value="0">&nbsp;&nbsp;<small><a href="#" onclick="document.tinyib.expire.value='3600';return false;">1hr</a>&nbsp;<a href="#" onclick="document.tinyib.expire.value='86400';return false;">1d</a>&nbsp;<a href="#" onclick="document.tinyib.expire.value='172800';return false;">2d</a>&nbsp;<a href="#" onclick="document.tinyib.expire.value='604800';return false;">1w</a>&nbsp;<a href="#" onclick="document.tinyib.expire.value='1209600';return false;">2w</a>&nbsp;<a href="#" onclick="document.tinyib.expire.value='2592000';return false;">30d</a>&nbsp;<a href="#" onclick="document.tinyib.expire.value='0';return false;">never</a></small><br>
	<label for="reason">Reason:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label> <input type="text" name="reason" id="reason">&nbsp;&nbsp;<small>(optional)</small>
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
			$expire = ($ban['expire'] > 0) ? date('y/m/d(D)H:i:s', $ban['expire']) : 'Never';
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
	<label for="moderate">Post ID:</label> <input type="text" name="moderate" id="moderate"> <input type="submit" value="Submit" class="managebutton"><br>
	<legend>
	</fieldset>
	</form><br>
EOF;
}

function manageModpostForm() {
	return <<<EOF
	<div class="postarea">
		<form id="tinyib" name="tinyib" method="post" action="?" enctype="multipart/form-data">
		<input type="hidden" name="modpost" value="1">
		<input type="hidden" name="MAX_FILE_SIZE" value="2097152">
		<table class="postform">
			<tbody>
				<tr>
					<td class="postblock">
						Thread No.
					</td>
					<td>
						<input type="text" name="parent" size="28" maxlength="75" value="0" accesskey="t">&nbsp;(0 for new thread)
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
			</tbody>
		</table>
		</form>
	</div>
	<div style="text-align: center;">
		All text entered in the "Message" field will be posted AS-IS with absolutely no formatting applied.<br>This means for all line-breaks you must enter the text "&lt;br&gt;".
	</div>
	<br>
EOF;
}

function manageModeratePost($post) {
	global $isadmin;
	$ban = banByIP($post['ip']);
	$ban_disabled = (!$ban && $isadmin) ? '' : ' disabled';
	$ban_disabled_info = (!$ban) ? '' : (' A ban record already exists for ' . $post['ip']);
	$post_html = buildPost($post, true);
	return <<<EOF
	<fieldset>
	<legend>Moderating post No.${post['id']}</legend>
	
	<div class="floatpost">
	<fieldset>
	<legend>Post</legend>	
	$post_html
	</fieldset>
	</div>
	
	<fieldset>
	<legend>Action</legend>					
	<form method="get" action="?">
	<input type="hidden" name="manage" value="">
	<input type="hidden" name="delete" value="${post['id']}">
	<input type="submit" value="Delete Post" class="managebutton">
	</form>
	<br>
	<form method="get" action="?">
	<input type="hidden" name="manage" value="">
	<input type="hidden" name="bans" value="${post['ip']}">
	<input type="submit" value="Ban Poster" class="managebutton"$ban_disabled>$ban_disabled_info
	</form>
	</fieldset>
	
	</fieldset>
	<br>
EOF;
}
?>