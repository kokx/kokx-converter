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
abstract class Default_Renderer_RendererAbstract
{

    /**
     * The current CR.
     *
     * @var Default_Model_CombatReport
     */
    public $_report;

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

        $return = $this->_renderTime();

        $return .= $this->_renderRounds();

        $return .= $this->_renderResult();

        return $return;
    }

    /**
     * Render the time.
     *
     * @return string
     */
    abstract public function _renderTime();

    /**
     * Render the rounds.
     *
     * @return string
     */
    abstract public function _renderRounds();

    /**
     * Render the result of the battle.
     *
     * @return string
     */
    abstract public function _renderResult();
}
