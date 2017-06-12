<?php
/**
 * Created by PhpStorm.
 * User: AnwarHussainAbulHassan
 * Date: 11/06/2017
 * Time: 7:49 PM
 */

namespace Abul\GDrive\Tests\Command;


use Abul\GDrive\Command\ListCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Tester\CommandTester;

class ListCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ListCommand
     */
    private $command;

    public function setup()
    {
        $this->command = new ListCommand();
    }

    public function testCommand()
    {
        $mainApp = new Application();
        $mainApp->add($this->command);
        $this->assertNotEmpty($mainApp->get('search'));
    }

    public function testConfigure()
    {
        $this->assertEquals('search',$this->command->getName());
    }

    public function testConfigure2()
    {
        $this->assertEquals('Search for files in gdrive application',$this->command->getDescription());
    }

    public function testConfigure3()
    {
        $this->assertTrue($this->command->getDefinition()->hasOption('query'));
        $this->assertTrue($this->command->getDefinition()->hasOption('orderBy'));
        $this->assertFalse($this->command->getDefinition()->hasArgument('orderBy'));
    }

    public function testExecute()
    {

        $command = $this->getMockBuilder('Abul\GDrive\Command\ListCommand')
            ->setMethods(['getAllFilesBasedOnQuesry'])
            ->getMock();

        $command->expects($this->any())
            ->method('getAllFilesBasedOnQuesry')
            ->willReturn([]);

        $commandTester = new CommandTester($command);
        $application = new Application();
        $application->add($command);

        $command = $application->find('search');
        $commandTester->execute(['command' => $command->getName(), '--query' => 'quer']);
        $this->assertEquals("No matching file/folder found.".PHP_EOL,$commandTester->getDisplay());
    }


    public function testExecute2()
    {
        $command = $this->getMockBuilder('Abul\GDrive\Command\ListCommand')
            ->setMethods(['getAllFilesBasedOnQuesry', 'getFileDetails'])
            ->getMock();

        $file = new \Google_Service_Drive_DriveFile();
        $file->setId('1');
        $file->setName('Foo.bar');
        $file->setSize(1022031);
        $file->setMimeType('applcation/bar');
        $file->createdTime = time();

        $command->expects($this->any())
            ->method('getAllFilesBasedOnQuesry')
            ->willReturn([$file]);
        $command->expects($this->any())
            ->method('getFileDetails')
            ->willReturn($file);

        $commandTester = new CommandTester($command);
        $application = new Application();
        $application->add($command);

        $command = $application->find('search');
        $commandTester->execute(['command' => $command->getName(), '--query' => 'quer']);
        $this->assertNotEquals("No matching file/folder found.".PHP_EOL,$commandTester->getDisplay());
    }
}