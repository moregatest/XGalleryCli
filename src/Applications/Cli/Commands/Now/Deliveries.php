<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands\Now;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Helper\ProgressBar;
use XGallery\Applications\Cli\Commands\AbstractCommandNow;
use XGallery\Factory;
use XGallery\Model\BaseModel;

/**
 * Class Search
 * @package XGallery\Applications\Cli\Commands\Now
 */
class Deliveries extends AbstractCommandNow
{
    protected $districts = [];
    protected $cuisines = [];
    protected $deliveryIds = [];
    protected $deliveryData = ['deliveries' => [], 'categories_xref' => [], 'branchs' => [], 'promotions' => []];

    const SG_CITY_ID = 217;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * configure
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \ReflectionException
     */
    protected function configure()
    {
        $this->connection = Factory::getConnection();

        parent::configure();
    }

    /**
     * Get districts & cuisines
     *
     * @return boolean
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function prepareGetDatabase()
    {
        $this->districts = $this->model->getDistricts();
        $this->cuisines  = $this->model->getCuisines();

        $cuisines = [];

        foreach ($this->cuisines as $cuisine) {
            $cuisines[$cuisine->name] = $cuisine;
        }

        $this->cuisines = $cuisines;

        $this->log('Found '.count($this->districts).' districts');
        $this->log('Found '.count($this->cuisines).' cuisines');

        return self::PREPARE_SUCCEED;
    }

    /**
     * Try to get as much as possible delivery ids
     *
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function prepareGetDeliveryIds()
    {
        $sorts = $this->model->getSorts();

        foreach ($this->districts as $district) {
            $this->log('Search on district: '.$district->name);

            foreach ($sorts as $sort) {
                $this->log('Search type: '.$sort->name);
                $data        = [
                    'category_group' => 1,
                    'city_id' => self::SG_CITY_ID, // SG
                    'delivery_only' => true,
                    'district_ids' => [(int)$district->id],
                    'foody_services' => [1],
                    'keyword' => '',
                    'sort_type' => (int)$sort->id,
                ];
                $deliveryIds = $this->now->searchDeliveryIds($data);

                if (!$deliveryIds) {
                    $this->log('Can not get delivery IDs', 'notice', $data);
                    continue;
                }

                $this->deliveryIds = array_merge($this->deliveryIds, $deliveryIds);
            }
        }

        $this->deliveryIds = array_unique($this->deliveryIds);
        $this->log('Found '.count($this->deliveryIds).' deliveries');

        return self::PREPARE_SUCCEED;
    }

    /**
     * prepareCleanupTables
     *
     * @return boolean
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function prepareCleanupTables()
    {
        $tables = [
            '`xgallery_now_deliveries`',
            '`xgallery_now_brands`',
            '`xgallery_now_categories_xref`',
            '`xgallery_now_cuisines_xref`',
            '`xgallery_now_promotions`',
        ];

        foreach ($tables as $table) {
            $this->connection->executeQuery('TRUNCATE `soulevil_xgallery3`.'.$table);
        }

        return self::PREPARE_SUCCEED;
    }

    /**
     * prepareGetDelieveryDetail
     * @return boolean
     * @throws \Doctrine\DBAL\DBALException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function prepareGetDelieveryDetail()
    {
        $this->output->writeln('');

        $progressBar = new ProgressBar($this->output, count($this->deliveryIds));
        $progressBar->start();

        foreach ($this->deliveryIds as $index => $deliveryId) {
            $deliveryDetail = $this->now->getDeliveryDetail($deliveryId);

            if (!$deliveryDetail) {
                $this->log('Can not get delivery detail id: '.$deliveryId, 'notice');

                continue;
            }

            // Prepare delivery data for inserting
            $row                      = new \stdClass;
            $row->name                = $deliveryDetail->name;
            $row->address             = $deliveryDetail->address;
            $row->restaurant_id       = $deliveryDetail->restaurant_id;
            $row->restaurant_url      = $deliveryDetail->restaurant_url;
            $row->delivery_id         = $deliveryDetail->delivery_id;
            $row->city_id             = $deliveryDetail->city_id;
            $row->district_id         = $deliveryDetail->district_id;
            $row->foody_service_id    = isset($deliveryDetail->foody_service_id) ? (int)$deliveryDetail->foody_service_id : 0;
            $row->is_city_alert       = isset($deliveryDetail->is_city_alert) ? (int)$deliveryDetail->is_city_alert : 0;
            $row->is_favorite         = isset($deliveryDetail->is_favorite) ? (int)$deliveryDetail->is_favorite : 0;
            $row->is_now_delivery     = isset($deliveryDetail->is_now_delivery) ? (int)$deliveryDetail->is_now_delivery : 0;
            $row->is_quality_merchant = isset($deliveryDetail->is_quality_merchant) ? (int)$deliveryDetail->is_quality_merchant : 0;
            $row->position            = json_encode($deliveryDetail->position);

            if (isset($deliveryDetail->price_range)) {
                $row->min_price = $deliveryDetail->price_range->min_price;
                $row->max_price = $deliveryDetail->price_range->max_price;
            }

            if (isset($deliveryDetail->brand)) {
                $row->brand_id = $deliveryDetail->brand->brand_id;
            }

            $this->deliveryData['deliveries'][] = $row;

            // Brand
            if (isset($deliveryDetail->brand) && !isset($this->deliveryData['branchs'][$deliveryDetail->brand->brand_id])) {
                $this->deliveryData['branchs'][$deliveryDetail->brand->brand_id]       = new \stdClass;
                $this->deliveryData['branchs'][$deliveryDetail->brand->brand_id]->id   = $deliveryDetail->brand->brand_id;
                $this->deliveryData['branchs'][$deliveryDetail->brand->brand_id]->url  = $deliveryDetail->brand->brand_url;
                $this->deliveryData['branchs'][$deliveryDetail->brand->brand_id]->name = $deliveryDetail->brand->name;
            }

            // Promotions
            if (isset($deliveryDetail->delivery, $deliveryDetail->delivery->promotions)) {
                foreach ($deliveryDetail->delivery->promotions as $promotion) {
                    $promotionObj                       = new \stdClass;
                    $promotionObj->delivery_id          = $deliveryId;
                    $promotionObj->discount             = $promotion->discount;
                    $promotionObj->discount_amount      = $promotion->discount_amount;
                    $promotionObj->discount_on_type     = $promotion->discount_on_type;
                    $promotionObj->discount_type        = $promotion->discount_type;
                    $promotionObj->discount_value_type  = $promotion->discount_value_type;
                    $promotionObj->max_discount_amount  = $promotion->max_discount_amount;
                    $promotionObj->max_discount_value   = $promotion->max_discount_value;
                    $promotionObj->min_order_amount     = $promotion->min_order_amount;
                    $promotionObj->min_order_value      = $promotion->min_order_value;
                    $promotionObj->promo_code           = $promotion->promo_code;
                    $promotionObj->id                   = $promotion->promotion_id;
                    $this->deliveryData['promotions'][] = $promotionObj;
                }
            }

            if (isset($deliveryDetail->delivery_categories)) {
                foreach ($deliveryDetail->delivery_categories as $deliveryCategory) {
                    $categoryXref                            = new \stdClass;
                    $categoryXref->delivery_id               = (int)$deliveryId;
                    $categoryXref->category_id               = (int)$deliveryCategory;
                    $this->deliveryData['categories_xref'][] = $categoryXref;
                }
            }

            //
            if (isset($deliveryDetail->cuisines)) {
                foreach ($deliveryDetail->cuisines as $cuisine) {
                    if (!isset($this->cuisines[$cuisine])) {
                        continue;
                    }
                    $this->model->insertCuisineXref($this->cuisines[$cuisine]->id, $deliveryId);
                }
            }

            $progressBar->advance();
        }

        return self::PREPARE_SUCCEED;
    }

    protected function processInsertDatabase()
    {
        $baseModel = new BaseModel;

        if (!empty($this->deliveryData['deliveries'])) {
            $baseModel->insertRows('xgallery_now_deliveries', $this->deliveryData['deliveries']);
        }

        if (!empty($this->deliveryData['categories_xref'])) {
            $baseModel->insertRows('xgallery_now_categories_xref', $this->deliveryData['categories_xref']);
        }

        if (!empty($this->deliveryData['branchs'])) {
            $baseModel->insertRows('xgallery_now_brands', $this->deliveryData['branchs']);
        }

        if (!empty($this->deliveryData['promotions'])) {
            $baseModel->insertRows('xgallery_now_promotions', $this->deliveryData['promotions']);
        }

        return true;
    }
}
