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
        $this->insertIgnore('`xgallery_now_cuisines`', [
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
