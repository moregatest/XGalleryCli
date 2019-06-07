<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Command;

use App\Entity\FlickrContact;
use App\Service\OAuth\Flickr\FlickrClient;
use Doctrine\ORM\EntityManagerInterface;
use XGallery\BaseCommand;

/**
 * Class FlickrCommand
 * @package XGallery\Command
 */
class FlickrCommand extends BaseCommand
{
    /**
     * Default limit number of requests to get photo sizes
     */
    const REST_LIMIT_PHOTOS_SIZE = 150;
    /**
     * Default limit downloads / execute
     */
    const DOWNLOAD_LIMIT = 200;
    /**
     * Number photos to process resize
     */
    const RESIZE_LIMIT = 100;
    const PHOTO_STATUS_DOWNLOADED = 1;
    const PHOTO_STATUS_ALREADY_DOWNLOADED = 2;
    const PHOTO_STATUS_FORCE_REDOWNLOAD = 3;
    const PHOTO_STATUS_SKIP_DOWNLOAD = 4;
    const PHOTO_STATUS_REDOWNLOAD_CORRUPTED = 5;
    const PHOTO_STATUS_LOCAL_CORRUPTED = 6;
    const PHOTO_STATUS_ERROR_NOT_FOUND = -1;
    const PHOTO_STATUS_ERROR_NOT_PHOTO = -2;
    const PHOTO_STATUS_ERROR_DOWNLOAD_FAILED = -3;
    const PHOTO_STATUS_ERROR_REDOWNLOAD_FAILED = -4;
    const PHOTO_STATUS_ERROR_NOT_MATCH_REQUIREMENT = -5;
    const PHOTO_STATUS_ERROR_NOT_FOUND_GET_SIZES = -10;

    const FLICKR_PHOTO_MIN_WIDTH = 800;
    const FLICKR_PHOTO_MIN_HEIGHT = 600;

    /**
     * @var FlickrClient
     */
    protected $client;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * FlickrCommand constructor.
     * @param FlickrClient $client
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(FlickrClient $client, EntityManagerInterface $entityManager)
    {
        $this->client = $client;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    /**
     * @param $nsid
     * @return FlickrContact|object|null
     */
    protected function getContact($nsid)
    {
        return $this->entityManager
            ->getRepository(FlickrContact::class)
            ->find($nsid);
    }
}