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

/**
 * Autoload function
 *
 * @param string $class
 *
 * @return void
 */
function myAutoload($class)
{
    require_once LIB . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
}

spl_autoload_register('myAutoload');

$parser = new Kokx_Parser_CrashReport();

var_dump($parser->parse(file_get_contents(ROOT . DIRECTORY_SEPARATOR . 'agsreport')));