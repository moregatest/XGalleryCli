<?php

namespace XGallery\Applications\Commands\Flickr;

use Doctrine\DBAL\FetchMode;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use XGallery\Applications\Commands\CommandFlickr;
use XGallery\Exceptions\Exception;
use XGallery\Factory;
use XGallery\Webservices\Services\Flickr;

/**
 * Class Photos
 * @package XGallery\Applications\Commands\Flickr
 */
class Photos extends CommandFlickr
{
    use LockableTrait;

    /**
     *
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('flickr:photos');
        $this->setDescription('Fetch photos from a contact');
        $this->addOption('nsid', null, InputOption::VALUE_OPTIONAL);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $progressBar = new ProgressBar($output, 3);

        $people = $this->getPeople($input);

        $progressBar->advance();

        if (!$people) {
            return;
        }

        /**
         * @var Flickr $flickr
         */
        $flickr = Factory::getServices('Flickr');
        $photos = $flickr->flickrPeopleGetAllPhotos($people->nsid);
        $progressBar->advance();
        $this->insertPhotos($photos);

        $progressBar->finish();

        return;
    }

    /**
     * @param $input
     * @return bool|mixed
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getPeople(InputInterface $input)
    {
        $conn = Factory::getDbo();

        try {
            $conn->beginTransaction();
            $nsid = $input->getOption('nsid');
            $query = 'SELECT * FROM `xgallery_flickr_contacts` ';

            if ($nsid) {
                $query .= 'WHERE `nsid` = ?  ORDER BY `last_fetched` ASC LIMIT 1 FOR UPDATE';
            } else {
                $query .= 'ORDER BY last_fetched ASC LIMIT 1 FOR UPDATE';
            }

            $stmt = $conn->executeQuery(
                $query,
                [$nsid]
            );

            $people = $stmt->fetch(FetchMode::STANDARD_OBJECT);

            if (!$people) {
                $conn->rollBack();

                return false;
            }

            $now = new \DateTime;

            $conn->executeUpdate(
                'UPDATE `xgallery_flickr_contacts` SET last_fetched = ? WHERE nsid = ?',
                array($now->format('Y-m-d H:i:s'), $people->nsid)
            );
            $conn->commit();

            return $people;
        } catch (Exception $exception) {
            $conn->rollBack();

            return false;
        }
    }

    /**
     * @param $photos
     * @return bool
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    private function insertPhotos($photos)
    {
        if (!$photos || empty($photos)) {
            return false;
        }

        $connection = Factory::getDbo();
        $query = 'INSERT INTO `xgallery_flickr_photos`';

        // Columns
        $query .= '(';
        $onDuplicateQuery = [];
        $columnNames = array_keys(get_object_vars($photos[0]));

        // Bind column names
        foreach ($columnNames as $columnName) {
            $query .= '`' . $columnName . '`,';
            $onDuplicateQuery[] = '`' . $columnName . '`=' . ' VALUES(`' . $columnName . '`)';
        }

        $query = rtrim($query, ',') . ')';
        $query .= ' VALUES';

        $bindKeys = [];

        foreach ($photos as $index => $photo) {

            $query .= ' (';
            foreach ($columnNames as $columnName) {
                $columnId = 'value_' . uniqid();
                $query .= ':' . $columnId . ',';
                $bindKeys[$index][$columnId] = isset($photo->{$columnName}) ? $photo->{$columnName} : NULL;
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