<?php

namespace Abul\GDrive\Tests\Helper;

use Abul\GDrive\Helper\StringHelper;
use PHPUnit_Framework_TestCase;

class StringHelperTest extends PHPUnit_Framework_TestCase
{

    public function testHumanReadableFileSize()
    {
        $this->assertEquals("0.00B", StringHelper::humanReadableFileSize(0));
    }

    public function testHumanReadableFileSize2()
    {
        $this->assertEquals("1.00kB", StringHelper::humanReadableFileSize(1024));
    }

    public function testHumanReadableFileSize3()
    {
        $this->assertEquals("0.96MB", StringHelper::humanReadableFileSize(1002400));
    }
}