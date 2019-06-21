<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Flickr;

use App\Entity\FlickrPhoto;
use DateTime;
use Exception;
use stdClass;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use XGallery\FlickrCommand;

/**
 * Class FlickrPhotos
 * @package App\Command\Flickr
 */
final class FlickrPhotos extends FlickrCommand
{
    /**
     * @var object[]
     */
    private $photos;

    /**
     * @var string
     */
    private $nsid;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Fetch & insert photos')
            ->setDefinition(
                new InputDefinition(
                    [
                        new InputOption('nsid', 'id', InputOption::VALUE_OPTIONAL, 'Fetch photos from specific NSID'),
                        new InputOption(
                            'favorites',
                            'fav',
                            InputOption::VALUE_OPTIONAL,
                            'Fetch favorites\' photos from specific NSID',
                            1
                        ),
                        new InputOption(
                            'album',
                            null,
                            InputOption::VALUE_OPTIONAL,
                            'Fetch photos from specific album URL'
                        ),
                        new InputOption(
                            'gallery',
                            null,
                            InputOption::VALUE_OPTIONAL,
                            'Fetch photos from specific gallery URL'
                        ),
                        new InputOption(
                            'photo_ids',
                            'pids',
                            InputOption::VALUE_OPTIONAL,
                            'Fetch photos from specific ids'
                        ),
                    ]
                )
            );

