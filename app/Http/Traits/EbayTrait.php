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


trait EbayTrait
{

    public function getEbayTime(int $offset = 0)
    {
        $time = microtime(true);
        $tMicro = sprintf("%03d",($time - floor($time)) * 1000);
        return gmdate('Y-m-d\TH:i:s.', $time).$tMicro.'Z';
    }

}