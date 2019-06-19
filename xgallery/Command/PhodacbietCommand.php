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


use App\Service\Crawler\Forums\PhodacbietCrawler;
use Doctrine\ORM\EntityManagerInterface;
use XGallery\BaseCommand;

class PhodacbietCommand extends BaseCommand
{
    /**
     * @var PhodacbietCrawler
     */
    protected $client;

    /**
     * PhodacbietCommand constructor.
     * @param PhodacbietCrawler $client
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(PhodacbietCrawler $client, EntityManagerInterface $entityManager)
    {
        $this->client        = $client;
        $this->entityManager = $entityManager;

        parent::__construct();
    }
}
