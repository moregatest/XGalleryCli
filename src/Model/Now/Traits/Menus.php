<?php

namespace XGallery\Model\Now\Traits;

use Doctrine\DBAL\DBALException;

/**
 * Trait Menus
 * @package XGallery\Model\Now\Traits
 */
trait Menus
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
     * insertMenuItem
     *
     * @param $deliveryId
     * @param $item
     * @throws DBALException
     */
    public function insertMenuItem($deliveryId, $item)
    {
        $this->insertIgnore('xgallery_now_menus', [
            'id' => $item->id,
            'delivery_id' => $deliveryId,
            'name' => $item->name,
            'description' => $item->description,
            'is_available' => (int)$item->is_available,
            'price' => (int)$item->price->value,
            'total_order' => (int)$item->total_order,
        ]);
    }
}
