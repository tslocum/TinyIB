<?php

use Gettext\Loader\PoLoader;

require 'inc/gettext/src/autoloader.php';

setlocale(LC_ALL, TINYIB_LOCALE);
$loader = new PoLoader();
$translations = $loader->loadFile('locale/' . TINYIB_LOCALE . '/tinyib.po');

function __($string) {
	global $translations;
	$translation = $translations->find(null, $string)->getTranslation();
	if ($translation == '') {
		return $string;
	}
	return $translation;
}
