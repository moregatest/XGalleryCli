<?php

namespace XGallery\Applications\Commands\Flickr;

use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use XGallery\Applications\Commands\CommandFlickr;
use XGallery\Exceptions\Exception;
use XGallery\Factory;

/**
 * Class Contacts
 *
 * @package XGallery\Applications\Commands\Flickr
 */
class Contacts extends CommandFlickr
{
    use LockableTrait;

    /**
     *
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('flickr:contacts');
        $this->setDescription('Fetch contacts from Flickr');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return boolean
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $progressBar = new ProgressBar($output, 2);
        $contacts = $this->getContacts();
        $progressBar->advance();

        $output->writeln('Total contacts: ' . count($contacts));

        if (empty($contacts)) {
            return false;
        }

        $contacts = array_slice($contacts, 0, 10);

        $this->insertContacts($contacts, $progressBar);

        $progressBar->finish();

        return;
    }

    /**
     * @param $contacts
     * @return bool
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    private function insertContacts($contacts)
    {
        $connection = Factory::getDbo();
        $query = 'INSERT INTO `xgallery_flickr_contacts`';

        // Columns
        $query .= '(';
        $onDuplicateQuery = [];
        $columnNames = array_keys(get_object_vars($contacts[0]));

        // Bind column names
        foreach ($columnNames as $columnName) {
            $query .= '`' . $columnName . '`,';
            $onDuplicateQuery[] = '`' . $columnName . '`=' . ' VALUES(`' . $columnName . '`)';
        }

        $query = rtrim($query, ',') . ')';
        $query .= ' VALUES';

        $bindKeys = [];

        foreach ($contacts as $index => $contact) {
            $query .= ' (';
            foreach ($columnNames as $columnName) {
                $columnId = 'value_' . uniqid();
                $query .= ':' . $columnId . ',';
                $bindKeys[$index][$columnId] = isset($contact->{$columnName}) ? $contact->{$columnName} : NULL;
            }

            $query = rtrim($query, ',') . '),';
        }

        $query = rtrim($query, ',');
        $query .= ' ON DUPLICATE KEY UPDATE ' . implode(',', $onDuplicateQuery) . ';';

        $connection->beginTransaction();
        $prepare = $connection->prepare($query);

        // Bind values
        foreach ($bindKeys as $index => $columns) {
            foreach ($columns as $columnId => $value) {
                $prepare->bindValue(':' . $columnId, $value);
            }
        }

        try {
            $prepare->execute();
            $connection->commit();

            return true;
        } catch (Exception $exception) {
            $connection->rollBack();

            return false;
        }
    }

}