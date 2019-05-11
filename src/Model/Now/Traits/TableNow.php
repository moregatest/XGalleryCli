<?php

namespace XGallery\Model\Now\Traits;

use Doctrine\DBAL\DBALException;

/**
 * Trait TableNow
 * @package XGallery\Model\Now\Traits
 */
trait TableNow
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
     * insertTableNow
     * @param object $metadata
     * @throws DBALException
     */
    public function insertTableNow($metadata)
    {
        foreach ($metadata->cuisine as $cuisine) {
            $this->insertCuisines($cuisine);
        }
    }

    /**
     * insertCuisines
     *
     * @param $cuisine
     * @return boolean
     * @throws DBALException
     */
    protected function insertCuisines($cuisine)
    {
        $this->insert('`xgallery_now_cuisines`', [
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
}
