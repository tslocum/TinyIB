<?php

if (TINYIB_ADMINPASS != '') {
	$admin = accountByUsername('admin');
	if (!empty($admin)) {
		$admin['password'] = TINYIB_ADMINPASS;
		updateAccount($admin);
	} else {
		$admin = array('username' => 'admin', 'password' => TINYIB_ADMINPASS, 'role' => TINYIB_SUPER_ADMINISTRATOR);
		insertAccount($admin);
	}
}

if (TINYIB_MODPASS != '') {
	$mod = accountByUsername('mod');
	if (!empty($mod)) {
		$mod['password'] = TINYIB_MODPASS;
		updateAccount($mod);
	} else {
		$mod = array('username' => 'mod', 'password' => TINYIB_MODPASS, 'role' => TINYIB_MODERATOR);
		insertAccount($mod);
	}
}

$cache_all = array();
$cache_moderated = array();
function postsInThreadByID($id, $moderated_only = true) {
	global $cache_all, $cache_moderated;

	if ($moderated_only) {
		$cache = &$cache_moderated;
	} else {
		$cache = &$cache_all;
	}

	$id = intval($id);
	if (!isset($cache[$id])) {
		$cache[$id] = _postsInThreadByID($id, $moderated_only);
	}
	return $cache[$id];
}

function clearPostCache() {
	global $cache_all, $cache_moderated;
	$cache_all = array();
	$cache_moderated = array();
}
