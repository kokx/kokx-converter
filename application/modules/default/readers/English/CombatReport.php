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
 * CR parser
 *
 * @category   KokxConverter
 * @package    Default
 * @subpackage Readers_English
 */
class Default_Reader_English_CombatReport
{

    /**
     * Source
     *
     * @var string
     */
    protected $_source = '';

    /**
     * Merge fleets or not.
     *
     * @var boolean
     */
    protected $_mergeFleets = true;

    /**
     * The resulting report.
     *
     * @var Default_Model_CombatReport
     */
    protected $_report;


    /**
     * Set if we have to merge fleets or not.
     *
     * @param boolean $mergeFleets
     *
     * @return void
     */
    public function setMergeFleets($mergeFleets)
    {
        $this->_mergeFleets = $mergeFleets;
    }

    /**
     * Check if we have to merge fleets.
     * 
     * @return boolean
     */
    public function getMergeFleets()
    {
        return $this->_mergeFleets;
    }

    /**
     * Parse a crash report
     *
     * @param string $source
     * @param boolean $mergeFleets
     *
     * @return Default_Model_CombatReport
     */
    public function parse($source)
    {
        $this->_source = stristr($source, 'On');

        // check the CR
        if (false === $this->_source) {
            throw new Exception('Bad CR');
        }

        $this->_report = new Default_Model_CombatReport();

        $matches = array();

        if (preg_match('#^On \(([0-9]{2}).([0-9]{2}).([0-9]{4}) ([0-9]{2}):([0-9]{2}):([0-9]{2})\) the following fleets met in battle:#i', $this->_source, $matches)) {
            $this->_report->setTime(new DateTime($matches[3] . ":" . $matches[2] . ":" . $matches[1] . " " . $matches[4] . ":" . $matches[5] . ":" . $matches[6]));
        } else {
            throw new Exception('Bad CR');
        }

        $this->_source = substr($this->_source, strlen($matches[0]));

        while (preg_match('#(Attacker|Defender) (.*) \[([0-9]:[0-9]{1,3}:[0-9]{1,2})\]#i', $this->_source)) {
            $this->_report->addRound($this->_parseRound());
        }

        $this->_parseResult();

        // check if we should merge multiple fleets of the same attacker or defender into one
        if ($this->getMergeFleets()) {
            $this->_mergeFleets();
        }

        return $this->_report;
    }

    /**
     * Normalize the name of a ship.
     */
    public function normalizeShipName($ship)
    {
        return ucwords(strtolower($ship));
    }

    /**
     * Parse a redesign round
     *
     * @return array
     */
    protected function _parseRound()
    {
        $round = new Default_Model_CombatRound();

        // first find the first attacker
        $this->_source = stristr($this->_source, 'Attacker');

        /*
         * Aanvaller Touch [2:193:9] Wapens: 110% Schilden: 90% Pantser: 110%
         * Soort 	L. Gevechtsschip 	Kruiser 	Slagschip 	Interceptor.
         * Aantal 	6.531 	1.139 	457 	315
         * Wapens: 	105 	840 	2.100 	1.470
         * Schilden 	19 	95 	380 	760
         * Romp 	840 	5.670 	12.600 	14.700
         *
         *
         * Verdediger Rambi vernietigd.
         */

        // complicated regex that extracts all info from a fleet slot
        $regex = '.*?(Attacker|Defender) ([^\n\r]*?)(\s*?\[([0-9]:[0-9]{1,3}:[0-9]{1,2})\])?'
               . '(\s*?Weapons: ([0-9]{0,2})0% Shields: ([0-9]{0,2})0% Armour: ([0-9]{0,2})0%)?\s*'
               . '(Type([A-Za-z.-\s]*)\s*' . 'Total([0-9.\s]*).*?Weapons' . '|destroyed.)\s*'
               . '.*?(?=Attacker|Defender)';

        $foundDefender = false;

        $matches = array();
        // loop trough the text until we have found all fleets in the round
        while (preg_match('#' . $regex . '#si', $this->_source, $matches)) {
            // extract the info
            $fleet = new Default_Model_Fleet();

            $fleet->setPlayer($matches[2]);

            if ($matches[9] != 'destroyed.') {
                $matches[10] = str_replace(array("\n", "\r", "  "), "\t", $matches[10]);
                $matches[11] = str_replace(array("\n", "\r", "  "), "\t", $matches[11]);

                // add the ships info
                $ships   = explode("\t", trim($matches[10]));
                $numbers = explode("\t", trim($matches[11]));

                foreach ($ships as $key => $ship) {
                    $fleet->addShip($this->_createShip($ship, $this->_convertToInt($numbers[$key])));
                }
            }

            // check if it is an attacker or a defender
            if (strtolower($matches[1]) == 'attacker') {
                if ($foundDefender) {
                    break;
                }

                $round->addAttackingFleet($fleet);
            } else {
                $round->addDefendingFleet($fleet);

                $foundDefender = true;
            }

            $this->_source = substr($this->_source, strlen($matches[0]));

            // always reset this array at the end
            $matches = array();
        }

        return $round;
    }

