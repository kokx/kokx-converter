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
     * Themes.
     *
     * @var array
     */
    protected $_themes = array(
        'kokx'         => 'kokx',
        'kokx-nolines' => 'kokx-nolines',
        'tsjerk'       => 'Albert Fish',
        'virus'        => 'ViRuS',
        'nexus'        => 'Nexus'
    );

    /**
     * The processed data
     *
     * @var array
     */
    protected $_data = array();


    /**
     * Get the available themes
     *
     * @return array
     */
    public function getThemes()
    {
        return $this->_themes;
    }

    /**
     * Read a CombatReport.
     *
     * @param array $data
     *
     * @return Default_Model_CombatReport
     */
    public function readReport(array $data, array $settings)
    {
        $data['report'] = utf8_encode($data['report']);

        $report = Default_Reader_Reader::readReport($data['report'], $settings);

        // harvest reports and raids
        if (isset($data['harvest_reports']) && !empty($data['harvest_reports']) && is_string($data['harvest_reports'])) {
            $report->setHarvestReports(Default_Reader_Reader::readHarvestReports($data['harvest_reports']));
        }
        if (isset($data['raids']) && !empty($data['raids']) && is_string($data['raids'])) {
            $report->setRaids(Default_Reader_Reader::readRaids($data['raids']));
        }

        $this->_data = $data;

        return $report;
    }

    /**
     * Get the renderer.
     *
     * @param array $settings
     *
     * @return Default_Renderer_Renderer
     */
    public function getRenderer(array $settings)
    {
        switch ($settings['theme']) {
            case 'virus':
                return new Default_Renderer_Virus($settings);
                break;
            case 'tsjerk':
                return new Default_Renderer_Tsjerk($settings);
                break;
            case 'nexus':
                return new Default_Renderer_Nexus($settings);
                break;
            case 'kokx-nolines':
                return new Default_Renderer_KokxNolines($settings);
                break;
            case 'kokx':
            default:
                return new Default_Renderer_Kokx($settings);
        }
    }

    /**
     * Get the default settings
     *
     * @return array
     */
    public function getDefaultSettings()
    {
        return array(
            'theme'        => 'kokx',
            'middle_text'  => Zend_Registry::get('Zend_Translate')->_("After the battle..."),
            'hide_time'    => true,
            'merge_fleets' => true
        );
    }

    /**
     * Get the data.
     *
     * @return array.
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Read the settings
     *
     * @param array $data
     *
     * @return array Settings
     */
    public function readSettings(array $data)
    {
        // set defaults
        $settings = $this->getDefaultSettings();

        if (isset($data['theme']) && isset($this->_themes[$data['theme']])) {
            $settings['theme'] = $data['theme'];
        }
        if (isset($data['middle_text']) && is_string($data['middle_text'])) {
            $settings['middle_text'] = htmlspecialchars($data['middle_text']);
        }
        if (isset($data['hide_time']) && ($data['theme'] != '1')) {
            $settings['hide_time'] = false;
        }
        if (isset($data['merge_fleets']) && ($data['merge_fleets'] != '1')) {
            $settings['merge_fleets'] = false;
        }

        return $settings;
    }
}
