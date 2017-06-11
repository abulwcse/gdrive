<?php

namespace Abul\GDrive\Command;


use Abul\GDrive\Exception\ConfigurationMissingException;
use Abul\GDrive\Exception\FileNotFoundException;
use Abul\GDrive\Helper\GDriveHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UploadCommand extends Command
{
    /**
     * @var GDriveHelper
     */
    protected $client;

    protected function configure()
    {
        $this->setName('upload');
        $this->setDescription('Upload a file to google drive.');
        $this->setDefinition(new InputDefinition([
            new InputArgument('file', InputArgument::REQUIRED, "File to be be uploaded")
        ]));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $file = $input->getArgument('file');
            $file = $this->uploadFile($file);
            $output->writeln("File upload successful.");
        } catch (FileNotFoundException $e) {
            $output->writeln($e->getMessage());
        } catch (ConfigurationMissingException $e) {
            $output->writeln($e->getMessage());
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }


    /**
     * Upload a file
     *
     * @param $file
     *
     * @return \Google_Service_Drive_DriveFile
     */
    public function uploadFile($file)
    {
        return $this->getClient()->uploadFile($file);
    }

    /**
     * Get the google drive helper class instance
     *
     * @return GDriveHelper
     */
    protected function getClient()
    {
        if ($this->client === null) {
            $this->client = new GDriveHelper();
        }
        return $this->client;
    }
}
