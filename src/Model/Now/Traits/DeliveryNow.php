<?php


namespace XGallery\Model\Now\Traits;

use Doctrine\DBAL\DBALException;

/**
 * Trait Metadata
 * @package XGallery\Model\Now\Deliverynow
 */
trait DeliveryNow
{
    /**
     * Inserts IGNORE a table row with specified data.
     *
     * Table expression and columns are not escaped and are not safe for user-input.
     *
     * @param string         $tableExpression The expression of the table to insert data into, quoted or unquoted.
     * @param mixed[]        $data            An associative array containing column-value pairs.
     * @param int[]|string[] $types           Types of the inserted data.
     *
     * @return int The number of affected rows.
     *
     * @throws DBALException
     */
    abstract protected function insertIgnore($tableExpression, array $data, array $types = []);

    /**
     * insertDeliveryNow
     * @param $metadata
     * @throws DBALException
     */
    public function insertDeliveryNow($metadata)
    {
        $this->insertCancellations($metadata->country->cancel_options);
        $this->insertDeliveryCategories($metadata->country->delivery_categories);
        $this->insertSortTypes($metadata->country->restaurant_sort_type);
        $this->insertSortTypes($metadata->country->delivery_sort_options);
        $this->insertCities($metadata->country->cities);
    }

    /**
     * insertCancellations
     * @param $cancellations
     * @return boolean
     * @throws DBALException
     */
    protected function insertCancellations($cancellations)
    {
        if (empty($cancellations)) {
            return false;
        }
        foreach ($cancellations as $cancellation) {
            $this->insertIgnore(
                'xgallery_now_cancellations',
                ['id' => $cancellation->id, 'name' => $cancellation->name]
            );
        }
    }

    /**
     * insertDeliveryCategories
     * @param $categories
     * @return boolean
     * @throws DBALException
     */
    protected function insertDeliveryCategories($categories)
    {
        if (empty($categories)) {
            return false;
        }
        foreach ($categories as $category) {
            $this->insertIgnore('`xgallery_now_categories`', [
                'id' => $category->id,
                'parent_id' => $category->parent_category_id,
                'country_id' => $category->country_id,
                'name' => $category->name,
                'url' => $category->url_rewrite,
            ]);
        }
    }

    /**
     * insertCities
     * @param $cities
     * @return boolean
     * @throws DBALException
     */
    protected function insertCities($cities)
    {
        if (empty($cities)) {
            return false;
        }
        foreach ($cities as $city) {
            $this->insertCity($city);

            foreach ($city->districts as $district) {
                $this->insertDistrict($district);
            }
        }
    }

    /**
     * insertSortTypes
     * @param $sortTypes
     * @return boolean
     * @throws DBALException
     */
    protected function insertSortTypes($sortTypes)
    {
        if (empty($sortTypes)) {
            return false;
        }
        foreach ($sortTypes as $sortType) {
            $this->insertIgnore('`xgallery_now_restaurant_sort_types`', [
                'id' => $sortType->id,
                'code' => $sortType->code,
                'name' => $sortType->name,
            ]);
        }
    }

    /**
     * insertCity
     * @param $city
     * @return int
     * @throws DBALException
     */
    private function insertCity($city)
    {
        return $this->insertIgnore('`xgallery_now_cities`', [
            'id' => $city->id,
            'name' => $city->name,
            'url' => $city->url_rewrite_name,
            'country_id' => 86,
            'latitude' => $city->latitude,
            'longitude' => $city->longitude,
            'services' => json_encode($city->services),
        ]);
    }

    /**
     * insertDistrict
     * @param $district
     * @return int
     * @throws DBALException
     */
    private function insertDistrict($district)
    {
        return $this->insertIgnore('`xgallery_now_districts`', [
            'id' => $district->district_id,
            'city_id' => $district->province_id,
            'is_has_delivery' => (int)$district->is_has_delivery,
            'latitude' => $district->latitude,
            'longitude' => $district->longitude,
            'name' => $district->name,
            'url' => $district->url_rewrite_name,
        ]);
    }
}
