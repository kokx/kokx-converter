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
 * @package    Kokx_Parser
 */

/**
 * CR parser
 *
 * @category   KokxConverter
 * @package    Kokx_Parser
 */
class Kokx_Parser_CrashReport
{

    const ATTACKER = 'attacker';
    const DEFENDER = 'defender';
    const NONE     = 'none';

    /**
     * Source
     *
     * @var string
     */
    protected $_source = '';

    /**
     * All the round data
     *
     * @var array
     */
    protected $_rounds = array();

    /**
     * Time of the battle
     *
     * @var array
     */
    protected $_time = array(
        'time' => '',
        'date' => ''
    );

    /**
     * The battle's result
     *
     * @var array
     */
    protected $_result = array(
        'winner'         => '', // 'none', 'defender' or 'attacker'
        'attackerlosses' => 0,  // the attacker's losses
        'defenderlosses' => 0,  // the defender's losses
    	'stolen'         => array( // the number of stolen goods
            'metal'   => 0,
            'crystal' => 0,
            'deut'    => 0
        ),
        'debris' => array( // debris field
            'metal'   => 0,
            'crystal' => 0
        ),
        'moonchance' => 0,
        'moon'       => false
    );


    /**
     * Get all the rounds
     *
     * @return array
     */
    public function getRounds()
    {
        return $this->_rounds;
    }

    /**
     * Get the battle time
     *
     * @return array
     */
    public function getTime()
    {
        return $this->_time;
    }

