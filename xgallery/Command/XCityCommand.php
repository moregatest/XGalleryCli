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


use App\Service\Crawler\XCityCrawler;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class XCityCommand
 * @package XGallery\Command
 */
class XCityCommand extends AbstractCommand
{
    /**
     * @var XCityCrawler
     */
    protected $client;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * XCityCommand constructor.
     * @param XCityCrawler $client
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(XCityCrawler $client, EntityManagerInterface $entityManager)
    {
        $this->client = $client;
        $this->entityManager = $entityManager;

        parent::__construct();
    }
}
