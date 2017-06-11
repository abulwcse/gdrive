<?php

namespace Abul\GDrive\Tests\Command;


use Abul\GDrive\Command\AboutCommand;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Tester\CommandTester;

class AboutCommandTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Command
     */
    private $command;

    public function setup()
    {
        $this->command = new AboutCommand();
    }

    public function testCommand()
    {
        $mainApp = new Application();
        $mainApp->add($this->command);
        $this->assertNotEmpty($mainApp->get('about'));
    }

    public function testConfigure()
    {
        $this->assertEquals('about',$this->command->getName());
    }

    public function testConfigure2()
    {
        $this->assertEquals('Authenticate gdrive application',$this->command->getDescription());
    }

    public function testCommandExecution($input = [])
    {
        $application = new Application();
        $application->add(new AboutCommand());

        $command = $application->find('about');

        $ask = function (InputInterface $input, OutputInterface $output, Question $question) {
            static $order = -1;
            $order = $order + 1;
            $text = $question->getQuestion();
            $output->write($text." => ");
            if (strpos($text, 'Please enter the client Secret') !== false) {
                $response = 'client_secrect';
            } elseif (strpos($text, 'Please enter the client ID') !== false) {
                $response = 'client_id';
            } elseif (strpos($text, 'Authentication code') !== false) {
                $response = 'dummy code';
            }

            if (isset($response) !== false) {
                throw new \RuntimeException('Was asked for input on an unhandled question: '.$text);
            } else {
                $output->writeln(print_r($response, true));
                return $response;
            }
        };
        $helper = $this->getMock('\Symfony\Component\Console\Helper\QuestionHelper', ['ask']);
        $helper->expects($this->any())
            ->method('ask')
            ->will($this->returnCallback($ask));

        $command->getHelperSet()->set($helper, 'question');

        $cmdTester = new CommandTester($command);

        $cmdTester->execute([
            'command' => $command->getName(),
        ]);
    }
}
