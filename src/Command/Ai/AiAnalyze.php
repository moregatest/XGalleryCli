<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Ai;

use App\Command\BaseCommand;
use App\Entity\JavMyFavorite;
use App\Entity\JavMyFavoriteData;
use App\Service\Crawler\R18Crawler;
use Doctrine\DBAL\DBALException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;

/**
 * Class AiAnalyze
 * @package App\Command\Ai
 */
class AiAnalyze extends BaseCommand
{
    /**
     * @var array
     */
    private $data;

    /**
     * @return boolean
     * @throws DBALException
     */
    protected function prepareGetData()
    {
        $this->data = $this->entityManager->getRepository(JavMyFavorite::class)->findAll();
        $this->entityManager->getConnection()->executeQuery('TRUNCATE `jav_my_favorites_data`');

        return self::PREPARE_SUCCEED;
    }

    /**
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    protected function processExtractData()
    {
        $r18 = new R18Crawler;

        foreach ($this->data as $item) {

            $items = $r18->getSearchLinks($item->getItemNumber());

            if (empty($items)) {
                continue;
            }

            foreach ($items as $item) {
                if (!$item) {
                    continue;
                }
                $detail = $r18->getDetail($item);

                if (!$detail) {
                    continue;
                }

                foreach ($detail->categories as $category) {
                    $this->addData($category, 'genre');
                }

                foreach ($detail->actress as $actress) {
                    $this->addData($actress, 'actress');
                }

                $this->addData($detail->director, 'director');
                $this->addData($detail->studio, 'studio');
                $this->addData($detail->label, 'label');
            }

            $this->entityManager->flush();

            return true;
        }
    }

    /**
     * @param $name
     * @param $type
     * @return boolean
     */
    private function addData($name, $type)
    {
        if (empty($name) || empty($type)) {
            return false;
        }

        $entity = new JavMyFavoriteData;
        $entity->setName($name);
        $entity->setType($type);
        $this->entityManager->persist($entity);
    }
}
