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
 * Combat Report model
 *
 * @package    Default
 * @subpackage Models
 */
class Default_CombatReport
{

    // references to other classes
    /**
     * Array of {@link Default_HarvestReport}, for the debris field.
     *
     * @var array
     */
    protected $_hrs;

    /**
     * Array of {@link Default_Raid}, for raids after the target has been
     * crushed.
     *
     * @var array
     */
    protected $_raids;

    /**
     * Array of {@link Default_CombatRound}, for founds of the combat.
     *
     * @var array
     */
    protected $_rounds;

    // properties of a CR
    
    // loot
    /**
     * Amount of stolen metal.
     *
     * @var int
     */
    protected $_metal;

    /**
     * Amount of stolen crystal
     *
     * @var int
     */
    protected $_crystal;

    /**
     * Amount of stolen deuterium
     *
     * @var int
     */
    protected $_deuterium;

    // debris field
    /**
     * Amount of metal in the debris field.
     *
     * @var int
     */
    protected $_debrisMetal;

    /**
     * Amount of crystal in the debris field.
     *
     * @var int
     */
    protected $_debrisCrystal;

    // losses
    /**
     * Number of losses the attackers made.
     *
     * @var int
     */
    protected $_lossesAttacker;

    /**
     * Number of losses the defenders made.
     *
     * @var int
     */
    protected $_lossesDefender;

    /**
     * Time that it happened
     *
     * @var DateTime
     */
    protected $_time;

    // other things that could happen
    /**
     * Moon given.
     *
     * @var boolean
     */
    protected $_moonGiven = false;
}
