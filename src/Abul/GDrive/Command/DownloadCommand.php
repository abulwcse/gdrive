<?php

namespace Abul\GDrive\Command;

use Abul\GDrive\Exception\ConfigurationMissingException;
use Abul\GDrive\Helper\GDriveHelper;
use Psr\Log\InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadCommand extends Command
{
    protected function configure()
    {
        $this->setName('download');
        $this->setDescription('Download files from google drive.');
        $this->setDefinition(new InputDefinition([
            new InputArgument('fileId', InputArgument::OPTIONAL, "ID of the file to be downloaded"),
            new InputOption('query', null, InputOption::VALUE_REQUIRED, "Download file that mmatches the given query")
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
            $fileId = $input->getArgument('fileId');
            $query = $input->getOption('query');
            if (!empty($query) && !empty($fileId)) {
                throw new InvalidArgumentException("Either file id or query arugument is expected. Given vslue for both.");
            }
            $fileIds = [];
            if (!empty($query)) {
                $files = $gDriveHelper->getAllFiles($query)->getFiles();
                foreach ($files as $file) {
                    $fileIds[$file->getId()] = $file->getName();
                }
            } else {
                $file = $gDriveHelper->getFileDetail($fileId);
                $fileId[$file->getId()] = $file->getName();
            };

            foreach ($fileIds as $fileId => $fileName) {
                $gDriveHelper->downloadFileDetail($fileId);
                $output->writeln("Successfully downloaded file : " . $fileName);
            }
        } catch (ConfigurationMissingException $e) {
            $output->writeln($e->getMessage());
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }
}