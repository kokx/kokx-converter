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
 * @subpackage Readers
 */
class Default_Reader_CombatReport
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
        $this->_source = stristr($source, 'De volgende vloten kwamen elkaar tegen op');

        // check the CR
        if (false === $this->_source) {
            throw new Exception('Bad CR');
        }

        $this->_report = new Default_Model_CombatReport();

        $matches = array();

        if (preg_match('#^De volgende vloten kwamen elkaar tegen op \(([0-9]{2}).([0-9]{2}).([0-9]{4}) ([0-9]{2}):([0-9]{2}):([0-9]{2})\):#i', $this->_source, $matches)) {
            $this->_report->setTime(new DateTime($matches[3] . ":" . $matches[2] . ":" . $matches[1] . " " . $matches[4] . ":" . $matches[5] . ":" . $matches[6]));
        } else {
            throw new Exception('Bad CR');
        }

        $this->_source = substr($this->_source, strlen($matches[0]));

        while (preg_match('#(Aanvaller|Verdediger) (.*) \[([0-9]:[0-9]{1,3}:[0-9]{1,2})\]#i', $this->_source)) {
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
        $this->_source = stristr($this->_source, 'Aanvaller');

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
        $regex = '.*?(Aanvaller|Verdediger) ([^\n\r]*?)(\s*?\[([0-9]:[0-9]{1,3}:[0-9]{1,2})\])?'
               . '(\s*?Wapens: ([0-9]{0,2})0% Schilden: ([0-9]{0,2})0% Pantser: ([0-9]{0,2})0%)?\s*'
               . '(Soort([A-Za-z.-\s]*)\s*' . 'Aantal([0-9.\s]*).*?Wapens' . '|vernietigd.)\s*'
               . '.*?(?=Aanvaller|Verdediger)';

        $foundDefender = false;

        $matches = array();
        // loop trough the text until we have found all fleets in the round
        while (preg_match('#' . $regex . '#si', $this->_source, $matches)) {
            // extract the info
            $fleet = new Default_Model_Fleet();

            $fleet->setPlayer($matches[2]);

            if ($matches[9] != 'vernietigd.') {
                $matches[10] = str_replace(array("\n", "\r", "  "), "\t", $matches[10]);
                $matches[11] = str_replace(array("\n", "\r", "  "), "\t", $matches[11]);

                // add the ships info
                $ships   = explode("\t", trim($matches[10]));
                $numbers = explode("\t", trim($matches[11]));

                foreach ($ships as $key => $ship) {
                    $fleet->addShip(new Default_Model_Ship($ship, $this->_convertToInt($numbers[$key])));
                }
            }

            // check if it is an attacker or a defender
            if (strtolower($matches[1]) == 'aanvaller') {
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
     * Parse the battle's result
     *
     * @return void
     */
    protected function _parseResult()
    {
        // check who has won the fight
        if (preg_match('#gewonnen#i', $this->_source)) {
            if (preg_match('#aanvaller heeft het gevecht#i', $this->_source)) {
                $this->_report->setWinner(Default_Model_CombatReport::ATTACKER);

                // the attacker won, get the number of stolen resources

                // De aanvaller heeft het gevecht gewonnen! De aanvaller steelt 26.971 metaal, 16.303 kristal en 11.528 deuterium.
                $regex = 'De aanvaller steelt\s*?([0-9.]*) metaal, ([0-9.]*) kristal en ([0-9.]*) deuterium';

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
        preg_match('#De aanvaller heeft een totaal van ([0-9.]*) eenheden verloren.#i', $this->_source, $matches);

        $this->_report->setLossesAttacker((int) str_replace('.', '', $matches[1]));

        // get the defender's losses
        $matches = array();
        preg_match('#De verdediger heeft een totaal van ([0-9.]*) eenheden verloren.#i', $this->_source, $matches);

        $this->_report->setLossesDefender((int) str_replace('.', '', $matches[1]));

        // get the debris
        $matches = array();
        preg_match('#in de ruimte zweven nu ([0-9.]*) Metaal en ([0-9.]*) Kristal.#i', $this->_source, $matches);

        $this->_report->setDebris((int) str_replace('.', '', $matches[1]), (int) str_replace('.', '', $matches[2]));

        // moonchance
        $matches = array();
        if (preg_match('#De kans dat een maan ontstaat uit het puin is ([0-9]{1,2})#i', $this->_source, $matches)) {
            $this->_report->setMoonChance((int) str_replace('.', '', $matches[1]));
        }

        // moon creation

        // De enorme hoeveelheden van rondzwevende metaal- en kristaldeeltjes trekken elkaar aan
        // en vormen langzaam een maan, in een baan rond de planeet.
        $regex = 'De enorme hoeveelheden van rondzwevende metaal- en kristaldeeltjes trekken elkaar aan '
               . 'en vormen langzaam een maan, in een baan rond de planeet.';
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
        foreach ($this->_rounds as $key => $round) {

            // first merge the fleets of the attackers
            $attackers = array();
            foreach ($round['attackers'] as $slot) {
                $name = $slot['player']['name'];
                if (isset($attackers[$name])) {
                    // merge this fleet into the previous one
                    $attackers[$name]['fleet'] = $this->_mergeFleet($attackers[$name]['fleet'], $slot['fleet']);
                } else {
                    // we haven't seen this attacker before, create a new slot
                    $attackers[$name] = $slot;
                }
            }

            // now merge the fleets of the defenders
            $defenders = array();
            foreach ($round['defenders'] as $slot) {
                $name = $slot['player']['name'];
                if (isset($attackers[$name])) {
                    // merge this fleet into the previous one
                    $defenders[$name]['fleet'] = $this->_mergeFleet($defenders[$name]['fleet'], $slot['fleet']);
                } else {
                    // we haven't seen this attacker before, create a new slot
                    $defenders[$name] = $slot;
                }
            }

            $round['attackers'] = array_values($attackers);
            $round['defenders'] = array_values($defenders);

            // at the end, change the round
            $this->_rounds[$key] = $round;
        }
    }

    /**
     * Merge two fleets
     *
     * @param array $fleet1
     * @param array $fleet2
     *
     * @return array
     */
    public function _mergeFleet(array $fleet1, array $fleet2)
    {
        foreach ($fleet2 as $ship => $amount) {
            if (isset($fleet1[$ship])) {
                $fleet1[$ship] += $amount;
            } else {
                $fleet1[$ship] = $amount;
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
