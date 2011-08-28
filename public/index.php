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
    'content' => TRANSLATE . DIRECTORY_SEPARATOR . 'en.mo',
    'locale'  => 'en'
));
$translate->addTranslation(array(
    'adapter' => 'gettext',
    'content' => TRANSLATE . DIRECTORY_SEPARATOR . 'nl.mo',
    'locale'  => 'nl'
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
