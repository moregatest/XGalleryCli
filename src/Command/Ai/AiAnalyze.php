<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Ai;


use App\Entity\JavMyFavorite;
use App\Entity\JavMyFavoriteData;
use App\Service\Crawler\R18Crawler;
use XGallery\BaseCommand;

class AiAnalyze extends BaseCommand
{
    private $data;

    protected function prepareGetData()
    {
        $this->data = $this->entityManager->getRepository(JavMyFavorite::class)->findAll();

        return self::PREPARE_SUCCEED;
    }

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
                    $entity = new JavMyFavoriteData;
                    $entity->setName($category);
                    $entity->setType('genre');
                    $this->entityManager->persist($entity);
                }

                foreach ($detail->actress as $actress) {
                    $entity = new JavMyFavoriteData;
                    $entity->setName($actress);
                    $entity->setType('actress');
                    $this->entityManager->persist($entity);
                }

                $entity = new JavMyFavoriteData;
                $entity->setName($detail->director);
                $entity->setType('director');
                $this->entityManager->persist($entity);

                $entity = new JavMyFavoriteData;
                $entity->setName($detail->studio);
                $entity->setType('studio');
                $this->entityManager->persist($entity);

                $entity = new JavMyFavoriteData;
                $entity->setName($detail->label);
                $entity->setType('label');
                $this->entityManager->persist($entity);
            }

            $this->entityManager->flush();
        }
    }
}
