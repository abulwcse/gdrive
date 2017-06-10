<?php

namespace Abul\GDrive\Tests\Command;


use Abul\GDrive\Command\AboutCommand;
use PHPUnit_Framework_TestCase;

class AboutCommandTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var AboutCommand
     */
    private $aboutCommandInstanse;

    public function setup()
    {
        $this->aboutCommandInstanse = new AboutCommand();
    }

    public function testConfigure()
    {
        $this->assertEquals('about',$this->aboutCommandInstanse->getName());
    }

    public function testConfigure2()
    {
        $this->assertEquals('Authenticate gdrive application',$this->aboutCommandInstanse->getDescription());
    }
}