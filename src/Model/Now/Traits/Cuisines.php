<?php

namespace XGallery\Model\Now\Traits;

use Doctrine\DBAL\FetchMode;

/**
 * Trait Cuisines
 * @package XGallery\Model\Now\Traits
 */
trait Cuisines
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
     * insertCuisineXref
     *
     * @param integer $cuisineId
     * @param integer $deliveryId
     * @return boolean|integer
     */
    public function insertCuisineXref($cuisineId, $deliveryId)
    {
        return $this->insert(
            '`xgallery_now_cuisines_xref`',
            ['cuisine_id' => $cuisineId, 'delivery_id' => $deliveryId]
        );
    }

    /**
     * getCuisines
     *
     * @return mixed
     */
    public function getCuisines()
    {
        return $this->executeQuery(' SELECT * FROM `xgallery_now_cuisines`')
            ->fetchAll(FetchMode::STANDARD_OBJECT);
    }
}
