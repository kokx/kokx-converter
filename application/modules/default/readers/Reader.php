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
class Default_Reader_Reader
{

    /**
     * Constructor, private to disable instantiation.
     *
     * @return void
     */
    private function __construct()
    {}

    /**
     * Read a CR.
     *
     * @param string $source
     * @param array  $settings
     *
     * @return Default_Model_CombatReport
     *
     * @throws Exception when the CR cannot be read
     */
    public static function readReport($source, array $settings = array())
    {
        if (stripos($source, 'De volgende vloten kwamen elkaar tegen op') !== false) {
            // use the dutch reader
            $reader = new Default_Reader_Dutch_CombatReport();
        } else if (stripos($source, 'the following fleets met in battle') !== false) {
            // use the english reader
            $reader = new Default_Reader_English_CombatReport();
        } else {
            throw new Exception("Invalid CR");
        }

        if (isset($settings['merge_fleets'])) {
            $reader->setMergeFleets($settings['merge_fleets']);
        }

        return $reader->parse($source);
    }

    /**
     * Read harvest reports
     *
     * @param string $source
     *
     * @return array  of {@link Default_Model_HarvestReport}'s
     */
    public static function readHarvestReports($source)
    {
        if (stripos($source, 'recyclers hebben een totale opslagcapaciteit van') !== false) {
            // use the dutch reader
            $reader = new Default_Reader_Dutch_HarvestReport();
        } else if (stripos($source, 'recycler(s) have a total cargo capacity of') !== false) {
            // use the english reader
            $reader = new Default_Reader_English_HarvestReport();
        } else {
            throw new Exception("Invalid HR");
        }

        return $reader->parse($source);
    }

    /**
     * Read raid reports
     *
     * @param string $source
     *
     * @return array  of {@link Default_Model_Raid}'s
     */
    public static function readRaids($source)
    {
        if (preg_match("/([0-9.]*) metaal, ([0-9.]*) kristal en ([0-9.]*) deuterium/i", $source)) {
            // use the dutch reader
            $reader = new Default_Reader_Dutch_Raid();
        } else if (preg_match("/([0-9.]*) metal, ([0-9.]*) crystal and ([0-9.]*) deuterium/i", $source)) {
            // use the english reader
            $reader = new Default_Reader_English_Raid();
        } else {
            throw new Exception("Invalid Raid");
        }

        return $reader->parse($source);
    }
}
