<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\XCity;

use App\Command\CrawlerCommand;
use App\Entity\JavIdol;
use App\Service\Crawler\XCityCrawler;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class XCityIdols
 * @package App\Command\XCity
 */
final class XCityIdols extends CrawlerCommand
{
    /**
     * @var XCityCrawler
     */
    private $client;

    /**
     * @var integer
     */
    private $idols = 0;

    /**
     *
     */
    protected function configure()
    {
        $this->setDescription('Extract ALL XCity idols');

        parent::configure();
    }

    /**
     * @return boolean
     * @throws GuzzleException
     */
    protected function processFetchProfiles()
    {
        $this->io->newLine();
        $this->client = $this->getClient('XCity');
        $this->client->getAllProfileLinks(
            function ($pages) {
                $this->io->progressStart(array_sum($pages));
            },
            function ($links) {

                if (!$links || empty($links)) {
                    $this->io->progressAdvance();

                    return;
                }

                foreach ($links as $link) {
                    $this->logInfo('Processing ' . $link);

                    /**
                     * @TODO Break down get detail for another process
                     */
                    $profile = $this->client->getProfileDetail($link);

                    $profileEntity = $this->entityManager->getRepository(JavIdol::class)->findOneBy(
                        ['xid' => $profile->xid, 'source' => 'xcity']
                    );

                    if ($profileEntity) {
                        $this->logNotice('Profile already exists ' . $link);

                        continue;
                    }

                    $profileEntity = new JavIdol;

                    $profileEntity->setSource('xcity');
                    $profileEntity->setXId($profile->xid);
                    $profileEntity->setBirthday($profile->birthday ? new DateTime($profile->birthday) : null);
                    $profileEntity->setBloodType($profile->blood_type ?? null);
                    $profileEntity->setCity($profile->city ?? null);
                    $profileEntity->setHeight($profile->height ?? null);

                    $profileEntity->setName($profile->name ?? '');
                    $profileEntity->setFavorite($profile->favorite ?? null);
                    $profileEntity->setHeight($profile->height ?? null);

                    $profileEntity->setBreast($profile->breast ?? null);
                    $profileEntity->setWaist($profile->waist ?? null);
                    $profileEntity->setHips($profile->hips ?? null);

                    $this->entityManager->persist($profileEntity);
                    $this->idols++;
                }

                $this->entityManager->flush();
                $this->io->progressAdvance();
            }
        );

        $this->log('Total idols ' . $this->idols);

        return self::PREPARE_SUCCEED;
    }
}
