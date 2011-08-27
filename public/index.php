<?php
/**
 * This file is part of Kokx's CR Converter.
 *
 * Kokx's CR Converter is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Kokx's CR Converter is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Kokx's CR Converter.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   KokxConverter
 * @license    http://www.gnu.org/licenses/gpl.txt
 * @copyright  Copyright (c) 2009 Kokx
 * @package    Index
 */

define('VERSION', '2.0.0');
define('LINK', 'http://converter.kokx.nl/');

define('GOOGLE_AD_CLIENT', 'pub-2117246455436779');
define('GOOGLE_AD_SLOT', '0492312042');
define('GOOGLE_GA_TRACKER_ID', 'UA-9087312-1');

/**
 * Public location
 */
define('PUB', dirname(__FILE__));
/**
 * Root location
 */
define('ROOT', realpath(PUB . "/../"));
/**
 * Library location
 */
define('LIB', ROOT . DIRECTORY_SEPARATOR . 'library');
/**
 * ZF library location.
 */
define('LIB_ZF', LIB . DIRECTORY_SEPARATOR . 'zend');
/**
 * Kokx library location.
 */
define('LIB_KOKX', LIB . DIRECTORY_SEPARATOR . 'kokx');
/**
 * Application location.
 */
define('APP', ROOT . DIRECTORY_SEPARATOR . 'application');
/**
 * Modules location.
 */
define('MODULES', APP . DIRECTORY_SEPARATOR . 'modules');
/**
 * Data location.
 */
define('DATA', ROOT . DIRECTORY_SEPARATOR . 'data');
/**
 * Translate location.
 */
define('TRANSLATE', DATA . DIRECTORY_SEPARATOR . 'translate');

ini_set('xdebug.var_display_max_depth', 20);

$includePath = array(
    LIB_KOKX,
    LIB_ZF,
    get_include_path()
);

set_include_path(implode(PATH_SEPARATOR, $includePath));

// initialize the autoloader
require_once 'Zend/Loader/Autoloader.php';

$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('Kokx_');

// resource loader
$defaultLoader = new Kokx_Application_ResourceLoader(array(
    'basePath'  => MODULES . DIRECTORY_SEPARATOR . 'default',
    'namespace' => 'Default'
));

// translation config
$translate = new Zend_Translate(array(
    'adapter' => 'gettext',
    'content' => TRANSLATE,
    'locale'  => 'auto',
    'scan'    => Zend_Translate::LOCALE_FILENAME
));

Zend_Registry::set('Zend_Translate', $translate);

/**
 * Order for determining the locale:
 * 
 * 1. If there is a $_GET['lang'] parameter, use that locale, also sets the session
 * 2. If there is a session setting, use that locale
 * 3. If the browser has setting, use that locale
 * 4. Fall back to en
 */
$session = new Zend_Session_Namespace('lang');

if (isset($_GET['lang']) && $translate->isAvailable($_GET['lang'])) {
    $translate->setLocale($_GET['lang']);
    $session->lang = $_GET['lang'];
}
if (isset($session->lang) && $translate->isAvailable($session->lang)) {
    $translate->setLocale($session->lang);
}

// front controller
$front = Zend_Controller_Front::getInstance();

$front->throwExceptions(true);
$front->addModuleDirectory(MODULES);

// layout config
$layout = Zend_Layout::startMvc();
$layout->setLayoutPath(APP . DIRECTORY_SEPARATOR . 'layouts');
$layout->setLayout('default');

$front->dispatch();

/*
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

    $mergeFleets = false;
    if ($_POST['merge_fleets'] == '1') {
        $mergeFleets = true;
    }

    try {
        $parser->parse($_POST['report'], $mergeFleets);
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
     *

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
     *

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
    $view->options['mergeFleets'] = $mergeFleets;
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
    if (isset($_POST['theme']) && in_array($_POST['theme'], array('kokx', 'kokx-nolines', 'tsjerk', 'virus'))) {
        $view->theme = $_POST['theme'];
    } else {
        $view->theme = 'kokx';
    }

    $view->report = $_POST['report'];
}
echo $view->render('layout.phtml');
 */
