<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Now;

use App\Entity\DeliveryNowCancelOption;
use App\Entity\DeliveryNowCategory;
use App\Entity\DeliveryNowCity;
use App\Entity\DeliveryNowDistrict;
use App\Entity\DeliveryNowService;
use XGallery\Command\NowCommand;

/**
 * Class DeliveryNow
 * @package App\Command\Now
 */
class DeliveryNow extends NowCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('delivery:now');

        parent::configure();
    }

    protected function prepareImport()
    {
        $deliveryNowMeta = $this->client->getDeliveryNowMetadata();

        $this->importCancelOptions($deliveryNowMeta->country->cancel_options);

        $this->importServices($deliveryNowMeta->country->now_services);
        $this->importServices($deliveryNowMeta->country->now_services);
        $this->importServices($deliveryNowMeta->country->booking_services);

        //$this->importCategories($deliveryNowMeta->country->categories);

        $this->importCities($deliveryNowMeta->country->cities);
    }

    /**
     * @param $cancelOptions
     */
    private function importCancelOptions($cancelOptions)
    {
        foreach ($cancelOptions as $cancelOption) {
            $cancelOptionEntity = $this->entityManager->getRepository(DeliveryNowCancelOption::class)->find(
                $cancelOption->id
            );

            if (!$cancelOptionEntity) {
                $cancelOptionEntity = new DeliveryNowCancelOption;
                $cancelOptionEntity->setId($cancelOption->id);
            }

            $cancelOptionEntity->setName($cancelOption->name);

            $this->entityManager->persist($cancelOptionEntity);
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
    }

    /**
     * @param $services
     */
    private function importServices($services)
    {
        foreach ($services as $service) {
            $serviceEntity = $this->entityManager->getRepository(DeliveryNowService::class)->find(
                $service->id
            );

            if (!$serviceEntity) {
                $serviceEntity = new DeliveryNowService;
                $serviceEntity->setId($service->id);
            }

            $serviceEntity->setName($service->name);
            $serviceEntity->setCallCenter($service->call_center);
            $serviceEntity->setCode($service->code);
            $serviceEntity->setUrl($service->url);

            $this->entityManager->persist($serviceEntity);
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
    }

    /**
     * @param $categories
     */
    private function importCategories($categories)
    {
        foreach ($categories as $category) {
            $existEntity = $categoryEntity = $this->entityManager->getRepository(DeliveryNowCategory::class)->find(
                $category->id
            );

            if (!$categoryEntity) {
                $categoryEntity = new DeliveryNowCategory;
                $categoryEntity->setId($category->id);
            }

            $categoryEntity->setName($category->name);
            $categoryEntity->setCode($category->code);
            $categoryEntity->setParentCategoryId($category->parent_category_id);
            $categoryEntity->setUrlRewriteName($category->url_rewrite_name);

            if (!empty($category->categories)) {
                $this->importCategories($category->categories);
            }

            if ($existEntity) {
                $this->entityManager->merge($categoryEntity);
            } else {
                $this->entityManager->persist($categoryEntity);
            }

            $this->entityManager->flush();
            $this->entityManager->clear();
        }
    }

    /**
     * @param $cities
     */
    private function importCities($cities)
    {
        foreach ($cities as $index => $city) {
            $cityEntity = $this->entityManager->getRepository(DeliveryNowCity::class)->find($city->id);

            if (!$cityEntity) {
                $cityEntity = new DeliveryNowCity;
                $cityEntity->setId($city->id);
            }

            $cityEntity->setName($city->name);
            $cityEntity->setLatitude($city->latitude);
            $cityEntity->setLongitude($city->longitude);

            $this->batchInsert($cityEntity, $index);

            /**
             * @TODO We must sure batchInsert succeed before import districts
             */
            $this->importDistricts($city->districts, $city->id);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * @param $districts
     * @param int|null $cityId
     */
    private function importDistricts($districts, ?int $cityId)
    {
        foreach ($districts as $index => $district) {
            $districtEntity = $this->entityManager->getRepository(DeliveryNowDistrict::class)->find(
                $district->district_id
            );

            if (!$districtEntity) {
                $districtEntity = new DeliveryNowDistrict;
                $districtEntity->setId($district->district_id);
            }

            $districtEntity->setName($district->name);
            $districtEntity->setLatitude($district->latitude);
            $districtEntity->setLongitude($district->longitude);
            $districtEntity->setIsHasDelivery($district->is_has_delivery);
            $districtEntity->setProvinceId($cityId);

            $this->batchInsert($districtEntity, $index);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
