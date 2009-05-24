<?php

define('VERSION', '1.0.0');

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

// initialize the autoloader
require_once 'Zend/Loader/Autoloader.php';

$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('Kokx_');

$view = new Zend_View();

$view->setScriptPath(ROOT . DIRECTORY_SEPARATOR . 'views');

$view->script = '';
$view->report = '';

// convert the CR if it is there
if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['report'])) {
    // first parse the CR
    $parser = new Kokx_Parser_CrashReport();

    try {
        $parser->parse($_POST['report']);
    } catch (Kokx_Parser_Exception $e) {
        $view->error = true;
        exit($view->render('layout.phtml'));
    }

    // use Zend View to render the shit

    // now set all the variables
    $view->time   = $parser->getTime();
    $view->rounds = $parser->getRounds();
    $view->result = $parser->getResult();

    // default options
    $view->options = array(
        'middleText' => 'Na het gevecht...',
        'hideTime'   => true
    );
    
    // check for options
    if (isset($_POST['middletext']) && is_string($_POST['middletext'])) {
        $view->options['middleText'] = $_POST['middletext'];
    }
    if (isset($_POST['hidetime'])) {
        if ($_POST['hidetime'] == '1') {
            $view->options['hideTime'] = true;
        } else {
            $view->options['hideTime'] = false;
        }
    }

    $view->script = 'report/default.phtml';
    $view->report = $_POST['report'];
}
echo $view->render('layout.phtml');