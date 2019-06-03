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

use App\Service\Crawler;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class BdsCommand
 * @package XGallery\Command
 */
class BdsCommand extends AbstractCommand
{
    /**
     * @var Crawler\BdsCrawler
     */
    protected $client;

    /**
     * BdsCommand constructor.
     *
     * @param Crawler\BdsCrawler $client
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(Crawler\BdsCrawler $client, EntityManagerInterface $entityManager)
    {
        $this->client = $client;
        $this->entityManager = $entityManager;

        parent::__construct();
    }
}
