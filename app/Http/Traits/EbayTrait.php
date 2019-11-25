<?php
/**
 * @since       v0.1.0
 * @package     Dev-PHP
 * @author      Andre Board <dre.board@gmail.com>
 * @version     v1.0
 * @access      public
 * @see         https://github.com/dreboard
 */

namespace App\Http\Traits;


use DateInterval;
use DateTimeImmutable;
use DateTimeZone;

trait EbayTrait
{
    protected $format = 'Y-m-d\TH:i:s\.000';

    public function getEbayTime(int $offset = 0)
    {
        $time = microtime(true);
        $tMicro = sprintf("%03d",($time - floor($time)) * 1000);
        return gmdate('Y-m-d\TH:i:s.', $time).$tMicro.'Z';
    }

    public function createTimeFrom()
    {
        $dates = [];
        $date = new DateTimeImmutable();
        $date->setTimezone(new DateTimeZone('GMT'));
        $time = microtime(true);
        $tMicro = sprintf("%03d",($time - floor($time)) * 1000);
        return $date->sub(new DateInterval('P30D'))->format('Y-m-d\TH:i:s\.').$tMicro.'Z';
    }

    public function createTimeTo()
    {
        $dates = [];
        $date = new DateTimeImmutable();
        $date->setTimezone(new DateTimeZone('GMT'));
        $time = microtime(true);
        $tMicro = sprintf("%03d",($time - floor($time)) * 1000);
        return $date->add(new DateInterval('P30D'))->format('Y-m-d\TH:i:s\.').$tMicro.'Z';
    }

}
