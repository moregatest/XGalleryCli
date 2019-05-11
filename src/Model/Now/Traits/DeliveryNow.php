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
     * Insert record
     *
     * @param       $tableExpression
     * @param array $data
     * @param array $types
     * @return boolean|integer
     */
    abstract protected function insert($tableExpression, array $data, array $types = []);

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
     * @param object[] $cancellations
     * @return boolean
     */
    protected function insertCancellations($cancellations)
    {
        if (empty($cancellations)) {
            return false;
        }

        foreach ($cancellations as $cancellation) {
            $this->insert(
                'xgallery_now_cancellations',
                ['id' => $cancellation->id, 'name' => $cancellation->name]
            );
        }
    }

    /**
     * insertDeliveryCategories
     *
     * @param object[] $categories
     * @return boolean
     */
    protected function insertDeliveryCategories($categories)
    {
        if (empty($categories)) {
            return false;
        }

        foreach ($categories as $category) {
            $this->insert(
                '`xgallery_now_categories`',
                [
                    'id' => $category->id,
                    'parent_id' => $category->parent_category_id,
                    'country_id' => $category->country_id,
                    'name' => $category->name,
                    'url' => $category->url_rewrite,
                ]
            );
        }
    }

    /**
     * insertCities
     *
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
     *
     * @param object[] $sortTypes
     * @return boolean
     */
    protected function insertSortTypes($sortTypes)
    {
        if (empty($sortTypes)) {
            return false;
        }

        foreach ($sortTypes as $sortType) {
            $this->insert(
                '`xgallery_now_restaurant_sort_types`',
                [
                    'id' => $sortType->id,
                    'code' => $sortType->code,
                    'name' => $sortType->name,
                ]
            );
        }
    }

    /**
     * insertCity
     *
     * @param object $city
     * @return boolean|integer
     */
    private function insertCity($city)
    {
        return $this->insert(
            '`xgallery_now_cities`',
            [
                'id' => $city->id,
                'name' => $city->name,
                'url' => $city->url_rewrite_name,
                'country_id' => 86,
                'latitude' => $city->latitude,
                'longitude' => $city->longitude,
                'services' => json_encode($city->services),
            ]
        );
    }

    /**
     * insertDistrict
     *
     * @param object $district
     * @return boolean|integer
     */
    private function insertDistrict($district)
    {
        return $this->insert(
            '`xgallery_now_districts`',
            [
                'id' => $district->district_id,
                'city_id' => $district->province_id,
                'is_has_delivery' => (int)$district->is_has_delivery,
                'latitude' => $district->latitude,
                'longitude' => $district->longitude,
                'name' => $district->name,
                'url' => $district->url_rewrite_name,
            ]
        );
    }
}
