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

// first parse the CR
$parser = new Kokx_Parser_CrashReport();

$parser->parse(file_get_contents(ROOT . DIRECTORY_SEPARATOR . 'agsreport'));

// use Zend View to render the CR
$view = new Zend_View();

$view->setScriptPath(ROOT . DIRECTORY_SEPARATOR . 'views');

// now set all the variables
$view->time   = $parser->getTime();
$view->rounds = $parser->getRounds();
$view->result = $parser->getResult();

$view->options = array(
    'middleText' => 'Powned!!',
    'showTime'   => false
);

echo '<textarea rows="15" cols="50">';
echo $view->render('default.phtml');
echo '</textarea>';

var_dump($parser);