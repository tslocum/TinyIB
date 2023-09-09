<?php

use Gettext\Translator;
use Gettext\Translations;

setlocale(LC_ALL, TINYIB_LOCALE);

require 'inc/gettext/src/autoloader.php';

$translations = Translations::fromPoFile('locale/' . TINYIB_LOCALE . '/tinyib.po');
$translator = new Translator();
$translator->loadTranslations($translations);
$translator->register();
