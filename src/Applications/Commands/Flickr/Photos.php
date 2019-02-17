<?php

namespace XGallery\Applications\Commands\Flickr;

use Doctrine\DBAL\FetchMode;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use XGallery\Applications\Commands\CommandFlickr;
use XGallery\Exceptions\Exception;
use XGallery\Factory;
use XGallery\Helper\MySql;

/**
 * Class Photos
 * @package XGallery\Applications\Commands\Flickr
 */
class Photos extends CommandFlickr
{
    /**
     * @throws \ReflectionException
     */
    protected function configure()
    {
        $this->description = 'Fetch ALL photos from a contact or by requested NSID';
        $this->options = [
            'nsid' => [],
        ];

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool|int|null
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $progressBar = new ProgressBar($output, 4);

        $output->writeln('Getting people from database/options...');

        $people = $this->getPeople($input->getOption('nsid'));

        if (!$people) {
            $output->writeln('Can not get people');

            return false;
        }

        $progressBar->advance();
        $output->writeln("\nWork on people nsid: ".$people->nsid." ...");
        $output->writeln('Fetching photos ...');

        $photos = $this->flickr->flickrPeopleGetAllPhotos($people->nsid);
        $progressBar->advance();

        $output->writeln("\nFound ".count($photos)." photos");
        $output->writeln("Inserting photos ...");

        $progressBar->advance();
        $this->insertRows('xgallery_flickr_photos', $photos);

        $output->writeln("\nUpdate total photos into contact");
        $progressBar->advance();

        // Update total photos
        $connection = Factory::getDbo();
        $connection->executeUpdate(
            'UPDATE `xgallery_flickr_contacts` SET total_photos = ? WHERE nsid = ?',
            array(count($photos), $people->nsid)
        );
        $connection->close();

        $progressBar->finish();
        $this->complete($output);

        return true;
    }

    /**
     * @param $nsid
     * @return bool|mixed
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getPeople($nsid)
    {
        try {
            $connection = Factory::getDbo();
            $connection->beginTransaction();

            if ($nsid) {
                $query = 'SELECT * FROM `xgallery_flickr_contacts` WHERE `nsid` = ?  ORDER BY `last_fetched` ASC LIMIT 1 FOR UPDATE';
            } else {
                $query = 'SELECT * FROM `xgallery_flickr_contacts` ORDER BY last_fetched ASC LIMIT 1 FOR UPDATE';
            }

            $stmt = $connection->executeQuery(
                $query,
                [$nsid]
            );

            $people = $stmt->fetch(FetchMode::STANDARD_OBJECT);

            if (!$people) {
                $connection->rollBack();
                $connection->close();

                return false;
            }

            $connection->executeUpdate(
                'UPDATE `xgallery_flickr_contacts` SET last_fetched = ? WHERE nsid = ?',
                array(MySql::getCurrentDateTime(), $people->nsid)
            );
            $connection->commit();
            $connection->close();

            return $people;
        } catch (Exception $exception) {
            $connection->rollBack();
            $connection->close();

            return false;
        }
    }
}