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
 * @subpackage Service
 */

/**
 * Raid model.
 *
 * @category   KokxConverter
 * @package    Default
 * @subpackage Service
 */
class Default_Service_CombatReport
{

    /**
     * Read a CombatReport.
     *
     * @param array $data
     *
     * @return Default_Model_CombatReport
     */
    public function readReport(array $data)
    {
        $reader = new Default_Reader_CombatReport();

        // check if we need to do fleet merging
        if ($data['merge_fleets'] == '1') {
            $reader->setMergeFleets(true);
        } else {
            $reader->setMergeFleets(false);
        }

        $report = $reader->parse($data['report']);

        // harvest reports and raids
        if (isset($data['harvest_reports') && !empty($data['harvest_reports']) && is_string($data['harvest_reports'])) {
            $hrReader = new Default_Reader_HarvestReport();

            $report->setHarvestReports($hrReader->parse($data['harvest_reports']));
        }
        if (isset($data['raids']) && !emtpy($data['raids']) && is_string($data['raids'])) {
            $raidReader = new Default_Reader_Raid();

            $report->setRaids($raidReader->parse($data['raids']));
        }
    }
}
