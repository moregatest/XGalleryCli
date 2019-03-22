<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Model;

use Doctrine\DBAL\DBALException;
use XGallery\Exceptions\Exception;
use XGallery\Factory;

class BaseModel extends AbstractModel
{
    protected $errors = [];

    /**
     * @param $table
     * @param $rows
     * @return boolean|integer
     * @throws DBALException
     */
    public static function insertRows($table, $rows)
    {
        $connection = Factory::getConnection();
        $query      = 'INSERT INTO `'.$table.'`';

        // Columns
        $query            .= '(';
        $onDuplicateQuery = [];
        $columnNames      = array_keys(get_object_vars($rows[0]));

        // Bind column names
        foreach ($columnNames as $columnName) {
            $query              .= '`'.$columnName.'`,';
            $onDuplicateQuery[] = '`'.$columnName.'`='.' VALUES(`'.$columnName.'`)';
            $onDuplicateQuery[] = '`'.$columnName.'`='.' VALUES(`'.$columnName.'`)';
        }

        $query = rtrim($query, ',').')';
        $query .= ' VALUES';

        $bindKeys = [];

        foreach ($rows as $index => $row) {
            $query .= ' (';
            foreach ($columnNames as $columnName) {
                $columnId                    = 'value_'.uniqid();
                $query                       .= ':'.$columnId.',';
                $bindKeys[$index][$columnId] = isset($row->{$columnName}) ? $row->{$columnName} : null;
            }

            $query = rtrim($query, ',').'),';
        }

        $query = rtrim($query, ',');
        $query .= ' ON DUPLICATE KEY UPDATE '.implode(',', $onDuplicateQuery).';';

        try {
            $prepare = $connection->prepare($query);

            // Bind values
            foreach ($bindKeys as $index => $columns) {
                foreach ($columns as $columnId => $value) {
                    $prepare->bindValue(':'.$columnId, $value);
                }
            }

            $prepare->execute();
            $connection->close();

            return $prepare->rowCount();
        } catch (Exception $exception) {
            $connection->close();
            Factory::getLogger(get_called_class())->error($exception->getMessage());

            return false;
        }
    }
}
