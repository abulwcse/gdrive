<?php

namespace Abul\GDrive\Command;


use Abul\GDrive\Exception\ConfigurationMissingException;
use Abul\GDrive\Helper\GDriveHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UploadCommand extends Command
{
    protected function configure()
    {
        $this->setName('upload');
        $this->setDescription('Download files from google drive.');
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
            $gDriveHelper = new GDriveHelper();
            $file = $input->getArgument('file');
            if (file_exists($file)) {
                $file = $gDriveHelper->uploadFile($file);
                $output->writeln("File upload successful.");
            }
        } catch (ConfigurationMissingException $e) {
            $output->writeln($e->getMessage());
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }
}
