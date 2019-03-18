<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands\Flickr;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use ReflectionException;
use stdClass;
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
     * @var stdClass
     */
    private $people;

    /**
     * @var array
     */
    private $photos = [];

    /**
     * @var integer
     */
    private $totalPhotos = 0;

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
     * @throws DBALException
     */
    protected function prepare()
    {
        parent::prepare();

        if (!$this->loadPeople()) {
            return false;
        }

        return true;
    }

    /**
     * @return boolean
     */
    protected function loadPeople()
    {
        try {
            $nsid = $this->input->getOption('nsid');

            if ($nsid) {
                $query = 'SELECT * FROM `xgallery_flickr_contacts` WHERE `nsid` = ?  ORDER BY `modified` ASC LIMIT 1 FOR UPDATE';
            } else {
                $query = 'SELECT * FROM `xgallery_flickr_contacts` ORDER BY `modified` ASC LIMIT 1 FOR UPDATE';
            }

            $this->people = $this->connection->executeQuery($query, [$nsid])->fetch(FetchMode::STANDARD_OBJECT);

            /**
             * @TODO If NSID not found in database then insert new one
             */
            if (!$this->people) {
                $this->logNotice('Can not get people from database');

                return false;
            }

            $this->info('Found people: '.$this->people->nsid, [], true);

            $this->connection->executeUpdate(
                'UPDATE `xgallery_flickr_contacts` SET `modified` = ? WHERE nsid = ?',
                array(DateTimeHelper::toMySql(), $this->people->nsid)
            );

            return true;
        } catch (DBALException $exception) {
            $this->logError($exception->getMessage());
        }

        return false;
    }

    /**
     * @param array $steps
     * @return boolean
     */
    protected function process($steps = [])
    {
        return parent::process(
            [
                'fetchPhotos',
                'insertPhotos',
                'updateTotal',
            ]
        );
    }

    /**
     * @return array|boolean
     */
    protected function fetchPhotos()
    {
        if ($this->people === null || !$this->people->nsid) {
            return false;
        }

        $this->info('Working on: '.$this->people->nsid);
        $this->photos = $this->flickr->flickrPeopleGetAllPhotos($this->people->nsid);

        if (!$this->photos || empty($this->photos)) {
            $this->logNotice('There are not photos');

            return false;
        }

        $this->totalPhotos = count($this->photos);
        $this->info('Found: '.$this->totalPhotos.' photos');

        return true;
    }

    /**
     * @return boolean
     * @throws DBALException
     */
    protected function insertPhotos()
    {
        $rows = DatabaseHelper::insertRows('xgallery_flickr_photos', $this->photos);

        if ($rows === false) {
            $this->logError('Can not insert photos', error_get_last());

            return false;
        }

        $this->info("Updated ".$rows." photos into contact");

        return true;
    }

    /**
     * @return boolean
     */
    protected function updateTotal()
    {
        try {
            // Update total photos
            $this->connection->executeUpdate(
                'UPDATE `xgallery_flickr_contacts` SET total_photos = ? WHERE nsid = ?',
                array($this->totalPhotos, $this->people->nsid)
            );

            $this->info('Updated total photos of contact: '.$this->totalPhotos);

            return true;
        } catch (DBALException $exception) {
            $this->logError($exception->getMessage());

        }

        return false;
    }
}