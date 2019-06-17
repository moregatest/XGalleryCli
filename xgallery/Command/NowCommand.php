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

use App\Service\NowClient;
use Doctrine\ORM\EntityManagerInterface;
use XGallery\BaseCommand;

/**
 * Class NowCommand
 * @package XGallery\Command
 */
class NowCommand extends BaseCommand
{
    /**
     * @var NowClient
     */
    protected $client;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * NowCommand constructor.
     *
     * @param NowClient $client
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(NowClient $client, EntityManagerInterface $entityManager)
    {
        $this->client        = $client;
        $this->entityManager = $entityManager;

        parent::__construct();
    }
}
