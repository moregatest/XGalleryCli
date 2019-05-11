<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands\Now;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use ReflectionException;
use stdClass;
use Symfony\Component\Console\Helper\ProgressBar;
use XGallery\Applications\Cli\Commands\AbstractCommandNow;
use XGallery\Entities\Now\Delivery;
use XGallery\Factory;

/**
 * Class Search
 * @package XGallery\Applications\Cli\Commands\Now
 */
class Deliveries extends AbstractCommandNow
{
    protected $districts = [];
    protected $cuisines = [];
    protected $deliveryIds = [];
    /**
     * @var array
     */
    protected $deliveryData = ['deliveries' => [], 'categories_xref' => [], 'branchs' => [], 'promotions' => []];

    const SG_CITY_ID = 217;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * configure
     *
     * @throws DBALException
     * @throws ReflectionException
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
     * @throws DBALException
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
     * @throws DBALException
     * @throws GuzzleException
     * @throws InvalidArgumentException
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
     * prepareGetDelieveryDetail
     * @return boolean
     * @throws DBALException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    protected function prepareGetDelieveryDetail()
    {
        $this->output->writeln('');

        $progressBar = new ProgressBar($this->output, count($this->deliveryIds));
        $progressBar->start();

        foreach ($this->deliveryIds as $index => $deliveryId) {
            $deliveryDetail = $this->now->getDeliveryDetail($deliveryId);
            $dish           = $this->now->getDeliveryDishes($deliveryId);

            if (!$deliveryDetail) {
                $this->log('Can not get delivery detail id: '.$deliveryId, 'notice');

                continue;
            }

            // Prepare delivery data for inserting
            $this->deliveryData['deliveries'][] = new Delivery($deliveryDetail);

            // Brand
            if (isset($deliveryDetail->brand) && !isset($this->deliveryData['branchs'][$deliveryDetail->brand->brand_id])) {
                $this->deliveryData['branchs'][$deliveryDetail->brand->brand_id]       = new stdClass;
                $this->deliveryData['branchs'][$deliveryDetail->brand->brand_id]->id   = $deliveryDetail->brand->brand_id;
                $this->deliveryData['branchs'][$deliveryDetail->brand->brand_id]->url  = $deliveryDetail->brand->brand_url;
                $this->deliveryData['branchs'][$deliveryDetail->brand->brand_id]->name = $deliveryDetail->brand->name;
            }

            // Promotions
            if (isset($deliveryDetail->delivery, $deliveryDetail->delivery->promotions)) {
                foreach ($deliveryDetail->delivery->promotions as $promotion) {
                    $promotionObj                       = new stdClass;
                    $promotionObj->id                   = $promotion->promotion_id;
                    $promotionObj->delivery_id          = $deliveryId;
                    $promotionObj->discount             = $promotion->discount;
                    $promotionObj->discount_amount      = $promotion->discount_amount;
                    $promotionObj->discount_on_type     = $promotion->discount_on_type;
                    $promotionObj->discount_type        = $promotion->discount_type;
                    $promotionObj->discount_value_type  = $promotion->discount_value_type;
                    $promotionObj->expired              = isset($promotion->expired) ? date(
                        'Y-m-d H:i:s',
                        strtotime($promotion->expired)
                    ) : null;
                    $promotionObj->max_discount_amount  = $promotion->max_discount_amount;
                    $promotionObj->max_discount_value   = $promotion->max_discount_value;
                    $promotionObj->min_order_amount     = $promotion->min_order_amount;
                    $promotionObj->min_order_value      = $promotion->min_order_value;
                    $promotionObj->promo_code           = $promotion->promo_code;
                    $this->deliveryData['promotions'][] = $promotionObj;
                }
            }

            // Category
            if (isset($deliveryDetail->delivery_categories)) {
                foreach ($deliveryDetail->delivery_categories as $deliveryCategory) {
                    $categoryXref                            = new stdClass;
                    $categoryXref->delivery_id               = (int)$deliveryId;
                    $categoryXref->category_id               = (int)$deliveryCategory;
                    $this->deliveryData['categories_xref'][] = $categoryXref;
                }
            }

            // Cuisines
            if (isset($deliveryDetail->cuisines)) {
                foreach ($deliveryDetail->cuisines as $cuisine) {
                    if (!isset($this->cuisines[$cuisine])) {
                        continue;
                    }
                    $cuisineXref                           = new stdClass;
                    $cuisineXref->cuisine_id               = $this->cuisines[$cuisine]->id;
                    $cuisineXref->delivery_id              = $deliveryId;
                    $this->deliveryData['cuisines_xref'][] = $cuisineXref;
                }
            }

            // Menu items
            foreach ($dish->menu_infos as $dishes) {
                foreach ($dishes->dishes as $dish) {
                    $this->model->insertMenuItem($deliveryId, $dish);
                }
            }

            $progressBar->advance();
        }

        return self::PREPARE_SUCCEED;
    }

    /**
     * processInsertDatabase
     *
     * @return boolean
     * @throws Exception
     */
    protected function processInsertDatabase()
    {
        if (!empty($this->deliveryData['deliveries'])) {
            $this->model->insertRows('xgallery_now_deliveries', $this->deliveryData['deliveries']);
        }

        if (!empty($this->deliveryData['categories_xref'])) {
            $this->model->insertRows('xgallery_now_categories_xref', $this->deliveryData['categories_xref']);
        }

        if (!empty($this->deliveryData['cuisines_xref'])) {
            $this->model->insertRows('xgallery_now_cuisines_xref', $this->deliveryData['cuisines_xref']);
        }

        if (!empty($this->deliveryData['branchs'])) {
            $this->model->insertRows('xgallery_now_brands', $this->deliveryData['branchs']);
        }

        if (!empty($this->deliveryData['promotions'])) {
            $this->model->insertRows('xgallery_now_promotions', $this->deliveryData['promotions']);
        }

        return true;
    }
}
