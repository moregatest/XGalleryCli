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
use XGallery\Applications\Cli\Commands\AbstractCommandFlickr;
use XGallery\Defines\DefinesFlickr;

/**
 * Class PhotosSize
 * @package XGallery\Applications\Commands\Flickr
 */
class PhotosSize extends AbstractCommandFlickr
{

    /**
     * @var array
     */
    private $photos;

    /**
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Fetch photos size');
        $this->options = [
            'nsid' => [
                'description' => 'Only fetch photos from this NSID',
            ],
            'photo_ids' => [
                'description' => 'Request specific photo by ids',
                'default' => null,
            ],
            'limit' => [
                'description' => 'Number of photos will be used for get sizes',
                'default' => DefinesFlickr::REST_LIMIT_PHOTOS_SIZE,
            ],
            'all' => [
                'description' => 'Fetch all photos from an NSID',
                'default' => false,
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

        if (!$this->loadPhotos()) {
            return false;
        }

        return true;
    }

    /**
     * @param array $steps
     * @return boolean
     */
    protected function process($steps = [])
    {
        return parent::process(
            [
                'fetchSizes',
            ]
        );
    }

    /**
     * @return boolean|mixed[]
     */
    protected function loadPhotos()
    {
        try {

            if ($this->input->getOption('photo_ids')) {
                $this->photos = explode(',', $this->input->getOption('photo_ids'));
                $this->info('Working on '.count($this->photos).' photos', [], true);

                // Fetch photos for inserting
                foreach ($this->photos as $photoId) {
                    $photo = $this->flickr->flickrPhotosGetInfo($photoId);

                    $query = ' INSERT IGNORE INTO xgallery_flickr_photos '
                        .'(`id`, `owner`, `secret`, `server`, `farm`, `title`, `ispublic`, `isfriend`, `isfamily` ) '
                        .' VALUES (?,?,?,?,?,?,?,?,?)';

                    $this->connection->executeQuery(
                        $query,
                        [
                            $photoId,
                            $photo->photo->owner->nsid,
                            $photo->photo->secret,
                            $photo->photo->server,
                            $photo->photo->farm,
                            $photo->photo->title->_content,
                            $photo->photo->visibility->ispublic,
                            $photo->photo->visibility->isfriend,
                            $photo->photo->visibility->isfamily,
                        ]
                    );
                }

                return true;
            }

            $this->info('Getting '.$this->input->getOption('limit').' photos ...');

            $query = 'SELECT `id` FROM `xgallery_flickr_photos` WHERE (`status` = 0 OR `status` IS NULL) AND `params` IS NULL ';

            $nsid = $this->input->getOption('nsid');

            // Specific NSID
            if ($nsid !== null) {
                $this->info('Work on NSID: '.$nsid);
                $query .= ' AND owner = ?';
            }

            if ($nsid === null || ($nsid && !$this->input->getOption('all'))) {
                $query .= 'LIMIT '.(int)$this->input->getOption('limit').' FOR UPDATE';
            } else {
                $this->info('Getting ALL');
            }

            $this->photos = $this->connection->executeQuery($query, [$nsid])->fetchAll(FetchMode::COLUMN);

            if (!$this->photos || empty($this->photos)) {
                $this->logNotice('Can not get photos from database or no photos found');
                $this->output->writeln('');

                return false;
            }

            if (count($this->photos) > 1000) {
                $this->logNotice('Over API. Reduced to 1000');
                $this->photos = array_slice($this->photos, 0, 1000);
            }

            $this->info('Found '.count($this->photos).' photos', [], true);

            return true;
        } catch (DBALException $exception) {

            $this->logError($exception->getMessage());
        }

        return false;
    }

    /**
     * @return boolean
     */
    protected function fetchSizes()
    {
        if (!$this->photos || empty($this->photos)) {
            return false;
        }

        $failed = 0;

        foreach ($this->photos as $photoId) {
            $photoSize = $this->flickr->flickrPhotosSizes($photoId);
            $this->info('Fetching '.$photoId);

            if (!$photoSize) {
                /**
                 * @TODO Update photo status to prevent fetch it again in future
                 */
                $this->logNotice('Something wrong on photo_id: '.$photoId);
                $this->output->write(': Failed');
                $failed++;

                continue;
            }

            $this->output->write(': Succeed');

            try {

                $this->connection->executeUpdate(
                    'UPDATE `xgallery_flickr_photos` SET `params` = ? WHERE `id` = ?',
                    [json_encode($photoSize->sizes->size), $photoId]
                );

            } catch (DBALException $exception) {
                $this->logError($exception->getMessage());
            }
        }

        $this->info('Failed count: '.$failed);

        return true;
    }
}