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
 * HR parser
 *
 * @category   Kokx
 * @package    Default
 * @subpackage Readers
 */
class Default_Reader_HarvestReport
{

    /**
     * Parse a harvest report
     *
     * @param string $source
     *
     * @return array  of {@link Default_Model_HarvestReport}'s
     */
    public function parse($source)
    {
        $reports = array();

        /**
         * Example report:
         *
         * Je 8001 recyclers hebben een totale opslagcapaciteit van 101.264.259.
         * In het bestemmingsveld zweven 0 metaal en 0 kristal in de ruimte.
         * Je hebt 0 metaal en 0 kristal opgehaald.
         */
        $regex  = 'Je ([0-9.]*?) recyclers hebben een totale opslagcapaciteit van ([0-9.]*?). ';
        $regex .= 'In het bestemmingsveld zweven ([0-9.]*?) metaal en ([0-9.]*?) kristal in de ruimte. ';
        $regex .= 'Je hebt ([0-9.]*?) metaal en ([0-9.]*?) kristal opgehaald.';

        $matches = array();

        preg_match_all('/' . $regex . '/i', $source, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $reports[] = new Default_Model_HarvestReport(
                (int) str_replace('.', '', $match[1]),
                (int) str_replace('.', '', $match[2]),
                (int) str_replace('.', '', $match[3]),
                (int) str_replace('.', '', $match[4]),
                (int) str_replace('.', '', $match[5]),
                (int) str_replace('.', '', $match[6])
            );
        }

        return $reports;
    }
}
