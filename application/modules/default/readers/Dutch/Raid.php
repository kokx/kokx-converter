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
 * @subpackage Readers
 */

/**
 * Raid parser
 *
 * @category   Kokx
 * @package    Default
 * @subpackage Readers_Dutch
 */
class Default_Reader_Dutch_Raid
{

    /**
     * Parse a raid report
     *
     * @param string $source
     *
     * @return array  of {@link Default_Reader_Raid}'s
     */
    public function parse($source)
    {
        $raids = array();
        /**
         * The source only has to contain something like:
         *
         * De aanvaller heeft het gevecht gewonnen! De aanvaller steelt 13.962 metaal, 4.463 kristal en 123.168 deuterium.
         *
         * De aanvaller heeft een totaal van 0 eenheden verloren.
         * De verdediger heeft een totaal van 11.056.000 eenheden verloren.
         * Op deze coördinaten in de ruimte zweven nu 2.407.800 metaal en 909.000 kristal.
         *
         *
         * [[ REDESIGN short version: ]]
         * Gevechtsrapport
         *
         * gevecht vond plaats op maan [x:xxx:x] (--.--.---- --:--:--)
         *
         * Wickedmen van Sinasappelsap [x:xx:x]
         * vs
         *
         * MNN van maan [x:xxx:x]
         *
         * schepen/verdediging: 	160 		schepen/verdediging: 	0
         * eenheden verloren: 	0 		eenheden verloren: 	0
         * Wapens: 	100% 		Wapens: 	110%
         * Schilden: 	80% 		Schilden: 	100%
         * Pantser: 	80% 		Pantser: 	120%
         * winnaar: Wickedmen
         * De aanvaller heeft het gevecht gewonnen!
         * buit : 	1.377.538 metaal, 1.074.657 kristal en 544.996 deuterium.
         * puinveld : 	0 metaal en 0 kristal.
         * gerepareerd : 	?
         * Gedetailleerd gevechtsrapport >> 
         */

        $regex = '([0-9.]*) metaal, ([0-9.]*) kristal en ([0-9.]*) deuterium';

        $matches = array();

        preg_match_all('/' . $regex . '/i', $source, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $raids[] = new Default_Model_Raid(
                (int) str_replace('.', '', $match[1]),
                (int) str_replace('.', '', $match[2]),
                (int) str_replace('.', '', $match[3]),
                0, 0 // temporarily, the attacker and defender losses will be 0
                     // until we implement good support for this
            );
        }


        return $raids;
    }
}
