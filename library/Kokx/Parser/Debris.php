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
class Kokx_Parser_Debris
{

    /**
     * The harvest reports
     *
     * @var array
     */
    protected $_harvest = array();


    /**
     * Get the harvest reports
     *
     * @return array
     */
    public function getHarvest()
    {
        return $this->_harvest;
    }

    /**
     * Parse a harvest report
     *
     * @param string $source
     *
     * @return Kokx_Parser_Debris
     */
    public function parse($source)
    {
        /**
         * Example report:
         *
         * Je 8001 recyclers hebben een totale opslagcapaciteit van 101.264.259.
         * In het bestemmingsveld zweven 0 metaal en 0 kristal in de ruimte.
         * Je hebt 0 metaal en 0 kristal opgehaald.
         */
        $regex  = 'Je ([0-9.]*?) recyclers hebben een totale opslagcapaciteit van ([0-9.]*?). ';
        $regex .= 'In het bestemmingsveld zweven ([0-9.]*?) metaal en ([0-9.]*?) kristal in de ruimte. ';
        $regex .= 'Je hebt ([0-9.]*?) metaal en ([0-9.]*?) kristal opgehaald.';

        $matches = array();

        preg_match_all('/' . $regex . '/i', $source, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $this->_harvest[] = array(
                'recs'         => str_replace('.', '', $match[1]),
                'storage'      => str_replace('.', '', $match[2]),
                'fieldmetal'   => str_replace('.', '', $match[3]),
                'fieldcrystal' => str_replace('.', '', $match[4]),
                'metal'        => str_replace('.', '', $match[5]),
                'crystal'      => str_replace('.', '', $match[6])
            );
        }

        return $this;
    }
}