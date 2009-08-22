<?php

define('VERSION', '1.2.2-dev');

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

// view initialization
$view = new Zend_View();

$view->setScriptPath(ROOT . DIRECTORY_SEPARATOR . 'views');

$view->theme  = 'kokx';
$view->report = '';
$view->title  = '';
$view->raids  = array();

// default options
$view->options = array(
    'middleText'  => 'Na het gevecht...',
    'hideTime'    => true,
    'mergeFleets' => true
);

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

    // we are using Zend_View to render the shit

    // now set all the variables
    $view->time   = $parser->getTime();
    $view->rounds = $parser->getRounds();
    $view->result = $parser->getResult();

    /**
     * Generate the title
     */

    // attackers first
    $attackers = array();

    foreach ($view->rounds[0]['attackers'] as $attacker) {
        $attackers[] = $attacker['player']['name'];
    }

    $attackers = array_keys(array_flip($attackers));

    // now defenders
    $defenders = array();

    foreach ($view->rounds[0]['defenders'] as $defender) {
        $defenders[] = $defender['player']['name'];
    }

    $defenders = array_keys(array_flip($defenders));

    // will be something like:
    // ViRuS & kokx vs. rasta & joop (A: 55.000.000, V: 88.000.000) [TOT: 20.000.000]

    $view->result['totallosses'] = $view->result['attackerlosses'] + $view->result['defenderlosses'];

    $title  = implode(' & ', $attackers) . ' vs. ' . implode(' & ', $defenders);
    $title .= ' (A: ' . number_format($view->result['attackerlosses'], 0, ',', '.');
    $title .= ', V: ' . number_format($view->result['defenderlosses'], 0, ',', '.') . ')';
    $title .= ' [TOT: ' . number_format($view->result['totallosses'], 0, ',', '.') . ']';

    $view->title = $title;


    /**
     * Check and use the options
     */

    // just simple options
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
    if (isset($_POST['merge_fleets'])) {
        if ($_POST['merge_fleets'] == '1') {
            $view->options['mergeFleets'] = true;
        } else {
            $view->options['mergeFleets'] = false;
        }
    }
    // after raids option
    if (isset($_POST['after_raids']) && is_string($_POST['after_raids'])) {
        $view->afterRaids = $_POST['after_raids'];

        $raidsParser = new Kokx_Parser_Raid();

        $view->raids = $raidsParser->parse($_POST['after_raids'])->getRaids();
    }
    // debris reports option
    if (isset($_POST['debris_reports']) && is_string($_POST['debris_reports'])) {
        $view->debrisReports = $_POST['debris_reports'];

        $debrisParser = new Kokx_Parser_Debris();

        $view->debris = $debrisParser->parse($_POST['debris_reports'])->getHarvest();
    }
    // skin
    if (isset($_POST['theme']) && in_array($_POST['theme'], array('kokx', 'kokx-nolines', 'tsjerk'))) {
        $view->theme = $_POST['theme'];
    } else {
        $view->theme = 'kokx';
    }

    $view->report = $_POST['report'];
}
echo $view->render('layout.phtml');