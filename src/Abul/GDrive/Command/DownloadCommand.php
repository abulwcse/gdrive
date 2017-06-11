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
            $fileId = $input->getArgument('fileId');
            $query = $input->getOption('query');
            if (!empty($query) && !empty($fileId)) {
                throw new InvalidArgumentException("Either file id or query arugument is expected. Given vslue for both.");
            }
            $fileIds = [];
            if (!empty($query)) {
                $files = $this->getAllFilesBasedOnQuesry($query);
                foreach ($files as $file) {
                    $fileIds[$file->getId()] = $file->getName();
                }
            } else {
                $file = $this->getFileDetails($fileId);
                $fileIds[$file->getId()] = $file->getName();
            };
            if(count($fileIds) > 0) {
                foreach ($fileIds as $fileId => $fileName) {
                    $this->downloadFile($fileId);
                    $output->writeln("Successfully downloaded file : " . $fileName);
                }
            } else {
                $output->writeln("No matching file/folder found.");
            }
        } catch (ConfigurationMissingException $e) {
            $output->writeln($e->getMessage());
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }


    /**
     * Get all files that matched the given query
     *
     * @param $query
     *
     * @return \Google_Service_Drive_DriveFile[]
     */
    protected function getAllFilesBasedOnQuesry($query)
    {
        return $this->getClient()->getAllFiles($query)->getFiles();
    }

    /**
     * Get File details based on filed id
     *
     * @param $fileId
     *
     * @return \Google_Service_Drive_DriveFile
     */
    protected function getFileDetails($fileId)
    {
        return $this->getClient()->getFileDetail($fileId);
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

    /**
     * Download the file with given id
     *
     * @param $fileId
     */
    protected function downloadFile($fileId)
    {
        $this->getClient()->downloadFileDetail($fileId);
    }
}
