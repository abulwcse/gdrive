<?php

namespace Abul\GDrive\Command;

use Abul\GDrive\Exception\ConfigurationMissingException;
use Abul\GDrive\Exception\MissingArgumentException;
use Abul\GDrive\Helper\GDriveHelper;
use Abul\GDrive\Helper\StringHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{

    /**
     * @var GDriveHelper
     */
    protected $client;

    protected function configure()
    {
        $this->setName('search');
        $this->setDescription('Search for files in gdrive application');
        $this->setDefinition(new InputDefinition([
            new InputOption('query', null, InputOption::VALUE_REQUIRED, "Query string to search files based on."),
            new InputOption('orderBy', null, InputOption::VALUE_REQUIRED, "Order by field to sort the resltant files.")
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
            $query = $input->getOption('query');
            $orderyBy = $input->getOption('orderBy');
            if (empty($query)) {
                throw new MissingArgumentException("Query string is not specified");
            }
            $files = $this->getAllFilesBasedOnQuesry($query, $orderyBy);
            if (count($files) > 0) {
                $table = new Table($output);
                $table->setHeaders(['Id', 'File Name', 'Size', 'File Type', 'Created Time']);
                foreach ($files as $file) {
                    $file = $this->getFileDetails($file->getId());
                    $table->addRow([
                        $file->getId(),
                        $file->getName(),
                        StringHelper::humanReadableFileSize($file->getSize()),
                        $file->getMimeType(),
                        $file->createdTime
                    ]);
                }
                $table->render();
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
     * @param $orderBy
     *
     * @return \Google_Service_Drive_DriveFile[]
     */
    protected function getAllFilesBasedOnQuesry($query, $orderBy)
    {
        return $this->getClient()->getAllFiles($query, $orderBy)->getFiles();
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
}
