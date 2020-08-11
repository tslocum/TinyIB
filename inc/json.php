<?php

function buildThreadsJSON() {
	$output['threads'] = array();

	$threads = allThreads();
	//$pages may be useful later, dismiss it for now
	//$pages = ceil(count($threads) / TINYIB_THREADSPERPAGE) - 1;

	foreach ($threads as $thread) {
		array_push($output['threads'], array('id' => $thread['id'], 'subject' => $thread['subject'], 'bumped' => $thread['bumped']));
	}

	$threads_json[] = $output;
	if (version_compare(phpversion(), '5.4.0', '>')) {
		return json_encode($threads_json, JSON_PRETTY_PRINT);
	} else {
		return json_encode($threads_json);
	}
}

function buildCatalogJSON() {
	$output['threads'] = array();

	$threads = allThreads();

	foreach ($threads as $thread) {
		$replies = postsInThreadByID($thread['id']);
		$images = imagesInThreadByID($thread['id']);

		if($thread['name'] == '') {
			array_push($output['threads'], array('id' => $thread['id'], 'parent' => $thread['parent'], 'timestamp' => $thread['timestamp'], 'bumped' => $thread['bumped'], 'name' => 'Anonymous', 'tripcode' => $thread['tripcode'], 'subject' => $thread['subject'], 'message' => $thread['message'], 'file' => $thread['file'], 'file_hex' => $thread['file_hex'], 'file_original' => $thread['file_original'], 'file_size' => $thread['file_size'], 'file_size_formated' => $thread['file_size_formatted'], 'image_width' => $thread['image_width'], 'image_height' => $thread['image_height'], 'thumb' => $thread['thumb'], 'thumb_width' => $thread['thumb_width'], 'thumb_height' => $thread['thumb_height'], 'stickied' => $thread['stickied'], 'moderated' => $thread['moderated'], 'locked' => $thread['locked'], 'replies' => count($replies) - 1, 'images' => $images));
		} else {
			array_push($output['threads'], array('id' => $thread['id'], 'parent' => $thread['parent'], 'timestamp' => $thread['timestamp'], 'bumped' => $thread['bumped'], 'name' => $thread['name'], 'tripcode' => $thread['tripcode'], 'subject' => $thread['subject'], 'message' => $thread['message'], 'file' => $thread['file'], 'file_hex' => $thread['file_hex'], 'file_original' => $thread['file_original'], 'file_size' => $thread['file_size'], 'file_size_formated' => $thread['file_size_formatted'], 'image_width' => $thread['image_width'], 'image_height' => $thread['image_height'], 'thumb' => $thread['thumb'], 'thumb_width' => $thread['thumb_width'], 'thumb_height' => $thread['thumb_height'], 'stickied' => $thread['stickied'], 'moderated' => $thread['moderated'], 'locked' => $thread['locked'], 'replies' => count($replies) - 1, 'images' => $images));
		}
	}

	$threads_json[] = $output;
	if (version_compare(phpversion(), '5.4.0', '>')) {
		return json_encode($threads_json, JSON_PRETTY_PRINT);
	} else {
		return json_encode($threads_json);
	}
}

function buildThreadNoJSON($id) {
	$output = array();
	
	$threads = allThreads();
	
	foreach ($threads as $thread) {
		$replies = postsInThreadByID($id);
		if($thread['parent'] == 0 && $thread['id'] == $id) {
			if($thread['name'] == '') {
				$output = array('posts' => [array('id' => $thread['id'], 'parent' => $thread['parent'], 'timestamp' => $thread['timestamp'], 'bumped' => $thread['bumped'], 'name' => 'Anonymous', 'tripcode' => $thread['tripcode'], 'subject' => $thread['subject'], 'message' => $thread['message'], 'file' => $thread['file'], 'file_hex' => $thread['file_hex'], 'file_original' => $thread['file_original'], 'file_size' => $thread['file_size'], 'file_size_formated' => $thread['file_size_formatted'], 'image_width' => $thread['image_width'], 'image_height' => $thread['image_height'], 'thumb' => $thread['thumb'], 'thumb_width' => $thread['thumb_width'], 'thumb_height' => $thread['thumb_height'], 'stickied' => $thread['stickied'], 'moderated' => $thread['moderated'], 'locked' => $thread['locked'])]);
			} else {
				$output = array('posts' => [array('id' => $thread['id'], 'parent' => $thread['parent'], 'timestamp' => $thread['timestamp'], 'bumped' => $thread['bumped'], 'name' => $thread['name'], 'tripcode' => $thread['tripcode'], 'subject' => $thread['subject'], 'message' => $thread['message'], 'file' => $thread['file'], 'file_hex' => $thread['file_hex'], 'file_original' => $thread['file_original'], 'file_size' => $thread['file_size'], 'file_size_formated' => $thread['file_size_formatted'], 'image_width' => $thread['image_width'], 'image_height' => $thread['image_height'], 'thumb' => $thread['thumb'], 'thumb_width' => $thread['thumb_width'], 'thumb_height' => $thread['thumb_height'], 'stickied' => $thread['stickied'], 'moderated' => $thread['moderated'], 'locked' => $thread['locked'])]);
			}
		}
	}
	foreach ($replies as $reply) {
		if($reply['parent'] == $id) {
			if($thread['name'] == '') {
				array_push($output['posts'], array('id' => $reply['id'], 'parent' => $reply['parent'], 'timestamp' => $reply['timestamp'], 'bumped' => $reply['bumped'], 'name' => 'Anonymous', 'tripcode' => $reply['tripcode'], 'subject' => $reply['subject'], 'message' => $reply['message'], 'file' => $reply['file'], 'file_hex' => $reply['file_hex'], 'file_original' => $reply['file_original'], 'file_size' => $reply['file_size'], 'file_size_formated' => $reply['file_size_formatted'], 'image_width' => $reply['image_width'], 'image_height' => $reply['image_height'], 'thumb' => $reply['thumb'], 'thumb_width' => $reply['thumb_width'], 'thumb_height' => $reply['thumb_height'], 'moderated' => $reply['moderated']));
			} else {
				array_push($output['posts'], array('id' => $reply['id'], 'parent' => $reply['parent'], 'timestamp' => $reply['timestamp'], 'bumped' => $reply['bumped'], 'name' => $reply['name'], 'tripcode' => $reply['tripcode'], 'subject' => $reply['subject'], 'message' => $reply['message'], 'file' => $reply['file'], 'file_hex' => $reply['file_hex'], 'file_original' => $reply['file_original'], 'file_size' => $reply['file_size'], 'file_size_formated' => $reply['file_size_formatted'], 'image_width' => $reply['image_width'], 'image_height' => $reply['image_height'], 'thumb' => $reply['thumb'], 'thumb_width' => $reply['thumb_width'], 'thumb_height' => $reply['thumb_height'], 'moderated' => $reply['moderated']));
			}
		}
	}

	if (version_compare(phpversion(), '5.4.0', '>')) {
		return json_encode($output, JSON_PRETTY_PRINT);
	} else {
		return json_encode($output);
	}
}