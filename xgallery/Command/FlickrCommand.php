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

/**
 * Class FlickrCommand
 * @package XGallery\Command
 */
class FlickrCommand extends AbstractCommand
{
    /**
     * @var FlickrClient
     */
    protected $client;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * FlickrContact constructor.
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
