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
 * @subpackage Readers
 */
class Default_Reader_Raid
{

    /**
     * The battle's result
     *
     * @var array
     */
    protected $_raids = array();


    /**
     * Get the battle result
     *
     * @return array
     */
    public function getRaids()
    {
        return $this->_raids;
    }

    /**
     * Parse a crash report
     *
     * @param string $source
     *
     * @return Kokx_Parser_Raid
     */
    public function parse($source)
    {
        // the source could have multiple CR's
        /**
         * The source only has to contain something like:
         *
         * 99.552 Metaal, 3.748 Kristal en 17.333 Deuterium
         */

        $regex = '([0-9.]*) Metaal, ([0-9.]*) Kristal en ([0-9.]*) Deuterium';

        $matches = array();

        preg_match_all('/' . $regex . '/i', $source, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $this->_raids[] = array(
                'metal'   => (int) str_replace('.', '', $match[1]),
                'crystal' => (int) str_replace('.', '', $match[2]),
                'deut'    => (int) str_replace('.', '', $match[3])
            );
        }


        return $this;
    }
}
