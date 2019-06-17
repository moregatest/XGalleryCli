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

use App\Entity\Nct;
use App\Service\Crawler\NctCrawler;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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
        $this->client        = $client;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    /**
     * @param $song
     * @param $index
     * @throws Exception
     */
    protected function insertEntity($song, $index)
    {
        $nctEntity = $this->entityManager
            ->getRepository(Nct::class)
            ->findOneBy(['url' => $song['href']]);

        if ($nctEntity !== null) {
            $this->io->progressAdvance();

            return;
        }

        $nctEntity = new Nct;
        $nctEntity->setCreated(new DateTime);
        $nctEntity->setUrl($song['href']);
        $nctEntity->setTitle($song['title']);
        $this->entityManager->persist($nctEntity);

        $this->batchInsert($nctEntity, $index);

        $this->getProcess(['nct:download', '--url=' . $song['href']])->run();

        $this->io->progressAdvance();
    }
}
