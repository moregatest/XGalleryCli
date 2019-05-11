<?php

namespace XGallery\Model\Now\Traits;

/**
 * Trait Menus
 * @package XGallery\Model\Now\Traits
 */
trait Menus
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
     * insertMenuItem
     *
     * @param integer $deliveryId
     * @param object  $item
     */
    public function insertMenuItem($deliveryId, $item)
    {
        $this->insert(
            'xgallery_now_menus',
            [
                'id' => $item->id,
                'delivery_id' => $deliveryId,
                'name' => $item->name,
                'description' => $item->description,
                'is_available' => (int)$item->is_available,
                'price' => (int)$item->price->value,
                'total_order' => (int)$item->total_order,
            ]
        );
    }
}
