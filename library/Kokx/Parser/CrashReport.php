<?php
/**
 * Ogame Converter
 *
 * @category   Kokx
 * @package    Kokx_Parser
 */

/**
 * CR parser
 *
 * @category   Kokx
 * @package    Kokx_Parser
 */
class Kokx_Parser_CrashReport
{

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
    );


    /**
     * Parse a crash report
     *
     * @return array
     */
    public function parse($source)
    {
        $this->_source = strstr($source, 'De volgende vloten kwamen elkaar tegen op');

        // check the CR
        if (false === $this->_source) {
            throw new Kokx_Parser_Exception('Bad CR');
        }

        $matches = array();

        preg_match('#^De volgende vloten kwamen elkaar tegen op ([0-9]{2}-[0-9]{2}) ([0-9]{2}:[0-9]{2}:[0-9]{2}) , toen het tot een gevecht kwam::#i', $this->_source, $matches);

        $this->_time['date'] = $matches[1];
        $this->_time['time'] = $matches[2];

        $this->_source = substr($this->_source, strlen($matches[0]));

        $this->_rounds = array($this->_parseFirstRound());

        do {
            $this->_rounds[] = $this->_parseRound();
        } while (preg_match('#De aanvallende vloot vuurt#i', $this->_source));

        $this->_parseResult();

        return $this;
    }

    /**
     * Parse the first round
     *
     * @return array
     */
    protected function _parseFirstRound()
    {
        $round = array(
            'attackers' => array(),
            'defenders' => array()
        );

        // first find the first attacker
        $this->_source = strstr($this->_source, 'Aanvaller');

        // complicated regex that extracts all info from a fleet slot
        $regex = '(Aanvaller|Verdediger) (.*?) \(\[([0-9]:[0-9]{1,3}:[0-9]{1,2})\]\)\s'
               . 'Wapens: ([0-9]{1,2})0% Schilden: ([0-9]{1,2})0% Romp beplating: ([0-9]{1,2})0%\s'
               . 'Soort([A-Za-z.\t]*)\s'
               . 'Aantal([0-9.\t]*)'
               . '.*?(Aanvaller|Verdediger|De aanvallende vloot vuurt)';

        $matches = array();
        // loop trough the text until we have found all fleets in the round
        while (preg_match('#' . $regex . '#s', $this->_source, $matches)) {
            $this->_source = substr($this->_source, strlen($matches[0]) - strlen($matches[9]));

            // extract the info from the matches array
            $info = array(
                'player' => array(
                    'name'   => $matches[2],
                    'coords' => $matches[3],
                    'techs'  => array(
                        'weapon' => (int) $matches[4],
                        'shield' => (int) $matches[5],
                        'armor'  => (int) $matches[6],
                    )
                ),
                'fleet' => array()
            );

            // add the fleet info
            $ships   = explode("\t", trim($matches[7]));
            $numbers = explode("\t", trim($matches[8]));

            foreach ($ships as $key => $ship) {
                $info['fleet'][$ship] = $numbers[$key];
            }

            // check if it is an attacker or a defender
            if ($matches[1] == 'Aanvaller') {
                $round['attackers'][] = $info;
            } else {
                $round['defenders'][] = $info;
            }

            // end the loop when we have all the fleets
            if ($matches[9] == 'De aanvallende vloot vuurt') {
                break;
            }

            // always reset this array at the end
            $matches = array();
        }

        return $round;
    }

    /**
     * Parse a round
     *
     * @return array
     */
    protected function _parseRound()
    {
        $round = array(
            'attackers' => array(),
            'defenders' => array()
        );

        // first find the first attacker
        $this->_source = strstr($this->_source, 'Aanvaller');

        // complicated regex that extracts all info from a fleet slot
        $regex = '(Aanvaller|Verdediger) (.*?) \(\[([0-9]:[0-9]{1,3}:[0-9]{1,2})\]\)\s'
               . '(Soort([A-Za-z.\t]*)\s' . 'Aantal([0-9.\t]*)' . '|Vernietigd)\s'
               . '.*?(Aanvaller|Verdediger|De aanvallende vloot vuurt|De aanvaller heeft|De verdediger heeft|remise)';

        $matches = array();
        // loop trough the text until we have found all fleets in the round
        while (preg_match('#' . $regex . '#s', $this->_source, $matches)) {
            $this->_source = substr($this->_source, strlen($matches[0]) - strlen($matches[7]));

            // extract the info from the matches array
            $info = array(
                'player' => array(
                    'name'   => $matches[2],
                    'coords' => $matches[3]
                ),
                'fleet' => array()
            );

            // if the fleet isn't destroyed, add it to the info
            if ($matches[4] != 'Vernietigd') {
                $ships   = explode("\t", trim($matches[5]));
                $numbers = explode("\t", trim($matches[6]));

                foreach ($ships as $key => $ship) {
                    $info['fleet'][$ship] = $numbers[$key];
                }
            }

            // check if it is an attacker or a defender
            if ($matches[1] == 'Aanvaller') {
                $round['attackers'][] = $info;
            } else {
                $round['defenders'][] = $info;
            }

            // end the loop when we have all the fleets
            if (($matches[7] == 'De aanvallende vloot vuurt')
            || ($matches[7] == 'De aanvaller heeft')
            || ($matches[7] == 'De verdediger heeft')
            || ($matches[7] == 'remise')) {
                break;
            }

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
        $this->_result['winner'] = '';
    }
}