        parent::configure();
    }

    /**
     * Validate options
     *
     * @return boolean|integer
     */
    protected function prepareOptions()
    {
        $this->nsid = $this->client->getNsid($this->getOption('nsid'));

        return self::NEXT_PREPARE;
    }

    /**
     * Fetch photos from specific album/set
     * --album:<URL> is required
     *
     * @return boolean|integer
     */
    protected function preparePhotosFromAlbum()
    {
        $album = $this->getOption('album');

        if (!$album || !filter_var($album, FILTER_VALIDATE_URL)) {
            $this->log('There is no album provided or invalid URL', 'notice');

            return self::NEXT_PREPARE;
        }

        $data = $this->client->getAlbumPhotos($album);

        if (!$data['photos']) {
            $this->log('Can not get photos in album or empty', 'notice');

            return self::NEXT_PREPARE;
        }

        $this->nsid = $data['nsid'];

        foreach ($data['photos'] as $index => $photo) {
            $photo->owner   = $this->nsid;
            $this->photos[] = $photo;
        }

        $this->log(
            'Fetched ' . count($this->photos) . ' photos of NSID: ' . $data['nsid'] . ' in album ' . $data['album']
        );

        return self::SKIP_PREPARE;
    }

    /**
     * Fetch photos from specific gallery
     *
     * @return integer
     */
    protected function preparePhotosFromGallery()
    {
        $gallery = $this->getOption('gallery');

        if (!$gallery) {
            $this->log('There is no gallery provided', 'notice');

            return self::NEXT_PREPARE;
        }

        if (filter_var($gallery, FILTER_VALIDATE_URL)) {
            $gallery = explode('/', trim($gallery, '/'));
            $gallery = end($gallery);
        }

        $this->log('Working on gallery: ' . $gallery);
        $this->photos = $this->client->flickrGalleriesGetAllPhotos($gallery);

        if (!$this->photos || empty($this->photos)) {
            $this->log('Can not get photos in gallery or empty', 'notice');

            return self::NEXT_PREPARE;
        }

        return self::SKIP_PREPARE;
    }

    /**
     * Fetch photos from specific ids
     * --photo_ids=<id,id> is required
     *
     * @return integer
     */
    protected function preparePhotosFromIds()
    {
        $photoIds = $this->getOption('photo_ids');

        if (!$photoIds) {
            return self::NEXT_PREPARE;
        }

        $this->log('Working on specific photos: ' . $photoIds);
        $photos = explode(',', $photoIds);

        /**
         * We won't check database because we assumed when use photo_ids user already want to force it
         */
        foreach ($photos as $photoId) {
            $flickrPhoto = $this->client->flickrPhotosGetInfo($photoId);

            if (!$flickrPhoto) {
                continue;
            }

            $photo           = new stdClass;
            $photo->id       = $photoId;
            $photo->owner    = $flickrPhoto->photo->owner->nsid;
            $photo->secret   = $flickrPhoto->photo->secret;
            $photo->server   = $flickrPhoto->photo->server;
            $photo->farm     = $flickrPhoto->photo->farm;
            $photo->title    = $flickrPhoto->photo->title->_content;
            $photo->ispublic = $flickrPhoto->photo->visibility->ispublic;
            $photo->isfriend = $flickrPhoto->photo->visibility->isfriend;
            $photo->isfamily = $flickrPhoto->photo->visibility->isfamily;

            $this->photos[] = $photo;
        }

        return self::SKIP_PREPARE;
    }

    /**
     * Get photos from NSID
     *
     * @return boolean
     * @throws Exception
     */
    protected function preparePhotosFromDatabase()
    {
        if (!$this->nsid) {
            $this->log('Get NSID from database by ordering');
            /**
             * @TODO Support check if we already reached photos of NSID in database
             */
            $contactEntity = $this->entityManager->getRepository(\App\Entity\FlickrContact::class)->getFreeContact();

            /**
             * @TODO Call flickr:mycontacts to import contacts than process again
             */
            if (!$contactEntity) {
                $this->log('Can not get people from database', 'notice');

                return self::PREPARE_FAILED;
            }

            $contactEntity->setUpdated(new DateTime());
            $this->entityManager->persist($contactEntity);
            $this->entityManager->flush();

            // Update total photos
            $contact = $this->client->flickrPeopleGetInfo($contactEntity->getNsid());

            if ($contact) {
                $contactEntity->setPhotos((int)$contact->person->photos->count->_content);
            }

            $this->entityManager->persist($contactEntity);
            $this->entityManager->flush();

            $this->nsid = $contactEntity->getNsid();
        }

        $this->log('Working on NSID: <options=bold>' . $this->nsid . '</>');
        $this->log('Getting all NSID\' photos ...');
        $this->photos = $this->client->flickrPeopleGetAllPhotos($this->nsid);
        $this->log('Found NSID\' photos: <options=bold>' . count($this->photos) . '</>');

        /**
         * @TODO Support limit
         */

        if ($this->getOption('favorites')) {
            $this->log('Getting all NSID\' favorites photos ...');
            $favoritePhotos = $this->client->getAllFavorities($this->nsid);
            $this->log('Found NSID\' fav photos: <options=bold>' . count($favoritePhotos) . '</>');

            if ($favoritePhotos) {
                $this->photos = array_merge($this->photos, $favoritePhotos);
            }
        }

        return self::PREPARE_SUCCEED;
    }

    /**
     * Insert photos
     *
     * @return boolean
     * @throws Exception
     */
    protected function processInsertPhotos()
    {
        if (!$this->photos || empty($this->photos)) {
            $this->log('There are no photos', 'notice');

            return false;
        }

        $this->photos = array_unique($this->photos, SORT_REGULAR);

        $this->io->newLine();
        $this->io->progressStart(count($this->photos));

        /**
         * @TODO Insert contact if photo owner not found in contacts
         */
        foreach ($this->photos as $index => $photo) {
            $flickrPhoto = $this->entityManager->getRepository(FlickrPhoto::class)->find($photo->id);

            // Photo already exists
            if ($flickrPhoto) {
                $this->io->progressAdvance();
                continue;
            }

            $flickrPhoto = new FlickrPhoto;
            $flickrPhoto->setId($photo->id);
            $flickrPhoto->setOwner($photo->owner);
            $flickrPhoto->setSecret($photo->secret);
            $flickrPhoto->setServer($photo->server);
            $flickrPhoto->setFarm($photo->farm);
            $flickrPhoto->setTitle($photo->title);
            $flickrPhoto->setIspublic($photo->ispublic);
            $flickrPhoto->setIsfriend($photo->isfriend);
            $flickrPhoto->setIsfamily($photo->isfamily);
            $flickrPhoto->setCreated(new DateTime);

            $this->batchInsert($flickrPhoto, $index);

            $this->io->progressAdvance();
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return true;
    }
}
