<?php
/**
 * Created by PhpStorm.
 * User: AnwarHussainAbulHassan
 * Date: 11/06/2017
 * Time: 10:55 PM
 */

namespace Abul\GDrive\Tests\Command;


use Abul\GDrive\Command\UploadCommand;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class UploadCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var UploadCommand
     */
    private $command;

    public function setup()
    {
        $this->command = new UploadCommand();
    }

    public function testCommand()
    {
        $mainApp = new Application();
        $mainApp->add($this->command);
        $this->assertNotEmpty($mainApp->get('upload'));
    }

    public function testConfigure()
    {
        $this->assertEquals('upload',$this->command->getName());
    }

    public function testConfigure2()
    {
        $this->assertEquals('Upload a file to google drive.',$this->command->getDescription());
    }

    public function testConfigure3()
    {
        $this->assertTrue($this->command->getDefinition()->hasArgument('file'));
        $this->assertFalse($this->command->getDefinition()->hasOption('file'));
    }

    public function testExecute()
    {
        $command = $this->getMockBuilder('Abul\GDrive\Command\UploadCommand')
            ->setMethods(['uploadFile'])
            ->getMock();

        $command->expects($this->any())
            ->method('uploadFile')
            ->willReturn(null);

        $commandTester = new CommandTester($command);
        $application = new Application();
        $application->add($command);

        $command = $application->find('upload');
        $commandTester->execute(['command' => $command->getName(), 'file' => 'foo.json']);
        $this->assertEquals('File upload successful.'.PHP_EOL,$commandTester->getDisplay());
    }
}