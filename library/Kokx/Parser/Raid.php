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
class Kokx_Parser_Raid
{

    /**
     * The battle's result
     *
     * @var array
     */
    protected $_raids = array();


    /**
     * Get the battle result
     *
     * @return array
     */
    public function getRaids()
    {
        return $this->_raids;
    }

    /**
     * Parse a crash report
     *
     * @param string $source
     *
     * @return array
     */
    public function parse($source)
    {
        // the source could have multiple CR's
        /**
         * The source only has to contain something like:
         *
         * 99.552 Metaal, 3.748 Kristal en 17.333 Deuterium
         */

        $regex = '([0-9.]*) Metaal, ([0-9.]*) Kristal en ([0-9.]*) Deuterium';

        $matches = array();

        preg_match_all('/' . $regex . '/i', $source, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $this->_raids[] = array(
                'metal'   => $match[1],
                'crystal' => $match[2],
                'deut'    => $match[3]
            );
        }


        return $this;
    }
}