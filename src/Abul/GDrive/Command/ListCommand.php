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
     *
     */
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
            $gDriveHelper = new GDriveHelper();
            $query = $input->getOption('query');
            $orderyBy = $input->getOption('orderBy');
            if (empty($query)) {
                throw new MissingArgumentException("Query string is not specified");
            }
            $files = $gDriveHelper->getAllFiles($query, $orderyBy)->getFiles();
            if (count($files) > 0) {
                $table = new Table($output);
                $table->setHeaders(['Id', 'File Name', 'Size', 'File Type', 'Created Time']);
                foreach ($files as $file) {
                    $file = $gDriveHelper->getFileDetail($file->getId());
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
}
