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


use App\Service\BasicAuthentication\LinksysClient;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class LinksysCommand
 * @package XGallery\Command
 */
class LinksysCommand extends AbstractCommand
{
    /**
     * @var LinksysClient
     */
    protected $client;

    /**
     * LinksysCommand constructor.
     * @param LinksysClient $client
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(LinksysClient $client, EntityManagerInterface $entityManager)
    {
        $this->client = $client;
        $this->entityManager = $entityManager;

        parent::__construct();
    }
}
