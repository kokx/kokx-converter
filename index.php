<?php

/**
 * Root location
 */
define('ROOT', dirname(__FILE__));

/**
 * Library location
 */
define('LIB', ROOT . DIRECTORY_SEPARATOR . 'library');

ini_set('xdebug.var_display_max_depth', 20);

$includePath = array(
    LIB,
    get_include_path()
);

set_include_path(implode(PATH_SEPARATOR, $includePath));

require_once 'Zend/Loader/Autoloader.php';

$autoloader = Zend_Loader_Autoloader::getInstance();

$autoloader->registerNamespace('Kokx_');

$parser = new Kokx_Parser_CrashReport();

var_dump($parser->parse(file_get_contents(ROOT . DIRECTORY_SEPARATOR . 'agsreport')));