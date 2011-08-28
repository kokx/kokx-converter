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
 * Ship formatter.
 *
 * @category   KokxConverter
 * @package    Default
 * @subpackage Renderer
 */
class Default_Renderer_Virus_Helper_FormatShip extends Zend_View_Helper_Abstract
{

    /**
     * Format a ship display
     *
     * @param bool $attacker
     * @param Default_Model_Ship $ship
     * $param Default_Model_Ship $oldShip  Optional, only for comparison
     *
     * @return string
     */
    public function formatShip($attacker, Default_Model_Ship $ship, Default_Model_Fleet $fleet = null)
    {
        if (null === $fleet) {
            return "[color=white]{$this->view->translate($ship->getName())} {$this->view->formatNumber($ship->getCount())}[/color]\n";
        } else {
            /*
             * Note that in this case, the $ship is the old ship, and $fleet
             * contains the new fleet.
             */
            $newCount = ($fleet->getShip($ship->getName()) != null) ? $fleet->getShip($ship->getName())->getCount() : 0;
            return "[color=white]{$this->view->translate($ship->getName())} {$newCount} "
                 . "[b]( -{$this->view->formatNumber($ship->getCount() - $newCount)} )[/b][/color]\n";
        }
    }
}