    /**
     * Get the battle result
     *
     * @return array
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * Parse a crash report
     *
     * @return array
     */
    public function parse($source)
    {
        $this->_source = stristr($source, 'De volgende vloten kwamen elkaar tegen op');

        // check the CR
        if (false === $this->_source) {
            throw new Kokx_Parser_Exception('Bad CR');
        }

        $matches = array();

        if (preg_match('#^De volgende vloten kwamen elkaar tegen op \(([0-9]{2}.[0-9]{2}.[0-9]{4}) ([0-9]{2}:[0-9]{2}:[0-9]{2})\):#i', $this->_source, $matches)) {
            // redesign style
            $this->_time['date'] = $matches[1];
            $this->_time['time'] = $matches[2];
        } else {
            throw new Kokx_Parser_Exception('Bad CR');
        }

        $this->_source = substr($this->_source, strlen($matches[0]));

        $this->_rounds = array();

        while (preg_match('#(Aanvaller|Verdediger) (.*) \[([0-9]:[0-9]{1,3}:[0-9]{1,2})\]#i', $this->_source)) {
            $this->_rounds[] = $this->_parseRedesignRound();
        }

        $this->_parseResult();

        return $this;
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
    protected function _parseRedesignRound()
    {
        $round = array(
            'attackers' => array(),
            'defenders' => array()
        );

        // first find the first attacker
        $this->_source = strstr($this->_source, 'Aanvaller');

        /*
         * Aanvaller Preacher [1:115:4] Wapens: 0% Schilden: 0% Pantser: 0%
         * Soort 	K. Vrachtschip
         * Aantal 	1
         * Wapens: 	5
         * Schilden 	10
         * Romp 	400
         *
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
        $regex = '(Aanvaller|Verdediger) ([^\n\r]*?)(\s*?\[([0-9]:[0-9]{1,3}:[0-9]{1,2})\])?'
               . '(\s*?Wapens: ([0-9]{0,2})0% Schilden: ([0-9]{0,2})0% Pantser: ([0-9]{0,2})0%)?\s*'
               . '(Soort([A-Za-z.-\s]*)\s*' . 'Aantal([0-9.\s]*)' . '|vernietigd.)\s*'
               . '.*?(Wapens)?';

        $foundDefender = false;

        $matches = array();
        // loop trough the text until we have found all fleets in the round
        while (preg_match('#' . $regex . '#si', $this->_source, $matches)) {
            //var_dump($matches);
            // extract the info from the matches array
            $info = array(
                'player' => array(
                    'name'   => $matches[2],
                    'coords' => $matches[4],
                    'techs'  => array(
                        'weapon' => (int) $matches[6],
                        'shield' => (int) $matches[7],
                        'armor'  => (int) $matches[8],
                    )
                ),
                'fleet' => array()
            );

            if ($matches[9] != 'vernietigd.') {
                $matches[10] = str_replace(array("\n", "\r", "  "), "\t", $matches[10]);
                $matches[11] = str_replace(array("\n", "\r", "  "), "\t", $matches[11]);

                // add the fleet info
                $ships   = explode("\t", trim($matches[10]));
                $numbers = explode("\t", trim($matches[11]));

                foreach ($ships as $key => $ship) {
                    $info['fleet'][$this->normalizeShipName($ship)] = trim(str_replace('.', '', $numbers[$key]));
                }
            }

            // check if it is an attacker or a defender
            if ($matches[1] == 'Aanvaller') {
                if ($foundDefender) {
                    break;
                }

                $round['attackers'][] = $info;
            } else {
                $round['defenders'][] = $info;

                $foundDefender = true;
            }

            $this->_source = substr($this->_source, strlen($matches[0]));

            // always reset this array at the end
            $matches = array();
        }

        //var_dump($round);

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
            if (preg_match('#De aanvaller heeft het gevecht#i', $this->_source)) {
                $this->_result['winner'] = self::ATTACKER;

                // the attacker won, get the number of stolen resources

                $regex = 'De aanvaller steelt\s*?([0-9.]*) Metaal, ([0-9.]*) Kristal en ([0-9.]*) Deuterium';

                $matches = array();
                preg_match('#' . $regex . '#si', $this->_source, $matches);

                $this->_result['stolen'] = array(
                    'metal'   => (int) str_replace('.', '', $matches[1]),
                    'crystal' => (int) str_replace('.', '', $matches[2]),
                    'deut'    => (int) str_replace('.', '', $matches[3]),
                );
            } else {
                $this->_result['winner'] = self::DEFENDER;
            }
        } else {
            $this->_result['winner'] = self::NONE;
        }

        // get the attacker's losses
        $matches = array();
        preg_match('#De aanvaller heeft een totaal van ([0-9.]*) Eenheden verloren.#i', $this->_source, $matches);

        $this->_result['attackerlosses'] = str_replace('.', '', $matches[1]);

        // get the defender's losses
        $matches = array();
        preg_match('#De verdediger heeft een totaal van ([0-9.]*) Eenheden verloren.#i', $this->_source, $matches);

        $this->_result['defenderlosses'] = str_replace('.', '', $matches[1]);

        // get the debris
        $matches = array();
        preg_match('#in de ruimte zweven nu ([0-9.]*) Metaal en ([0-9.]*) Kristal.#i', $this->_source, $matches);

        $this->_result['debris']['metal']   = str_replace('.', '', $matches[1]);
        $this->_result['debris']['crystal'] = str_replace('.', '', $matches[2]);

        // moonchance
        $matches = array();
        if (preg_match('#De kans dat een maan ontstaat uit het puin is ([0-9]{1,2})#i', $this->_source, $matches)) {
            $this->_result['moonchance'] = (int) str_replace('.', '', $matches[1]);
        }

        // moon creation

        // De enorme hoeveelheden van rondzwevende metaal- en kristaldeeltjes trekken elkaar aan
        // en vormen langzaam een maan, in een baan rond de planeet.
        $regex = 'De enorme hoeveelheden van rondzwevende metaal- en kristaldeeltjes trekken elkaar aan '
               . 'en vormen langzaam een maan, in een baan rond de planeet.';
        $matches = array();
        if (preg_match("#{$regex}#i", $this->_source, $matches)) {
            $this->_result['moon'] = true;
        }
    }
}
