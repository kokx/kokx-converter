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
 * @subpackage Models
 */

/**
 * Ship model.
 *
 * @category   KokxConverter
 * @package    Default
 * @subpackage Models
 */
class Default_Model_Ship
{

    /**
     * The name of the ship
     *
     * @var string
     */
    protected $_name;

    /**
     * Number of ships
     * 
     * @var int
     */
    protected $_count;

    // ship types
    const SMALL_CARGO     = 'Small Cargo';
    const LARGE_CARGO     = 'Large Cargo';
    const LIGHT_FIGTHER   = 'Light Fighter';
    const HEAVY_FIGHTER   = 'Heavy Fighter';
    const CRUISER         = 'Cruiser';
    const BATTLESHIP      = 'Battleship';
    const COLONY_SHIP     = 'Colony Ship';
    const RECYCLER        = 'Recycler';
    const ESPIONAGE_PROBE = 'Espionage Probe';
    const BOMBER          = 'Bomber';
    const SOLAR_SATTELITE = 'Solar Sattelite';
    const DESTROYER       = 'Destroyer';
    const DEATHSTAR       = 'Deathstar';
    const BATTLECRUISER   = 'Battlecruiser';

    // defense types
    const ROCKET_LAUNCHER   = 'Rocket Launcher';
    const LIGHT_LASER       = 'Light Laser';
    const HEAVY_LASER       = 'Heavy Laser';
    const GAUSS_CANNON      = 'Gauss Cannon';
    const ION_CANNON        = 'Ion Cannon';
    const PLASMA_TURRET     = 'Plasma Turret';
    const SMALL_SHIELD_DOME = 'Small Shield Dome';
    const LARGE_SHIELD_DOME = 'Large Shield Dome';



    /**
     * Constructor
     *
     * @param string $name
     * @param int $count
     *
     * @return void
     */
    public function __construct($name, $count)
    {
        $this->_name  = $name;
        $this->_count = $count;
    }

    /**
     * Get the name of the ship.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get the number of ships
     *
     * @return int
     */
    public function getCount()
    {
        return $this->_count;
    }

    /**
     * Add a number of ships to the count.
     *
     * @param int $count  Count to add
     *
     * @return Default_Model_Ship
     */
    public function addCount($count)
    {
        $this->_count += $count;
    }
}
