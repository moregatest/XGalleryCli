<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\XCity;

use App\Entity\JavIdol;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use XGallery\Command\XCityCommand;

/**
 * Class XCityIdols
 * @package App\Command\XCity
 */
final class XCityIdols extends XCityCommand
{
    /**
     * @var array
     */
    private $urls;

    protected function configure()
    {
        $this->setDescription('Extract XCity idols');

        parent::configure();
    }

    /**
     * @return boolean
     * @throws GuzzleException
     */
    protected function prepareGetProfileUrls()
    {
        $this->urls = $this->client->getProfileLinks();

        return self::PREPARE_SUCCEED;
    }

    /**
     * @return boolean
     * @throws GuzzleException
     */
    protected function processInsertProfiles()
    {
        if (empty($this->urls)) {
            return false;
        }

        $this->io->newLine();
        $this->io->progressStart(count($this->urls));

        foreach ($this->urls as $index => $url) {
            $profile = $this->client->getProfileDetail($url);

            if (!$profile) {
                $this->log('Can not get profile: ' . $url);
                continue;
            }

            $profileEntity = $this->entityManager->getRepository(JavIdol::class)->findOneBy(
                ['xid' => $profile->xid, 'source' => 'xcity']
            );

            if (!$profileEntity) {
                $profileEntity = new JavIdol;
            }

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

            $this->batchInsert($profileEntity, $index);

            $this->io->progressAdvance();
        }

        return true;
    }
}
