<?php

namespace Abul\GDrive\Command;

use Abul\GDrive\Exception\ConfigurationMissingException;
use Abul\GDrive\Helper\ConfigurationHelper;
use Abul\GDrive\Helper\GDriveHelper;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class AboutCommand extends Command
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName('about');
        $this->setDescription('Authenticate gdrive application');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $gDriveHelper = $this->getGoogleDriveHelper();
            foreach ($gDriveHelper->getAbout() as $key => $value) {
                $output->writeln("$key : " . $value);
            }
        } catch (ConfigurationMissingException $e) {
            $gDriveHelper = new GDriveHelper(false);
            $helper = $this->getHelper('question');
            if (!ConfigurationHelper::getInstance()->configExist(ConfigurationHelper::APP_CONFIG_FILE)) {
                $question = new Question('Please enter the client ID: ', '');
                $clientID = $helper->ask($input, $output, $question);
                $question = new Question('Please enter the client Secret: ', '');
                $clientSecret = $helper->ask($input, $output, $question);
                $gDriveHelper->getClient()->setClientId(trim($clientID));
                $gDriveHelper->getClient()->setClientSecret(trim($clientSecret));

                ConfigurationHelper::getInstance()->writeConfig(ConfigurationHelper::APP_CONFIG_FILE,[
                    'client_id' => $clientID,
                    'client_secret' => $clientSecret
                ]);
            }

            $output->writeln("Please visit the link to get Authentication code: " . $gDriveHelper->getClient()->createAuthUrl());
            $question = new Question('Authentication code: ', '');
            $authCode = $helper->ask($input, $output, $question);
            $config = $gDriveHelper->getClient()->fetchAccessTokenWithAuthCode($authCode);
            ConfigurationHelper::getInstance()->writeConfig(
                ConfigurationHelper::ACCESS_CONFIG_FILE,
                $config
            );
            $output->writeln("GDrive is successfully initialised.");

        } catch (Exception $e) {
            $output->writeln($e->getMessage());
        }
    }

    /**
     * @return GDriveHelper
     */
    private function getGoogleDriveHelper()
    {
        return new GDriveHelper();
    }
}
