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
 * @subpackage Readers_English
 */
class Default_Reader_English_Raid
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
         * The attacker has won the battle! He captured 180.341 metal, 117.390 crystal and 41.033 deuterium. 
         * 
         * The attacker lost a total of 0 units.
         * The defender lost a total of 0 units.
         * At these space coordinates now float 0 metal and 0 crystal.
         *
         *
         * [[ REDESIGN short version: ]]
         * Combat Report
         * 
         * Combat at Flehm [1:227:5] (27.08.2011 15:08:23) 
         * 
         * kokx from Yatas [1:206:7]
         * vs
         * 
         * Wilgold from Flehm [1:227:5]
         * Ships/Defence: 	 80 		 Ships/Defence: 	 0 
         * Lost units: 	 0 		 Lost units: 	 0 
         * Weapons: 	 110% 		 Weapons: 	 40% 
         * Shields: 	 110% 		 Shields: 	 60% 
         * Armour: 	 110% 		 Armour: 	 40% 
         * Winner: kokx 
         * The attacker has won the battle! 
         * Loot : 	 180.341 Metal, 117.390 Crystal and 41.033 Deuterium. 
         * debris field : 	 0 metal and 0 crystal. 
         * Repaired : 	 ? 
         * 
         * Detailed combat report >>
         */

        $regex = '([0-9.]*) metal, ([0-9.]*) crystal and ([0-9.]*) deuterium';

        $matches = array();

        preg_match_all('/' . $regex . '/i', $source, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            // temporarily, the attacker and defender losses will be 0
            // until we implement good support for this
            $raids[] = new Default_Model_Raid(
                (int) str_replace('.', '', $match[1]),
                (int) str_replace('.', '', $match[2]),
                (int) str_replace('.', '', $match[3]),
                0,
                0
            );
        }


        return $raids;
    }
}
