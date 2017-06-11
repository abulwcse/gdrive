<?php

namespace Abul\GDrive\Tests\Command;


use Abul\GDrive\Command\DownloadCommand;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DownloadCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DownloadCommand
     */
    private $command;

    public function setup()
    {
        $this->command = new DownloadCommand();
    }

    public function testCommand()
    {
        $mainApp = new Application();
        $mainApp->add($this->command);
        $this->assertNotEmpty($mainApp->get('download'));
    }

    public function testConfigure()
    {
        $this->assertEquals('download',$this->command->getName());
    }

    public function testConfigure2()
    {
        $this->assertEquals('Download files from google drive.',$this->command->getDescription());
    }

    public function testConfigure3()
    {
        $this->assertTrue($this->command->getDefinition()->hasArgument('fileId'));
        $this->assertFalse($this->command->getDefinition()->hasArgument('query'));
        $this->assertTrue($this->command->getDefinition()->hasOption('query'));
        $this->assertFalse($this->command->getDefinition()->hasOption('fileId'));
    }

    public function testExecute()
    {
        $command = $this->getMockBuilder('Abul\GDrive\Command\DownloadCommand')
            ->setMethods(['getAllFilesBasedOnQuesry'])
            ->getMock();

        $command->expects($this->any())
            ->method('getAllFilesBasedOnQuesry')
            ->willReturn([]);

        $commandTester = new CommandTester($command);
        $application = new Application();
        $application->add($command);

        $command = $application->find('download');
        $commandTester->execute(['command' => $command->getName(), '--query' => 'quer']);
        $this->assertEquals('No matching file/folder found.'.PHP_EOL,$commandTester->getDisplay());
    }

    public function testExecute2()
    {
        $command = $this->getMockBuilder('Abul\GDrive\Command\DownloadCommand')
            ->setMethods(['getAllFilesBasedOnQuesry', 'getFileDetails', 'downloadFile'])
            ->getMock();

        $file = new \Google_Service_Drive_DriveFile();
        $file->setId('1');
        $file->setName('Foo.bar');
        $file->setSize(1022031);
        $file->setMimeType('applcation/bar');
        $file->createdTime = time();

        $command->expects($this->once())
            ->method('getAllFilesBasedOnQuesry')
            ->willReturn([$file]);

        $command->expects($this->never())
            ->method('getFileDetails')
            ->willReturn($file);

        $commandTester = new CommandTester($command);
        $application = new Application();
        $application->add($command);

        $command = $application->find('download');
        $commandTester->execute(['command' => $command->getName(), '--query' => 'quer']);
        $this->assertEquals("Successfully downloaded file : Foo.bar".PHP_EOL,$commandTester->getDisplay());
    }

    public function testExecute3()
    {
        $command = $this->getMockBuilder('Abul\GDrive\Command\DownloadCommand')
            ->setMethods(['getAllFilesBasedOnQuesry', 'getFileDetails', 'downloadFile'])
            ->getMock();

        $file = new \Google_Service_Drive_DriveFile();
        $file->setId('1');
        $file->setName('Foo.bar');
        $file->setSize(1022031);
        $file->setMimeType('applcation/bar');
        $file->createdTime = time();

        $command->expects($this->never())
            ->method('getAllFilesBasedOnQuesry')
            ->willReturn([$file]);

        $command->expects($this->once())
            ->method('getFileDetails')
            ->willReturn($file);

        $commandTester = new CommandTester($command);
        $application = new Application();
        $application->add($command);

        $command = $application->find('download');
        $commandTester->execute(['command' => $command->getName(), 'fileId' => '1']);
        $this->assertEquals("Successfully downloaded file : Foo.bar".PHP_EOL,$commandTester->getDisplay());
    }
}