    /**
     * Create a ship
     *
     * @param string $name
     * @param int $count
     *
     * @return Default_Model_Ship
     */
    protected function _createShip($name, $count)
    {
        $name = trim(str_replace(' ', '', strtolower($name)));

        switch ($name) {
            // ships
            case 's.cargo':
                $name = Default_Model_Ship::SMALL_CARGO;
                break;
            case 'l.cargo':
                $name = Default_Model_Ship::LARGE_CARGO;
                break;
            case 'l.fighter':
                $name = Default_Model_Ship::LIGHT_FIGTHER;
                break;
            case 'h.fighter':
                $name = Default_Model_Ship::HEAVY_LASER;
                break;
            case 'cruiser':
                $name = Default_Model_Ship::CRUISER;
                break;
            case 'battleship':
                $name = Default_Model_Ship::BATTLESHIP;
                break;
            case 'col.ship':
                $name = Default_Model_Ship::COLONY_SHIP;
                break;
            case 'recy.':
                $name = Default_Model_Ship::RECYCLER;
                break;
            case 'esp.probe':
                $name = Default_Model_Ship::ESPIONAGE_PROBE;
                break;
            case 'bomber':
                $name = Default_Model_Ship::BOMBER;
                break;
            case 'sol.sat':
                $name = Default_Model_Ship::SOLAR_SATTELITE;
                break;
            case 'dest.':
                $name = Default_Model_Ship::DESTROYER;
                break;
            case 'deathstar':
                $name = Default_Model_Ship::DEATHSTAR;
                break;
            case 'battlecr.':
                $name = Default_Model_Ship::BATTLECRUISER;
                break;
            // defenses
            case 'r.launcher':
                $name = Default_Model_Ship::ROCKET_LAUNCHER;
                break;
            case 'l.laser':
                $name = Default_Model_Ship::LIGHT_LASER;
                break;
            case 'h.laser':
                $name = Default_Model_Ship::HEAVY_LASER;
                break;
            case 'gauss':
                $name = Default_Model_Ship::GAUSS_CANNON;
                break;
            case 'ionc.':
                $name = Default_Model_Ship::ION_CANNON;
                break;
            case 'plasma':
                $name = Default_Model_Ship::PLASMA_TURRET;
                break;
            case 's.dome':
                $name = Default_Model_Ship::SMALL_SHIELD_DOME;
                break;
            case 'l.dome':
                $name = Default_Model_Ship::LARGE_SHIELD_DOME;
                break;
        }

        return new Default_Model_Ship($name, $count);
    }

