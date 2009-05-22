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

// initialize the autoloader
require_once 'Zend/Loader/Autoloader.php';

$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('Kokx_');

$output = '';

// convert the CR if it is there
if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['report'])) {
    // first parse the CR
    $parser = new Kokx_Parser_CrashReport();

    $parser->parse($_POST['report']);

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

    $output = $view->render('default.phtml');
} else {
    $_POST['report'] = '';
}
?>
<form method="post" action="">
<textarea name="report" rows="15" cols="50"><?= $_POST['report'] ?></textarea>

<textarea rows="15" cols="50"><?= $output ?></textarea><br />
<input type="submit" name="submit" value="convert" />
</form>