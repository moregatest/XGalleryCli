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

use App\Service\Crawler\BatdongsanCrawler;
use Doctrine\ORM\EntityManagerInterface;
use XGallery\BaseCommand;

/**
 * Class BatdongsanCommand
 * @package XGallery\Command
 */
class BatdongsanCommand extends BaseCommand
{
    /**
     * @var BatdongsanCrawler
     */
    protected $client;

    /**
     * BatdongsanCommand constructor.
     * @param BatdongsanCrawler $client
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(BatdongsanCrawler $client, EntityManagerInterface $entityManager)
    {
        $this->client        = $client;
        $this->entityManager = $entityManager;

        parent::__construct();
    }
}
