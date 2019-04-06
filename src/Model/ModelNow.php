<?php

namespace XGallery\Model;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use XGallery\Applications\Cli\Commands\Now\Deliveries;

/**
 * Class ModelNow
 * @package XGallery\Model
 */
class ModelNow extends BaseModel
{
    /**
     * insertCity
     *
     * @param $city
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function insertCity($city)
    {
        return $this->connection->insert('`xgallery_now_cities`', [
            'id' => $city->id,
            'name' => $city->name,
            'url' => $city->name_url,
            'country_id' => 86,
        ]);
    }

    /**
     * insertDistrict
     *
     * @param $district
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function insertDistrict($district)
    {
        return $this->connection->insert('`xgallery_now_districts`', [
            'id' => $district->id,
            'name' => $district->name,
            'city_id' => $district->province_id,
        ]);
    }

    public function insertDeliveryCategory($category)
    {
        return $this->connection->insert('`xgallery_now_categories`', [
            'id' => $category->id,
            'parent_id' => $category->parent_category_id,
            'country_id' => $category->country_id,
            'name' => $category->name,
            'url' => $category->url_rewrite,
        ]);
    }

    public function insertSortTypes($sortType)
    {
        try {
            return $this->connection->insert('`xgallery_now_restaurant_sort_types`', [
                'id' => $sortType->id,
                'code' => $sortType->code,
                'name' => $sortType->name,
            ]);
        } catch (DBALException $exception) {
            return false;
        }
    }

    public function insertCuisines($cuisine)
    {
        $this->connection->insert('`xgallery_now_cuisines`', [
            'id' => $cuisine->id,
            'name' => $cuisine->name,
            'parent_id' => $cuisine->parent_id,
        ]);

        if (isset($cuisine->children) && !empty($cuisine->children)) {
            foreach ($cuisine->children as $child) {
                $this->insertCuisines($child);
            }
        }

        return true;
    }

    public function insertCuisineXref($cuisineId, $deliveryId)
    {
        return $this->connection->insert('`xgallery_now_cuisines_xref`',
            ['cuisine_id' => $cuisineId, 'delivery_id' => $deliveryId]);
    }

    /**
     * getDistrictIds
     * @param int $city
     * @return mixed[]
     * @throws DBALException
     */
    public function getDistricts($city = Deliveries::SG_CITY_ID)
    {
        return $this->connection->executeQuery(' SELECT * FROM `xgallery_now_districts` WHERE `city_id` = '.(int)$city)
            ->fetchAll(FetchMode::STANDARD_OBJECT);
    }

    public function getCuisines()
    {
        return $this->connection->executeQuery(' SELECT * FROM `xgallery_now_cuisines`')
            ->fetchAll(FetchMode::STANDARD_OBJECT);
    }

    /**
     * getSorts
     * @return mixed[]
     * @throws DBALException
     */
    public function getSorts()
    {
        return $this->connection->executeQuery(' SELECT * FROM `xgallery_now_restaurant_sort_types`')
            ->fetchAll(FetchMode::STANDARD_OBJECT);
    }

    public function getDeliveriesWithPromotion($categories)
    {
        $query = 'SELECT
    *
FROM
    xgallery_now_deliveries AS `deliveries`
INNER JOIN(
    SELECT
        *
    FROM
        `xgallery_now_promotions` AS `promotions`
    WHERE
        `promotions`.`delivery_id` IN(
        SELECT
            delivery_id
        FROM
            `xgallery_now_categories_xref`
        WHERE
            `category_id` IN('.$categories.')
    ) AND `promotions`.`discount_value_type` = 1
DESC
) AS `top_promotions`
ON
    `top_promotions`.`delivery_id` = `deliveries`.`delivery_id`
ORDER BY
    `discount_amount`';

        $list       = $this->connection->executeQuery($query)->fetchAll(FetchMode::STANDARD_OBJECT);
        $categories = $this->connection->executeQuery('SELECT `name` FROM `xgallery_now_delivery_categories` WHERE `id` IN ('.$categories.')')->fetchAll(FetchMode::COLUMN);

        return [
            'list' => $list,
            'categories' => $categories,
            'query' => $query,
        ];
    }


}