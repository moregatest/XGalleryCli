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

use App\Service\Crawler\Xiuren\XiurenOrgCrawler;
use Doctrine\ORM\EntityManagerInterface;
use XGallery\BaseCommand;

class XiurenOrgCommand extends BaseCommand
{
    /**
     * @var XiurenOrgCrawler
     */
    protected $client;

    /**
     * XiurenOrgCommand constructor.
     * @param XiurenOrgCrawler $client
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(XiurenOrgCrawler $client, EntityManagerInterface $entityManager)
    {
        $this->client        = $client;
        $this->entityManager = $entityManager;

        parent::__construct();
    }
}
