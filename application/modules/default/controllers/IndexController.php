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
 * @package    Default
 * @subpackage Controllers
 */

/**
 * Index controller
 *
 * @category   KokxConverter
 * @package    Default
 * @subpackage Controllers
 */
class IndexController extends Zend_Controller_Action
{

    /**
     * CR service
     *
     * @var Default_Service_CombatReport
     */
    protected $_crService;


    /**
     * Get the CR service
     *
     * @return Default_Service_CombatReport
     */
    protected function _getCrService()
    {
        if (null === $this->_crService) {
            $this->_crService = new Default_Service_CombatReport();
        }

        return $this->_crService;
    }

    /**
     * Index action.
     *
     * @return void
     */
    public function indexAction()
    {
        $crService = $this->_getCrService();

        $settings = $crService->getDefaultSettings();

        $this->view->data = array(
            'report'          => '',
            'raids'           => '',
            'harvest_reports' => '',
            'theme'           => 'kokx'
        );
        $this->view->error = false;
        $this->view->rendered = '';
        $this->view->title    = '';

        if ($this->getRequest()->isPost()) {
            try {
                $settings = $crService->readSettings($_POST);
                $report   = $crService->readReport($_POST, $settings);

                $this->view->report = $report;
                $this->view->data   = $crService->getData();

                $renderer = $crService->getRenderer($settings);

                $this->view->rendered = $renderer->render($report);
                $this->view->title    = $renderer->renderTitle($report);
            } catch (Exception $e) {
                $this->view->error = true;
            }
        }

        $this->view->settings = $settings;

        $this->view->strictVars(true);
    }
}