    /**
     * Parse the battle's result
     *
     * @return void
     */
    protected function _parseResult()
    {
        // check who has won the fight
        if (preg_match('#has won the battle#i', $this->_source)) {
            if (preg_match('#attacker has won the battle#i', $this->_source)) {
                $this->_report->setWinner(Default_Model_CombatReport::ATTACKER);

                // the attacker won, get the number of stolen resources

                // The attacker has won the battle! He captured 67.585 metal, 36.070 crystal and 10.421 deuterium.
                $regex = 'He captured\s*?([0-9.]*) metal, ([0-9.]*) crystal and ([0-9.]*) deuterium';

                $matches = array();
                preg_match('#' . $regex . '#si', $this->_source, $matches);

                $this->_report->setLoot((int) str_replace('.', '', $matches[1]),
                                        (int) str_replace('.', '', $matches[2]),
                                        (int) str_replace('.', '', $matches[3]));
            } else {
                $this->_report->setWinner(Default_Model_CombatReport::DEFENDER);
            }
        } else {
                $this->_report->setWinner(Default_Model_CombatReport::DRAW);
        }

        // get the attacker's losses
        $matches = array();
        preg_match('#The attacker lost a total of ([0-9.]*) units.#i', $this->_source, $matches);

        $this->_report->setLossesAttacker((int) str_replace('.', '', $matches[1]));

        // get the defender's losses
        $matches = array();
        preg_match('#The defender lost a total of ([0-9.]*) units.#i', $this->_source, $matches);

        $this->_report->setLossesDefender((int) str_replace('.', '', $matches[1]));

        // get the debris
        $matches = array();
        preg_match('#At these space coordinates now float ([0-9.]*) metal and ([0-9.]*) crystal.#i', $this->_source, $matches);

        $this->_report->setDebris((int) str_replace('.', '', $matches[1]), (int) str_replace('.', '', $matches[2]));

        // moonchance
        $matches = array();
        if (preg_match('#The chance for a moon to be created is ([0-9]{1,2})#i', $this->_source, $matches)) {
            $this->_report->setMoonChance((int) str_replace('.', '', $matches[1]));
        }

        // moon creation

        // The enormous amounts of free metal and crystal draw together and form a moon around the planet.
        $regex = 'form a moon around the planet.';
        $matches = array();
        if (preg_match("#{$regex}#i", $this->_source, $matches)) {
            $this->_report->setMoonGiven(true);
        } else {
            $this->_report->setMoonGiven(false);
        }
    }

    /**
     * Merge fleets together.
     *
     * @return void
     */
    private function _mergeFleets()
    {
        foreach ($this->_report->getRounds() as $round) {
            // first merge the fleets of the attackers
            $attackers = array();
            foreach ($round->getAttackers() as $fleet) {
                $player = $fleet->getPlayer();
                if (isset($attackers[$player])) {
                    // merge this fleet into the previous one
                    $attackers[$player] = $this->_mergeFleet($attackers[$player], $fleet);
                } else {
                    // we haven't seen this attacker before, create a new slot
                    $attackers[$player] = $fleet;
                }
            }

            $round->setAttackers(array_values($attackers));
            unset($attackers);

            // now merge the fleets of the defenders
            $defenders = array();
            foreach ($round->getDefenders() as $fleet) {
                $player = $fleet->getPlayer();
                if (isset($defenders[$player])) {
                    // merge this fleet into the previous one
                    $defenders[$player] = $this->_mergeFleet($defenders[$player], $fleet);
                } else {
                    // we haven't seen this attacker before, create a new slot
                    $defenders[$player] = $fleet;
                }
            }

            $round->setDefenders(array_values($defenders));
        }
    }

    /**
     * Merge two fleets
     *
     * @param Default_Model_Fleet $fleet1
     * @param Default_Model_Fleet $fleet2
     *
     * @return array
     */
    public function _mergeFleet(Default_Model_Fleet $fleet1, Default_Model_Fleet $fleet2)
    {
        foreach ($fleet2->getShips() as $ship) {
            if ($fleet1->hasShip($ship->getName())) {
                $fleet1->getShip($ship->getName())->addCount($ship->getCount());
            } else {
                $fleet1->addShip($ship);
            }
        }
        return $fleet1;
    }

    /**
     * Convert to integer.
     *
     * @param string $number
     *
     * @return int
     */
    protected function _convertToInt($number)
    {
        return (int) str_replace('.', '', $number);
    }
}
