<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands\Flickr;

use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use ReflectionException;
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;
use XGallery\Database\DatabaseHelper;
use XGallery\Utilities\DateTimeHelper;

/**
 * Class Photos
 * @package XGallery\Applications\Commands\Flickr
 */
class Photos extends AbstractCommandFlickr
{
    /**
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Fetch ALL photos from a contact or by requested NSID');
        $this->options = [
            'nsid' => [
                'description' => 'Only fetch photos from this NSID',
            ],
        ];

        parent::configure();
    }

    /**
     * @return boolean
     * @throws ConnectionException
     * @throws DBALException
     */
    protected function process()
    {
        $this->info('Getting people from database/options...', [], true);
        $this->progressBar->start(4);

        if (!$people = $this->getPeople()) {
            $this->logNotice('Can not get people from database');

            return false;
        }

        $this->progressBar->advance();

        $photos = $this->getPhotos($people->nsid);
        $this->progressBar->advance();
        $totalPhotos = count($photos);

        $this->info('Found '.$totalPhotos.' photos');
        $this->info("Inserting photos ...");

        $rows = DatabaseHelper::insertRows('xgallery_flickr_photos', $photos);
        $this->progressBar->advance();

        if ($rows === false) {
            $this->logError('Can not insert photos', error_get_last());
            $this->progressBar->finish();

            return false;
        }

        $this->info("Updated ".$rows." photos into contact", [], true);

        // Update total photos
        $this->connection->executeUpdate(
            'UPDATE `xgallery_flickr_contacts` SET total_photos = ? WHERE nsid = ?',
            array($totalPhotos, $people->nsid)
        );
        $this->connection->close();

        $this->progressBar->finish();
        $this->info('Updated total photos of contact: '.$totalPhotos);

        return true;
    }

    /**
     * @return boolean|mixed
     * @throws ConnectionException
     */
    private function getPeople()
    {
        try {
            $nsid = $this->input->getOption('nsid');
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
        } catch (DBALException $exception) {
            $this->connection->rollBack();
            $this->connection->close();

            $this->logError($exception->getMessage());

            return false;
        }
    }

    /**
     * @param integer $nsid
     * @return array|boolean
     */
    private function getPhotos($nsid)
    {
        if (!$nsid) {
            return false;
        }

        $this->info('Work on nsid: '.$nsid);
        $this->info('Fetching photos ...');

        return $this->flickr->flickrPeopleGetAllPhotos($nsid);
    }
}