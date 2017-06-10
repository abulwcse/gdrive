<?php

namespace Abul\GDrive\Helper;


class StringHelper
{
    /**
     * @param $bytes
     * @param int $decimals
     * @return string
     */
    public static function humanReadableFileSize($bytes, $decimals = 2)
    {
        $size = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

}
