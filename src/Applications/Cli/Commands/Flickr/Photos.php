<?php

namespace XGallery\Applications\Cli\Commands\Flickr;

use Doctrine\DBAL\FetchMode;
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;
use XGallery\Database\DatabaseHelper;
use XGallery\Exceptions\Exception;
use XGallery\Helper\MySql;
use XGallery\Utilities\DateTimeHelper;

/**
 * Class Photos
 * @package XGallery\Applications\Commands\Flickr
 */
class Photos extends AbstractCommandFlickr
{
    /**
     * @throws \ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Fetch ALL photos from a contact or by requested NSID');
        $this->options = [
            'nsid' => [
                'description',
                'Only fetch photos from this NSID',
            ],
        ];

        parent::configure();
    }

    /**
     * @return boolean
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function process()
    {
        $this->info('Getting people from database/options...');

        if (!$people = $this->getPeople($this->input->getOption('nsid'))) {
            $this->logNotice('Can not get people from database');

            return false;
        }

        $this->info('Work on nsid: '.$people->nsid);
        $this->info('Fetching photos ...');

        $photos = $this->flickr->flickrPeopleGetAllPhotos($people->nsid);

        $totalPhotos = count($photos);
        $this->info('Found '.$totalPhotos.' photos');
        $this->info("Inserting photos ...");

        $rows = DatabaseHelper::insertRows('xgallery_flickr_photos', $photos);

        if (!$rows) {
            $this->logError('Can not insert photos');

            return false;
        }

        $this->info("Updated ".$rows." photos into contact");

        // Update total photos

        $this->connection->executeUpdate(
            'UPDATE `xgallery_flickr_contacts` SET total_photos = ? WHERE nsid = ?',
            array($totalPhotos, $people->nsid)
        );
        $this->connection->close();

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
            $this->connection->beginTransaction();

            if ($nsid) {
                $query = 'SELECT * FROM `xgallery_flickr_contacts` WHERE `nsid` = ?  ORDER BY `modified` ASC LIMIT 1 FOR UPDATE';
            } else {
                $query = 'SELECT * FROM `xgallery_flickr_contacts` ORDER BY `modified` ASC LIMIT 1 FOR UPDATE';
            }

            $people = $this->connection->executeQuery($query, [$nsid])->fetch(FetchMode::STANDARD_OBJECT);

            if (!$people) {
                $this->connection->rollBack();
                $this->connection->close();

                return false;
            }

            $this->connection->executeUpdate(
                'UPDATE `xgallery_flickr_contacts` SET `modified` = ? WHERE nsid = ?',
                array(DateTimeHelper::toMySql(), $people->nsid)
            );
            $this->connection->commit();
            $this->connection->close();

            return $people;
        } catch (Exception $exception) {
            $this->connection->rollBack();
            $this->connection->close();

            $this->logError($exception->getMessage());

            return false;
        }
    }
}