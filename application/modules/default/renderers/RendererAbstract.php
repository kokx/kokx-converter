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
 * @subpackage Renderer
 */

/**
 * CR parser
 *
 * @category   KokxConverter
 * @package    Default
 * @subpackage Renderer
 */
abstract class Default_Renderer_RendererAbstract implements Default_Renderer_Renderer
{

    /**
     * The current CR.
     *
     * @var Default_Model_CombatReport
     */
    public $_report;

    /**
     * The default Zend_View instance
     *
     * @var Zend_View
     */
    public $_view;

    /**
     * The settings
     *
     * @var array
     */
    public $_settings;


    /**
     * Construct the renderer
     *
     * @param array $settings
     *
     * @return void
     *
     * @todo Read the settings in correctly
     */
    public function __construct(array $settings = array())
    {
        $this->_settings = $settings;
    }

    /**
     * Get the view prefix.
     *
     * @return string
     */
    abstract protected function _getViewBasePrefix();

    /**
     * Get the view path.
     *
     * @return string
     */
    abstract protected function _getViewBasePath();

    /**
     * Render a CR.
     *
     * @param Default_Model_CombatReport $report
     *
     * @return string
     */
    public function render(Default_Model_CombatReport $report)
    {
        $this->_report = $report;
        $this->getView()->report = $report;

        $return = $this->_renderTime();

        $return .= $this->_renderRounds();

        $return .= $this->_renderResult();

        return $return;
    }

    /**
     * Get the view
     *
     * @return Zend_View
     */
    public function getView()
    {
        if (null === $this->_view) {
            $this->_view = new Zend_View(array('strictVars' => true));

            $this->_view->setBasePath($this->_getViewBasePath(), $this->_getViewBasePrefix());

            $this->_view->renderer = $this;
        }

        return $this->_view;
    }

    /**
     * Render the time.
     *
     * @return string
     */
    public function _renderTime()
    {
        $this->getView()->hideTime = $this->_settings['hide_time'] ?: true;

        return $this->getView()->render('time.phtml');
    }

    /**
     * Render the rounds.
     *
     * @return string
     */
    public function _renderRounds()
    {
        $result = $this->_renderFirstRound();

        if (isset($this->_settings['middle_text'])) {
            $result .= $this->_settings['middle_text'];
        }

        $result .= $this->_renderLastRound();

        return $result;
    }

    /**
     * Render the first round.
     *
     * @return string
     */
    public function _renderFirstRound()
    {
        return $this->getView()->render('firstround.phtml');
    }

    /**
     * Render the last round.
     *
     * @return string
     */
    public function _renderLastRound()
    {
        return $this->getView()->render('lastround.phtml');
    }

    /**
     * Render the result of the battle.
     *
     * @return string
     */
    public function _renderResult()
    {
        return $this->_renderWinnerLoot()
             . $this->_renderLossesMoon()
             . $this->_renderDebris()
             . $this->_renderSummary();
    }

    /**
     * Render the winner and the loot
     *
     * @return string
     */
    public function _renderWinnerLoot()
    {
        return $this->getView()->render('winnerloot.phtml');
    }

    /**
     * Render the losses and moon creation.
     *
     * @return string
     */
    public function _renderLossesMoon()
    {
        return $this->getView()->render('lossesmoon.phtml');
    }

    /**
     * Render the debris.
     *
     * @return string
     */
    public function _renderDebris()
    {
        return $this->getView()->render('debris.phtml');
    }

    /**
     * Render the summary.
     *
     * @return string
     */
    public function _renderSummary()
    {
        return $this->getView()->render('summary.phtml');
    }
}
