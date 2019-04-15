<?php

namespace XGallery\Model\Now\Traits;

use Doctrine\DBAL\FetchMode;

trait Cuisines
{
    public function insertCuisineXref($cuisineId, $deliveryId)
    {
        return $this->connection->insert(
            '`xgallery_now_cuisines_xref`',
            ['cuisine_id' => $cuisineId, 'delivery_id' => $deliveryId]
        );
    }

    public function getCuisines()
    {
        return $this->connection->executeQuery(' SELECT * FROM `xgallery_now_cuisines`')
            ->fetchAll(FetchMode::STANDARD_OBJECT);
    }
}
