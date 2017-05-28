<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 9/5/17
 * Time: 16:58
 */

namespace GLFramework;

/**
 * Class DateUtils
 *
 * @package GLFramework
 */
class DateUtils
{
    /**
     * TODO
     *
     * @param $date
     * @param null $range
     */
    public function parse($date, $range = null)
    {
        $tests = array(
            array(
                'pattern' => '#([0-9]{2})/([0-9]{2})/([0-9]{2})#',
                'try' => array(
                    array('D' => '1', 'M' => '2', 'A' => '3'),

                )
            )
        );
        foreach ($tests as $item) {
            $pattern = $item['pattern'];
            $tries = $item['try'];
            if (!isset($tries[0])) {
                $tries = array($tries);
            }
            if (preg_match($item, $date, $matches)) {
                foreach ($tries as $try) {
                    $valid = $this->build($matches, $try);
                    //                    if($va)
                }
            }
        }
    }

    /**
     * TODO
     *
     * @param $matches
     * @param $assoc
     */
    private function build($matches, $assoc)
    {
    }

    /**
     * TODO
     *
     * @param $date
     * @return bool
     */
    private function isValid($date)
    {
        return strtotime($date) !== 0;
    }
}
