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
class Default_Renderer_Kokx extends Default_Renderer_RendererAbstract
{

    /**
     * Total resources from the raids
     *
     * @var int
     */
    protected $_totalRaids;

    /**
     * Get the view path.
     *
     * @return string
     */
    public function _getViewScriptPath()
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'kokx';
    }

    /**
     * Render the time.
     *
     * @return string
     */
    public function _renderTime()
    {
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
