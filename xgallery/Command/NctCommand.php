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

use App\Service\Crawler\NctCrawler;
use Doctrine\ORM\EntityManagerInterface;
use XGallery\BaseCommand;

/**
 * Class NctCommand
 * @package XGallery\Command
 */
class NctCommand extends BaseCommand
{
    /**
     * @var NctCrawler
     */
    protected $client;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * NctCommand constructor.
     * @param NctCrawler $client
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(NctCrawler $client, EntityManagerInterface $entityManager)
    {
        $this->client = $client;
        $this->entityManager = $entityManager;

        parent::__construct();
    }
}
