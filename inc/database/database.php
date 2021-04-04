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
