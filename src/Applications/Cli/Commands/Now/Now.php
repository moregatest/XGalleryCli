<?php

namespace XGallery\Applications\Cli\Commands\Now;

use XGallery\Applications\Cli\Commands\AbstractCommandNow;

/**
 * Class Now
 * @package XGallery\Applications\Cli\Commands\Now
 */
final class Now extends AbstractCommandNow
{
    /**
     * @var object
     */
    protected $metadata;

    /**
     * @var object
     */
    protected $deliveryMetadata;

    /**
     * configure
     *
     * @throws \ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Generate base data from NOW');

        parent::configure();
    }

    /**
     * prepareGetMetadata
     * @return boolean
     */
    protected function prepareGetMetadata()
    {
        // Tablenow
        $this->metadata = $this->now->getMetadata();
        // DeliveryNow
        $this->deliveryMetadata = $this->now->getDeliveryNowMetadata();

        if (!$this->metadata || !$this->deliveryMetadata) {
            return self::PREPARE_FAILED;
        }

        return self::PREPARE_SUCCEED;
    }

    /**
     * prepareCleanupTables
     * @return boolean
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function prepareCleanupTables()
    {
        $tables = [
            'xgallery_now_cities',
            'xgallery_now_districts',
            'xgallery_now_cuisines',
            'xgallery_now_categories',
            'xgallery_now_restaurant_sort_types',
        ];

        foreach ($tables as $table) {
            $this->model->truncate($table);
        }

        return self::PREPARE_SUCCEED;
    }

    /**
     * processInsertDatabase
     *
     * @return boolean
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function processTableNow()
    {
        foreach ($this->metadata->province as $city) {

            $this->log('Processing city: '.$city->name.' ...');
            $this->model->insertCity($city);

            foreach ($city->district as $district) {
                $this->model->insertDistrict($district);
            }
        }

        foreach ($this->metadata->cuisine as $cuisine) {
            $this->model->insertCuisines($cuisine);
        }

        return true;
    }

    /**
     * processDeliveryNow
     *
     * @return boolean
     */
    protected function processDeliveryNow()
    {
        foreach ($this->deliveryMetadata->country->delivery_categories as $category) {
            $this->model->insertDeliveryCategory($category);
        }

        foreach ($this->deliveryMetadata->country->restaurant_sort_type as $sortType) {
            $this->model->insertSortTypes($sortType);
        }

        foreach ($this->deliveryMetadata->country->delivery_sort_options as $sortType) {
            $this->model->insertSortTypes($sortType);
        }

        return true;
    }
}